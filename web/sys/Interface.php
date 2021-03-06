<?php
/**
 * Smarty Extension class
 *
 * PHP version 5
 *
 * Copyright (C) Villanova University 2007.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @category VuFind
 * @package  Support_Classes
 * @author   Andrew S. Nagy <vufind-tech@lists.sourceforge.net>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/system_classes Wiki
 */
require_once 'Smarty/Smarty.class.php';
require_once 'sys/mobile_device_detect.php';
require_once 'sys/Cart_Model.php';

/**
 * Smarty Extension class
 *
 * @category VuFind
 * @package  Support_Classes
 * @author   Andrew S. Nagy <vufind-tech@lists.sourceforge.net>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/system_classes Wiki
 */
class UInterface extends Smarty
{
    public $lang;
    private $_vufindTheme;   // which theme(s) are active?

    /**
     * Constructor
     *
     * @param string $local Local directory for cache and compile
     *
     * @access public
     */
    public function UInterface($local = '')
    {
        global $configArray;

        if (!$local) {
            $local = $configArray['Site']['local'];
        }
        $this->_vufindTheme = $configArray['Site']['theme'];

        // Use mobile theme for mobile devices (if enabled in config.ini)
        if (isset($configArray['Site']['mobile_theme'])) {
            // If the user is overriding the UI setting, store that:
            if (isset($_GET['ui'])) {
                $_COOKIE['ui'] = $_GET['ui'];
                setcookie('ui', $_GET['ui'], null, '/');
            } else if (!isset($_COOKIE['ui'])) {
                // If we don't already have a UI setting, detect if we're on a
                // mobile device and store the result in a cookie so we don't waste
                // time doing the detection routine on every page:
                $_COOKIE['ui'] = mobile_device_detect() ? 'mobile' : 'standard';
                setcookie('ui', $_COOKIE['ui'], null, '/');
            }
            // If we're mobile, override the standard theme with the mobile one:
            if ($_COOKIE['ui'] == 'mobile') {
                $this->_vufindTheme = $configArray['Site']['mobile_theme'];
            }
        }

        // Check to see if multiple themes were requested; if so, build an array,
        // otherwise, store a single string.
        $themeArray = explode(',', $this->_vufindTheme);
        if (count($themeArray) > 1) {
            $this->template_dir = array();
            foreach ($themeArray as $currentTheme) {
                $currentTheme = trim($currentTheme);
                $this->template_dir[] = "$local/interface/themes/$currentTheme";
            }
        } else {
            $this->template_dir  = "$local/interface/themes/{$this->_vufindTheme}";
        }

        // Create an MD5 hash of the theme name -- this will ensure that it's a
        // writeable directory name (since some config.ini settings may include
        // problem characters like commas or whitespace).
        $md5 = md5($this->_vufindTheme);
        $this->compile_dir   = "$local/interface/compile/$md5";
        if (!is_dir($this->compile_dir)) {
            mkdir($this->compile_dir);
        }
        $this->cache_dir     = "$local/interface/cache/$md5";
        if (!is_dir($this->cache_dir)) {
            mkdir($this->cache_dir);
        }
        $this->plugins_dir   = array('plugins', "$local/interface/plugins");
        $this->caching       = false;
        $this->debug         = true;
        $this->compile_check = true;

        unset($local);

        $this->register_function('translate', 'translate');
        $this->register_function('char', 'char');

        $this->assign('site', $configArray['Site']);
        $this->assign('path', $configArray['Site']['path']);
        $this->assign('url', $configArray['Site']['url']);
        $this->assign(
            'fullPath',
            isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : false
        );
        $this->assign('supportEmail', $configArray['Site']['email']);
        $searchObject = SearchObjectFactory::initSearchObject();
        $this->assign(
            'basicSearchTypes',
            is_object($searchObject) ? $searchObject->getBasicTypes() : array()
        );
        $this->assign(
            'autocomplete',
            is_object($searchObject) ? $searchObject->getAutocompleteStatus() : false
        );

        $this->assign('retainFiltersByDefault', $searchObject->getRetainFilterByDefaultSetting());

        if (isset($configArray['Site']['showBookBag'])) {
            $this->assign(
                'bookBag', ($configArray['Site']['showBookBag'])
                ? Cart_Model::getInstance() : false
            );
        }

        if (isset($configArray['OpenURL'])
            && isset($configArray['OpenURL']['url'])
        ) {
            // Trim off any parameters (for legacy compatibility -- default config
            // used to include extraneous parameters):
            list($base) = explode('?', $configArray['OpenURL']['url']);
        } else {
            $base = false;
        }
        $this->assign('openUrlBase', empty($base) ? false : $base);

        // Other OpenURL settings:
        $this->assign(
            'openUrlWindow',
            empty($configArray['OpenURL']['window_settings'])
            ? false : $configArray['OpenURL']['window_settings']
        );
        $this->assign(
            'openUrlGraphic',
            empty($configArray['OpenURL']['graphic'])
            ? false : $configArray['OpenURL']['graphic']
        );
        $this->assign(
            'openUrlGraphicWidth',
            empty($configArray['OpenURL']['graphic_width'])
            ? false : $configArray['OpenURL']['graphic_width']
        );
        $this->assign(
            'openUrlGraphicHeight',
            empty($configArray['OpenURL']['graphic_height'])
            ? false : $configArray['OpenURL']['graphic_height']
        );
        if (isset($configArray['OpenURL']['embed'])
            && !empty($configArray['OpenURL']['embed'])
        ) {
            include_once 'sys/Counter.php';
            $this->assign('openUrlEmbed', true);
            $this->assign('openUrlCounter', new Counter());
        }

        $this->assign('currentTab', 'Search');

        $this->assign('authMethod', $configArray['Authentication']['method']);

        if (isset($configArray['Authentication']['libraryCard'])  && !$configArray['Authentication']['libraryCard']) {
            $this->assign('libraryCard', false);
        } else {
            $this->assign('libraryCard', true);
        }

        $this->assign(
            'sidebarOnLeft',
            !isset($configArray['Site']['sidebarOnLeft'])
            ? false : $configArray['Site']['sidebarOnLeft']
        );

        $piwikUrl = isset($configArray['Piwik']['url']) ? $configArray['Piwik']['url'] : false;
        if ($piwikUrl && isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
            $piwikUrl = preg_replace('/^http:/', 'https:', $piwikUrl);
        }
        $this->assign('piwikUrl', $piwikUrl);

        $this->assign(
            'piwikSiteId',
            !isset($configArray['Piwik']['site_id'])
            ? false : $configArray['Piwik']['site_id']
        );

        // Create prefilter list
        $prefilters = getExtraConfigArray('prefilters');
        if (isset($prefilters['Prefilters'])) {
            $filters = array();
            foreach ($prefilters['Prefilters'] as $key => $filter) {
                $filters[$key] = $filter;
            }
            $this->assign('prefilterList', $filters);
        }
        if (isset($_REQUEST['prefiltered'])) {
            $this->assign('activePrefilter', $_REQUEST['prefiltered']);
        }

        $metalib = getExtraConfigArray('MetaLib');
        if (!empty($metalib)) {
            $this->assign('metalibEnabled', isset($metalib['General']['enabled']) ? $metalib['General']['enabled'] : true);
        }

        $pci = getExtraConfigArray('PCI');
        if (!empty($pci)) {
            $this->assign('pciEnabled', isset($pci['General']['enabled']) ? $pci['General']['enabled'] : true);
        }

        $rssFeeds = getExtraConfigArray('rss');
        if (isset($rssFeeds)) {
            $this->assign('rssFeeds', $rssFeeds);
        }

        $catalog = ConnectionManager::connectToCatalog();
        $this->assign("offlineMode", $catalog->getOfflineMode());
        $hideLogin = isset($configArray['Authentication']['hideLogin'])
            ? $configArray['Authentication']['hideLogin'] : false;
        $this->assign("hideLogin", $hideLogin ? true : $catalog->loginIsHidden());

        if (isset($configArray['Site']['development']) && $configArray['Site']['development']) {
            $this->assign('developmentSite', true);
        }

        if (isset($configArray['Site']['dualResultsEnabled']) && $configArray['Site']['dualResultsEnabled']) {
            $this->assign('dualResultsEnabled', true);
        }

        // Resolve enabled context-help ids
        $contextHelp = array();
        if (isset($configArray['ContextHelp'])) {
            foreach ($configArray['ContextHelp'] as $key => $val) {
                if ((boolean)$val) {
                    $contextHelp[] = $key;
                }
            }
        }
        $this->assign('contextHelp', $contextHelp);

        // Set Advanced Search start year and scale
        // The default values:
        $advSearchYearScale = array(0,900,1800,1910);

        $yearScale = !isset($configArray['Site']['advSearchYearScale'])
            ? false : $configArray['Site']['advSearchYearScale'];

        if (isset($yearScale)) {
            $scaleArray = explode(',', $yearScale);
            $i = count($scaleArray);
            // Do we have more values or just the starting year
            if ($i > 1) {
                $j = 0;
                if ($i <= 4) {
                    while ($j < $i) {
                        $advSearchYearScale[$j] = (int)$scaleArray[$j];
                        $j++;
                    }
                } else {
                    while ($j < 4) {
                        $advSearchYearScale[$j] = (int)$scaleArray[$j];
                        $j++;
                    }
                }
            } else { // Only the starting year is set
                $advSearchYearScale[0] = (int)$yearScale;
            }
        }
        $this->assign('advSearchYearScale', $advSearchYearScale);

    }

