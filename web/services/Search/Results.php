<?php
/**
 * Results action for Search module
 *
 * PHP version 5
 *
 * Copyright (C) Andrew Nagy 2009
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
 * @package  Controller_Search
 * @author   Andrew S. Nagy <vufind-tech@lists.sourceforge.net>
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
require_once 'Action.php';
require_once 'services/MyResearch/lib/User.php';
require_once 'services/MyResearch/lib/Search.php';

require_once 'sys/Pager.php';
require_once 'sys/ResultScroller.php';

/**
 * Results action for Search module
 *
 * @category VuFind
 * @package  Controller_Search
 * @author   Andrew S. Nagy <vufind-tech@lists.sourceforge.net>
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
class Results extends Action
{
    private $_solrStats = false;

    /**
     * Process incoming parameters and display the page.
     *
     * @return void
     * @access public
     */
    public function launch()
    {
        global $interface;
        global $configArray;

        // Set Proxy URL
        if (isset($configArray['EZproxy']['host'])) {
            $interface->assign('proxy', $configArray['EZproxy']['host']);
        }

        // Initialise from the current search globals
        $searchObject = SearchObjectFactory::initSearchObject();
        $searchObject->init();

        // Handle hierarchical facets (request level 0 only for initial display)
        $facetConfig = getExtraConfigArray('facets');
        if (isset($facetConfig['SpecialFacets']['hierarchical'])) {
            foreach ($facetConfig['SpecialFacets']['hierarchical'] as $facet) {
                $searchObject->addFacetPrefix(array($facet => '0/'));
            }
        }

        // Build RSS Feed for Results (if requested)
        if ($searchObject->getView() == 'rss') {
            // Throw the XML to screen
            echo $searchObject->buildRSS();
            // And we're done
            exit();
        }

        $accept = isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : '';

        // Build JSON for Results (if requested)
        if ($searchObject->getView() == 'json'
            || stristr($accept, 'application/json')
        ) {
            // Throw the XML to screen
            echo $searchObject->buildJSON();
            // And we're done
            exit();
        }

        // Build XML for Results (if requested)
        if ($searchObject->getView() == 'xml' || stristr($accept, 'text/xml')) {
            // Throw the XML to screen
            echo $searchObject->buildXML();
            // And we're done
            exit();
        }

        // Determine whether to display book previews
        if (isset($configArray['Content']['previews'])) {
            $interface->assignPreviews();
        }

        $interface->assign(
            'showContext',
            isset($configArray['Content']['showHierarchyTree'])
            ? $configArray['Content']['showHierarchyTree']
            : false
        );

        // TODO : Stats, move inside the search object
        // Setup Statistics Index Connection
        if ($configArray['Statistics']['enabled']) {
            $this->_solrStats = ConnectionManager::connectToIndex('SolrStats');
        }

        // Set Interface Variables
        //   Those we can construct BEFORE the search is executed
        $displayQuery = $searchObject->displayQuery();
        $interface->setPageTitle(
            translate('Search Results') .
            (empty($displayQuery) ? '' : ' - ' . htmlspecialchars($displayQuery))
        );

        // Process Search
        $result = $searchObject->processSearch(true, true);
        if (PEAR::isError($result)) {
            PEAR::raiseError($result->getMessage());
        }

        // Some more variables
        //   Those we can construct AFTER the search is executed, but we need
        //   no matter whether there were any results
        $interface->assign('qtime', round($searchObject->getQuerySpeed(), 2));
        $interface->assign(
            'spellingSuggestions', $searchObject->getSpellingSuggestions()
        );

        $interface->assign('isEmptySearch', $searchObject->isEmptySearch());
        $interface->assign('lookfor', $displayQuery);
        $interface->assign('searchType', $searchObject->getSearchType());
        // Will assign null for an advanced search
        $interface->assign('searchIndex', $searchObject->getSearchIndex());

        $interface->assign('sortList',   $searchObject->getSortList());
        $interface->assign('viewList',   $searchObject->getViewList());
        $interface->assign('rssLink',    $searchObject->getRSSUrl());
        $interface->assign('limitList',  $searchObject->getLimitList());
        $interface->assign('searchWithoutFilters', $searchObject->renderSearchUrlWithoutFilters());
        $interface->assign('searchWithFilters', $searchObject->renderSearchUrl());

        if ($spatialDateRangeType = $searchObject->getSpatialDateRangeFilterType()) {
            $interface->assign('spatialDateRangeType', $spatialDateRangeType);
        }

        // We'll need recommendations no matter how many results we found:
        $interface->assign(
            'topRecommendations', $searchObject->getRecommendationsTemplates('top')
        );
        $interface->assign(
            'sideRecommendations', $searchObject->getRecommendationsTemplates('side')
        );
        $interface->assign(
            'orFilters', $searchObject->getOrFilters()
        );

        // Whether RSI is enabled
        if (isset($configArray['OpenURL']['use_rsi']) && $configArray['OpenURL']['use_rsi']) {
            $interface->assign('rsi', true);
        }

        // Whether embedded openurl autocheck is enabled
        if (isset($configArray['OpenURL']['autocheck']) && $configArray['OpenURL']['autocheck']) {
            $interface->assign('openUrlAutoCheck', true);
        }

        // If no record found
        if ($searchObject->getResultTotal() < 1) {
            // Don't let bots crawl "no results" pages
            $this->disallowBots();

            $interface->setTemplate('list-none.tpl');
            $interface->assign('recordCount', 0);
            $interface->assign('removeAllFilters', $searchObject->renderSearchUrlWithoutFilters(array('prefiltered')));

            // Set up special "no results" recommendations:
            $interface->assign(
                'noResultsRecommendations',
                $searchObject->getRecommendationsTemplates('noresults')
            );

            // Was the empty result set due to an error?
            $error = $searchObject->getIndexError();
            if ($error !== false) {
                // Solr 4 returns error as an array
                if (is_array($error)) {
                    $error = $error['msg'];
                }
                // If it's a parse error or the user specified an invalid field, we
                // should display an appropriate message:
                if (stristr($error, 'org.apache.lucene.queryParser.ParseException')
                    || stristr($error, 'org.apache.solr.search.SyntaxError')
                    || preg_match('/^undefined field/', $error)
                ) {
                    $interface->assign('parseError', true);
                } else {
                    // Unexpected error -- let's treat this as a fatal condition.
                    PEAR::raiseError(
                        new PEAR_Error(
                            'Unable to process query<br />Solr Returned: ' . $error
                        )
                    );
                }
            }

            // TODO : Stats, move inside the search object
            // Save no records found stat
            if ($this->_solrStats) {
                $this->_solrStats->saveNoHits($_GET['lookfor'], $_GET['type']);
            }
        } else {
            // TODO : Stats, move inside the search object
            // Save search stat
            if ($this->_solrStats) {
                $this->_solrStats->saveSearch($_GET['lookfor'], $_GET['type']);
            }

            // If the "jumpto" parameter is set, jump to the specified result index:
            $this->_processJumpto($result);

            // Assign interface variables
            $summary = $searchObject->getResultSummary();
            $interface->assign('recordCount', $summary['resultTotal']);
            $interface->assign('recordStart', $summary['startRecord']);
            $interface->assign('recordEnd',   $summary['endRecord']);

            // Big one - our results
            $interface->assign('recordSet', $searchObject->getResultRecordHTML());

            // Setup Display

            //Get view & load template
            $currentView  = $searchObject->getView();
            $interface->assign('subpage', 'Search/list-' . $currentView .'.tpl');
            $interface->setTemplate('list.tpl');

            // Process Paging
            $link = $searchObject->renderLinkPageTemplate();
            $options = array('totalItems' => $summary['resultTotal'],
                             'fileName'   => $link,
                             'perPage'    => $summary['perPage']);
            $pager = new VuFindPager($options);
            $interface->assign('pageLinks', $pager->getLinks());
        }

        // 'Finish' the search... complete timers and log search history.
        $searchObject->close();
        $interface->assign('time', round($searchObject->getTotalSpeed(), 2));
        // Show the save/unsave code on screen
        // The ID won't exist until after the search has been put in the search
        //    history so this needs to occur after the close() on the searchObject
        $interface->assign('showSaved',   true);
        $interface->assign('savedSearch', $searchObject->isSavedSearch());
        $interface->assign('searchId',    $searchObject->getSearchId());

        // Save the URL of this search to the session so we can return to it easily:
        $_SESSION['lastSearchURL'] = $searchObject->renderSearchUrl();
        // Save the display query too, so we can use it e.g. in the breadcrumbs
        $_SESSION['lastSearchDisplayQuery'] = $displayQuery;
        // Also save the search ID and type so user can edit the advanced search
        $_SESSION['lastSearchID'] =  $searchObject->getSearchID();
        $_SESSION['searchType'] = $searchObject->getSearchType();

        // initialize the search result scroller for this search
        $scroller = new ResultScroller();
        $scroller->init($searchObject, $result);

        // Done, display the page
        $interface->display('layout.tpl');
    } // End launch()

    /**
     * Process the "jumpto" parameter.
     *
     * @param array $result Solr result returned by SearchObject
     *
     * @return void
     * @access private
     */
    private function _processJumpto($result)
    {
        if (isset($_REQUEST['jumpto']) && is_numeric($_REQUEST['jumpto'])) {
            $i = intval($_REQUEST['jumpto'] - 1);
            if (isset($result['response']['docs'][$i])) {
                $record = RecordDriverFactory::initRecordDriver(
                    $result['response']['docs'][$i]
                );
                $jumpUrl = '../Record/' . urlencode($record->getUniqueID());
                header('Location: ' . $jumpUrl);
                die();
            }
        }
    }
}

?>
