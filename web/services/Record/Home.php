<?php
/**
 * Home action for Record module
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
 * @package  Controller_Record
 * @author   Andrew S. Nagy <vufind-tech@lists.sourceforge.net>
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
require_once 'Action.php';

/**
 * Home action for Record module
 *
 * @category VuFind
 * @package  Controller_Record
 * @author   Andrew S. Nagy <vufind-tech@lists.sourceforge.net>
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
class Home extends Action
{
    /**
     * Process incoming parameters and display the page.
     *
     * @return void
     * @access public
     */
    public function launch()
    {
        global $configArray, $interface;

        // Execute Default Tab
        $defaultTab = isset($configArray['Site']['defaultRecordTab']) ?
            $configArray['Site']['defaultRecordTab'] : 'Holdings';
            
        // Get number of comments for this record
        require_once 'services/MyResearch/lib/Comments.php';
        $comments = new Comments();
        $commentCount = $comments->getCommentCount($_REQUEST['id']);    
        $interface->assign(compact('commentCount'));

        // We need to do a whole bunch of extra work to determine the default
        // tab if we have the hideHoldingsTabWhenEmpty setting turned on; only
        // do this work if we absolutely have to!
        if (isset($configArray['Site']['hideHoldingsTabWhenEmpty'])
            && $configArray['Site']['hideHoldingsTabWhenEmpty']
            && $defaultTab == "Holdings"
        ) {
            $db = ConnectionManager::connectToIndex();
            if (!($record = $db->getRecord($_REQUEST['id']))) {
                PEAR::raiseError(new PEAR_Error('Record Does Not Exist'));
            }
            $recordDriver = RecordDriverFactory::initRecordDriver($record);
            $showHoldingsTab = $recordDriver->hasHoldings();

            $defaultTab = $showHoldingsTab ? 'Holdings' : 'Description';
        }

        include_once $defaultTab . '.php';
        $service = new $defaultTab();
        $service->recordHit();
        $service->launch();
    }
}

?>