    /**
     * Get the current active theme setting.
     *
     * @return string
     * @access public
     */
    public function getVuFindTheme()
    {
        return $this->_vufindTheme;
    }

    /**
     * Set the inner page template to display.
     *
     * @param string $tpl Template filename.
     *
     * @return void
     * @access public
     */
    public function setTemplate($tpl)
    {
        $tpl = $this->getLocalOverride($tpl, true);
        $this->assign('pageTemplate', $tpl);
    }

    /**
     * Set the page title to display.
     *
     * @param string $title Page title.
     *
     * @return void
     * @access public
     */
    public function setPageTitle($title)
    {
        global $configArray;
        $siteTitle = '';
        if (isset($configArray['Site']['title']) && $configArray['Site']['title']) {
            $fullTitle = translate($title) . ' - ' . $configArray['Site']['title'];
            $siteTitle = $configArray['Site']['title'];
        } else {
            $fullTitle = translate($title);
        }
        $this->assign('pageTitle', $fullTitle);
        $this->assign('shortTitle', translate($title));
        $this->assign('siteTitle', $siteTitle);
    }

    /**
     * Get the currently selected language code.
     *
     * @return string
     * @access public
     */
    public function getLanguage()
    {
        return $this->lang;
    }

    /**
     * Set the currently selected language code.
     *
     * @param string $lang Language code.
     *
     * @return void
     * @access public
     */
    public function setLanguage($lang)
    {
        global $configArray;

        $this->lang = $lang;
        $this->assign('userLang', $lang);
        $this->assign('allLangs', $configArray['Languages']);
    }

    /**
     * Initialize global interface variables (part of standard VuFind startup
     * process).  This method is designed for initializations that can't happen
     * in the constructor because they rely on session initialization and other
     * processing that happens subsequently in the front controller.
     *
     * @return void
     * @access public
     */
    public function initGlobals()
    {
        global $module, $action, $user, $configArray;

        // Pass along module and action to the templates.
        $this->assign('module', $module);
        $this->assign('action', $action);
        // Don't pass a PEAR error to interface
        $this->assign('user', PEAR::isError($user) ? null : $user);

        if($user) {
            // Get My Lists
            $listList = $user->getLists();
            $this->assign('listList', $listList);
        }

        // Load the last limit from the request or session for initializing default
        // in search box:
        if (isset($_REQUEST['limit'])) {
            $this->assign('lastLimit', $_REQUEST['limit']);
        } else if (isset($_SESSION['lastUserLimit'])) {
            $this->assign('lastLimit', $_SESSION['lastUserLimit']);
        }

        // Load the last sort from the request or session for initializing default
        // in search box.  Note that this is not entirely ideal, since sort settings
        // will carry over from one module to another (i.e. WorldCat vs. Summon);
        // however, this is okay since the validation code will prevent errors and
        // simply revert to default sort when switching between modules.
        if (isset($_REQUEST['sort'])) {
            $this->assign('lastSort', $_REQUEST['sort']);
        } else if (isset($_SESSION['lastUserSort'])) {
            $this->assign('lastSort', $_SESSION['lastUserSort']);
        }

        // This is detected already, but we want a "back to mobile"
        // button in the standard view on mobile devices so we check it again
        if (isset($configArray["Site"]["mobile_theme"]) && mobile_device_detect()) {
            $pageURL = $_SERVER['REQUEST_URI'];
            if (isset($_GET["ui"])) {
                $pageURL = str_replace(
                    "ui=" . urlencode($_GET["ui"]), "ui=mobile", $pageURL
                );
            } else if (strstr($pageURL, "?") != false) {
                $pageURL = str_replace("?", "?ui=mobile&", $pageURL);
            } else if (strstr($pageURL, "#") != false) {
                $pageURL = str_replace("#", "?ui=mobile#", $pageURL);
            } else {
                $pageURL .= "?ui=mobile";
            }
            $this->assign("mobileViewLink", $pageURL);
        }

        // Init Mozilla Persona here now that we may have a valid user
        if (isset($configArray['Authentication']['mozillaPersona']) && $configArray['Authentication']['mozillaPersona']) {
            $this->assign('mozillaPersona', true);
            if (isset($_SESSION['authMethod']) && $_SESSION['authMethod'] == 'MozillaPersona') {
                if (PEAR::isError($user)) {
                    $this->assign('mozillaPersonaCurrentUser', null);
                } else {
                    $username = $user->username;
                    if (isset($configArray['Site']['institution']) && strncmp($configArray['Site']['institution'] . ':', $username, strlen($configArray['Site']['institution']) + 1) == 0) {
                        $username = substr($username, strlen($configArray['Site']['institution']) + 1);
                    }
                    $this->assign('mozillaPersonaCurrentUser', $username);
                }
            }
            if (!isset($configArray['Authentication']['mozillaPersonaAutoLogout']) || $configArray['Authentication']['mozillaPersonaAutoLogout']) {
                $this->assign('mozillaPersonaAutoLogout', true);
            }
        }

        // Catalog Account List
        if ($user && !PEAR::isError($user)) {
            $this->assign('currentCatalogAccount', $user->cat_username);
            $this->assign('catalogAccounts', $user->getCatalogAccounts());
        }


        // Override default value for retain filters -option (searches.ini::retain_filters_by_default)
        // if it can be found in the request URL or has previously been saved as a session variable.
        $retainFilters = null;
        if (isset($_REQUEST['retainFilters'])) {
            $retainFilters = $_REQUEST['retainFilters'] === '1';
        } elseif (isset($_SESSION['retainFilters'])) {
            $retainFilters = $_SESSION['retainFilters'] === 1;
        }
        if (!is_null($retainFilters)) {
            $_SESSION['retainFilters'] = (int)$retainFilters;
            $this->assign('retainFiltersByDefault', $retainFilters);
        }

        // Assign national theme header image
        $images = 4; // Number of available header images

        $bgNumber = isset($_SESSION['bgNumber']) ? $_SESSION['bgNumber'] : rand(1, $images);
        $_SESSION['bgNumber'] = $bgNumber;

        $this->assign('bgNumber', $bgNumber);

        // Moved to here from constructor
        if (isset($configArray['Authentication']['shibboleth']) && $configArray['Authentication']['shibboleth']) {
            if (!isset($configArray['Shibboleth']['login'])) {
                throw new Exception(
                    'Missing parameter in the config.ini. Check if ' .
                    'the login parameter is set.'
                );
            }
            if (isset($configArray['Shibboleth']['target'])) {
                $shibTarget = $configArray['Shibboleth']['target'];
            } else {
                if ($module == 'MyResearch' && $action == 'Home') {
                    $myRes = isset($configArray['Site']['defaultLoggedInModule'])
                        ? $configArray['Site']['defaultLoggedInModule'] : 'MyResearch';
                    $myRes .= '/Home';
                } else if ($module == 'MyResearch' && $action == 'SaveSearch') {
                    $myRes = $module . '/' . $action . '?save=' . $_GET['save'];
                } else {
                    $myRes = $module . '/' . $action;
                }
                // Override default location with followup location if set
                if (isset($_REQUEST['followupModule'])) {
                    $myRes = $_REQUEST['followupModule'];
                    if (isset($_REQUEST['followupAction'])) {
                        $myRes .= '/' . urlencode($_REQUEST['followupAction']);
                        // Hack to allow quickadd to favorites after Shibboleth login
                        if (isset($configArray['Site']['quickAddToFavorites'])
                            && $configArray['Site']['quickAddToFavorites']
                            && isset($_REQUEST['followupId'])
                            && $_REQUEST['followupAction'] == 'Save'
                        ) {
                            if ($_REQUEST['followupModule'] != 'PCI') {
                                $myRes = urlencode($_REQUEST['followupModule'])
                                    . '/' . urlencode($_REQUEST['followupId'])
                                    . '/' . urlencode($_REQUEST['followupAction'])
                                    . '?submit';
                            } else {
                                $myRes = urlencode($_REQUEST['followupModule'])
                                    . '/' . urlencode($_REQUEST['followupAction'])
                                    . '?submit'
                                    . '&id=' . urlencode($_REQUEST['followupId']);
                            }
                        }
                    } else {
                        $myRes .= '/Home';
                    }
                }

                $shibTarget = $configArray['Site']['url'] . '/' . $myRes;
            }
            $sessionInitiator = $configArray['Shibboleth']['login'];
            $sessionInitiator .= (strpos($sessionInitiator, '?') === false) ? '?' : '&';
            $sessionInitiator .= 'target=' . urlencode($shibTarget);

            if (isset($configArray['Shibboleth']['provider_id'])) {
                $sessionInitiator = $sessionInitiator . '&providerId=' .
                    urlencode($configArray['Shibboleth']['provider_id']);
            }

            $this->assign('sessionInitiator', $sessionInitiator);
        }
    }

    /**
     * Assign book preview options to the interface.
     *
     * @return void
     */
    public function assignPreviews()
    {
        global $configArray;
        global $interface;

        $providers = explode(',', $configArray['Content']['previews']);
        $interface->assign('showPreviews', true);
        foreach ($providers as $provider) {
            $provider = trim($provider);
            switch ($provider) {
            case 'Google':
                // fetch Google options from config, if none use default vals.
                $googleOptions = isset($configArray['Content']['GoogleOptions'])
                    ? str_replace(' ', '', $configArray['Content']['GoogleOptions'])
                    : "full,partial";
                $interface->assign('googleOptions', $googleOptions);
                break;
            case 'OpenLibrary':
                // fetch OL options from config, if none use default vals.
                $olOptions = isset($configArray['Content']['OpenLibraryOptions'])
                    ? str_replace(
                        ' ', '', $configArray['Content']['OpenLibraryOptions']
                    )
                    : "full,partial";
                $interface->assign('olOptions', $olOptions);
                break;
            case 'HathiTrust':
                // fetch Hathi access rights from config (or default to pd,world)
                $hathiOptions = isset($configArray['Content']['HathiRights'])
                    ? str_replace(' ', '', $configArray['Content']['HathiRights'])
                    : "pd,world";
                $interface->assign('hathiOptions', $hathiOptions);
                break;
            }
        }
    }

    /**
     * Returns an info message of the active prefilter customized
     * for the given search type. The message is displayed in the UI by
     * search services that do not support prefilters (PCI/MetaLib).
     *
     * @param string $searchType Name of search type, needs to match a
     * translation key.
     *
     * @return string Messaga, or null if no prefilter is active
     * or the active prefilter has no filters defined.
     */
    public function getGlobalFiltersNotification($searchType)
    {
        if (isset($_REQUEST['prefiltered'])) {
            $code = $_REQUEST['prefiltered'];
            if ($code != '-') {
                $prefilters = getExtraConfigArray('prefilters');
                if (!isset($prefilters[$code])) {
                    return null;
                }
                $prefilter = $prefilters[$code];
                if (!isset($prefilter['filter']) || count($prefilter['filter']) == 0) {
                    // No warning if prefilter has no filters
                    return null;
                }

                $prefilterStr = '<strong>' . translate($code) . '</strong>';
                $showGlobalFiltersNote = translate('global_filters_note');
                $showGlobalFiltersNote = str_replace('{0}', translate($searchType), $showGlobalFiltersNote);
                $showGlobalFiltersNote = str_replace('{1}', $prefilterStr, $showGlobalFiltersNote);
                return $showGlobalFiltersNote;
            }
        }
        return null;
    }

    /**
     * Check if a .local.tpl version of a template exists and return it if it does
     *
     * @param string $tpl      Template file name
     * @param bool   $inModule Whether to look in the $module directory
     *
     * @return string Template file name (local if found, otherwise original)
     */
    protected function getLocalOverride($tpl, $inModule)
    {
        global $module;

        $localTpl = preg_replace('/(.*)\./', '\\1.local.', $tpl);
        foreach (is_array($this->template_dir) ? $this->template_dir : array($this->template_dir) as $templateDir) {
            if ($inModule) {
                $fullPath = $templateDir . DIRECTORY_SEPARATOR . $module
                    . DIRECTORY_SEPARATOR . $localTpl;
            } else {
                $fullPath = $templateDir . DIRECTORY_SEPARATOR . $localTpl;
            }
            if (file_exists($fullPath)) {
                return $localTpl;
            }
        }
        return $tpl;
    }

    // @codingStandardsIgnoreStart

    /**
     * Convert theme file name to an absolute path
     *
     * @param string $resource_name File name
     *
     * @return string Absolute path
     */
    protected function convertToAbsolutePath($resource_name)
    {
        foreach (is_array($this->template_dir) ? $this->template_dir : array($this->template_dir) as $_curr_path) {
            $_fullpath = $_curr_path . DIRECTORY_SEPARATOR . $resource_name;
            if (file_exists($_fullpath) && is_file($_fullpath)) {
                $resource_name = $_fullpath;
                break;
            }
            // didn't find the file, try include_path
            $_params = array('file_path' => $_fullpath);
            require_once(SMARTY_CORE_DIR . 'core.get_include_path.php');
            if(smarty_core_get_include_path($_params, $this)) {
                $resource_name = $_params['new_file_path'];
                break;
            }
        }
        return $resource_name;
    }

    /**
     * called for included templates
     *
     * @param string $_smarty_include_tpl_file
     * @param string $_smarty_include_vars
     */

    // $_smarty_include_tpl_file, $_smarty_include_vars

    function _smarty_include($params)
    {
        if (isset($params['smarty_include_tpl_file'])) {
            $params['smarty_include_tpl_file'] = $this->getLocalOverride($params['smarty_include_tpl_file'], false);
            // Change resource_name to absolute path so that Smarty caching must take into account the theme directory
            $params['smarty_include_tpl_file'] = $this->convertToAbsolutePath($params['smarty_include_tpl_file']);
        }
        return parent::_smarty_include($params);
    }

    /**
     * executes & returns or displays the template results
     *
     * @param string $resource_name
     * @param string $cache_id
     * @param string $compile_id
     * @param boolean $display
     */
    function fetch($resource_name, $cache_id = null, $compile_id = null, $display = false)
    {
        $resource_name = $this->getLocalOverride($resource_name, false);

        // Change resource_name to absolute path so that Smarty caching must take into account the theme directory
        $resource_name = $this->convertToAbsolutePath($resource_name);

        return parent::fetch($resource_name, $cache_id, $compile_id, $display);
    }

    /**
     * fetch the template info. Gets timestamp, and source
     * if get_source is true
     *
     * sets $source_content to the source of the template, and
     * $resource_timestamp to its time stamp
     * @param string $resource_name
     * @param string $source_content
     * @param integer $resource_timestamp
     * @param boolean $get_source
     * @param boolean $quiet
     * @return boolean
     */

    function _fetch_resource_info(&$params)
    {
        // We need to take into account any change in symlink timestamp too, hence the following...
        $retval = parent::_fetch_resource_info($params);
        if ($retval && isset($params['resource_name'])) {
            $_params = array('resource_name' => $params['resource_name']) ;
            if (isset($params['resource_base_path']))
                $_params['resource_base_path'] = $params['resource_base_path'];
            else
                $_params['resource_base_path'] = $this->template_dir;
            if ($this->_parse_resource_name($_params) && is_link($_params['resource_name'])) {
                $info = lstat($_params['resource_name']);
                $params['resource_timestamp'] = max($info['mtime'], $params['resource_timestamp']);
            }
        }
        return $retval;
    }
    // @codingStandardsIgnoreEnd

}

/**
 * Smarty extension function to translate a string.
 *
 * @param string|array $params Either array from Smarty or plain string to translate
 *
 * @return string              Translated string
 */
function translate($params)
{
    global $translator;

    // If no translator exists yet, create one -- this may be necessary if we
    // encounter a failure before we are able to load the global translator
    // object.
    if (!is_object($translator)) {
        global $configArray;

        $translator = new I18N_Translator(
            array('lang', 'lang_local'), $configArray['Site']['language'], $configArray['System']['debug']
        );
    }
    if (is_array($params)) {
        return $translator->translate($params['text'], isset($params['prefix']) ? $params['prefix'] : '');
    } else {
        return $translator->translate($params);
    }
}

/**
 * Smarty extension function to check if a translation exists.
 *
 * @param string|array $params Either array from Smarty or plain string to translate
 *
 * @return boolean              translation exists
 */
function translationExists($params)
{
    global $translator;

    // If no translator exists yet, create one -- this may be necessary if we
    // encounter a failure before we are able to load the global translator
    // object.
    if (!is_object($translator)) {
        global $configArray;

        $translator = new I18N_Translator(
            array('lang', 'lang_local'), $configArray['Site']['language'], $configArray['System']['debug']
        );
    }
    if (is_array($params)) {
        return $translator->translationExists($params['text'], isset($params['prefix']) ? $params['prefix'] : '');
    } else {
        return $translator->translationExists($params);
    }
}

/**
 * Smarty extension function to generate a character from an integer.
 *
 * @param array $params Parameters passed in by Smarty
 *
 * @return string       Generated character
 */
function char($params)
{
    extract($params);
    return chr($int);
}

?>
