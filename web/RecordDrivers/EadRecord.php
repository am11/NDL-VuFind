<?php
/**
 * EAD Record Driver
 *
 * PHP version 5
 *
 * Copyright (C) The National Library of Finland 2012.
 * Copyright (C) Villanova University 2010.
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
 * @package  RecordDrivers
 * @author   Ere Maijala <ere.maijala@helsinki.fi>
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @author   Luke O'Sullivan <l.osullivan@swansea.ac.uk>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/other_than_marc Wiki
 */
require_once 'RecordDrivers/IndexRecord.php';
require_once 'Drivers/Hierarchy/HierarchyFactory.php';
require_once 'modules/geshi.php';

/**
 * EAD Record Driver
 *
 * This class is designed to handle EAD records.
 *
 * @category VuFind
 * @package  RecordDrivers
 * @author   Ere Maijala <ere.maijala@helsinki.fi>
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @author   Eoghan O'Carragain <Eoghan.OCarragan@gmail.com>
 * @author   Luke O'Sullivan <l.osullivan@swansea.ac.uk>
 * @author   Lutz Biedinger <lutz.Biedinger@gmail.com>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/other_than_marc Wiki
 */
class EadRecord extends IndexRecord
{
    /**
     * Constructor.  We build the object using all the data retrieved
     * from the (Solr) index (which also happens to include the
     * 'fullrecord' field containing raw metadata).  Since we have to
     * make a search call to find out which record driver to construct,
     * we will already have this data available, so we might as well
     * just pass it into the constructor.
     *
     * @param array $indexFields All fields retrieved from the index.
     *
     * @access public
     */
    public function __construct($indexFields)
    {
        parent::__construct($indexFields);
        
        $this->record = simplexml_load_string($this->fields['fullrecord']);
    }
    
    /**
     * Assign necessary Smarty variables and return a template name to
     * load in order to display core metadata (the details shown in the
     * top portion of the record view pages, above the tabs).
     *
     * @return string Name of Smarty template file to display.
     * @access public
     */
    public function getCoreMetadata()
    {
        global $configArray;
        global $interface;
        
        $template = parent::getCoreMetadata();

        $interface->assign('coreIsPartOfArchiveSeries', $this->isPartOfArchiveSeries());
        
        $interface->assign('coreYearRange', $this->getYearRange());
        $interface->assign('coreOrigination', $this->getOrigination());
        $interface->assign('coreOriginationId', $this->getOriginationID());
        $interface->assign('corePhysicalLocation', $this->getPhysicalLocation());
        if ($this->record->did->daogrp) {
            $interface->assign('coreDigitizedMaterial', true);
        }
        $interface->assign('coreIdentifier', $this->getIdentifier());
        
        $interface->assign('coreSubjects', isset($this->fields['topic']) ? $this->fields['topic'] : array());
        $interface->assign('coreGeographicSubjects', isset($this->fields['geographic']) ? $this->fields['geographic'] : array());

        if (isset($this->fields['measurements'])) {
            $interface->assign('coreMeasurements', $this->fields['measurements']);
        }

        
        $datasource = $this->fields['datasource_str_mv'][0];
        if (isset($configArray['Record']['ead_document_order_link_template'][$datasource])) {
            $interface->assign('coreDocumentOrderLinkTemplate', $configArray['Record']['ead_document_order_link_template'][$datasource]);
        }
        if (isset($configArray['Record']['ead_usage_permission_request_link_template'][$datasource])) {
            $interface->assign('coreUsagePermissionRequestLinkTemplate', $configArray['Record']['ead_usage_permission_request_link_template'][$datasource]);
        }
        if (isset($configArray['Record']['ead_external_link_template'][$datasource])) {
            $interface->assign('coreExternalLinkTemplate', $configArray['Record']['ead_external_link_template'][$datasource]);
        }

        $unitId = $this->record->xpath("did/unitid");
        if (count($unitId)) {
            $interface->assign('coreReferenceCode', (string)$unitId[0]);
        }

        return 'RecordDrivers/Ead/core.tpl';
    }
    
    /**
    * Assign necessary Smarty variables and return a template name for the current
    * view to load in order to display a summary of the item suitable for use in
    * search results.
    *
    * @param string $view The current view.
    *
    * @return string      Name of Smarty template file to display.
    * @access public
    */
    public function getSearchResult($view = 'list')
    {
        global $interface;
        
        $template = parent::getSearchResult($view);
        
        $interface->assign('summYearRange', $this->getYearRange());
        $interface->assign('summOrigination', $this->getOrigination());
        $interface->assign('summOriginationId', $this->getOriginationID());
        $interface->assign('summPhysicalLocation', $this->getPhysicalLocation());

        $interface->assign('isPartOfArchiveSeries', $this->isPartOfArchiveSeries());
        
        return 'RecordDrivers/Ead/result-' . $view . '.tpl';
    }
        
    /**
     * Get the collection data to display.
     *
     * @return void
     * @access public
     */
    public function getCollectionMetadata()
    {
        global $interface;

        parent::getCollectionMetadata();
        
        $interface->assign('collYearRange', $this->getYearRange());
        
        // Send back the template name:
        return 'RecordDrivers/Hierarchy/collection-info.tpl';
    }

    /**
     * Check if an item is part of an archive series.
     * 
     * @return array
     * @access protected
     */
    public function isPartOfArchiveSeries()
    {
        return isset($this->fields['hierarchy_parent_id'][0]) 
            && isset($this->fields['hierarchy_top_id'][0])
            && $this->fields['hierarchy_parent_id'][0] != $this->fields['hierarchy_top_id'][0]
            && $this->fields['hierarchy_top_id'] != $this->fields['id'];
    }

    /**
     * Return an associative array of image URLs associated with this record (key = URL,
     * value = description), if available; false otherwise. 
     *
     * @param string $size Size of requested images
     *
     * @return mixed
     * @access protected
     */
    public function getAllImages($size = 'large')
    {
        $urls = array();
        $url = '';
        $role = $size == 'large' 
            ? 'image_reference'
            : 'image_thumbnail';

        foreach ($this->record->xpath("did/daogrp/daoloc[@role=\"$role\"]") as $node) {
            $url = (string)$node->attributes()->href;
            $urls[$url] = '';
        }
        return $urls;
    }
    
    /**
     * Return a URL to a thumbnail preview of the record, if available; false
     * otherwise.
     *
     * @param array $size Size of thumbnail (small, medium or large -- small is
     * default).
     *
     * @return mixed
     * @access public
     */
    public function getThumbnail($size = 'small')
    {
        global $configArray;
        if (isset($this->fields['thumbnail']) && $this->fields['thumbnail']) {
            return $configArray['Site']['url'] . '/thumbnail.php?id=' .
                urlencode($this->getUniqueID()) . '&size=' . urlencode($size);
        }
        return false;
    }

    /**
     * Return the actual URL where a thumbnail can be retrieved, if available; false
     * otherwise.
     *
     * @param array $size Size of thumbnail (small, medium or large -- small is
     * default).
     *
     * @return mixed
     * @access public
     */
    public function getThumbnailURL($size = 'small')
    {
        $urls = $this->getAllImages($size);
        return $urls ? reset(array_keys($urls)) : false;
    }
        
    /**
    * Return an associative array of URLs associated with this record (key = URL,
    * value = description).
    *
    * @return array
    * @access protected
    */
    protected function getURLs()
    {
        $urls = array();
        $url = '';
        foreach ($this->record->xpath('//daoloc') as $node) {
            $url = (string)$node->attributes()->href;
            if (isset($node->attributes()->role) && $node->attributes()->role == 'image_thumbnail') {
                continue;
            }

            $desc = $url;
            if ($node->daodesc) {
                if ($node->daodesc->p) {
                    $desc = (string)$node->daodesc->p;
                } else {
                    $desc = (string)$node->daodesc;
                }
            } else {
                if ($p = $node->xpath('parent::*/daodesc/p')) {
                    $desc = $p[0];
                }
            }
            $urls[$url] = $desc;
        }
        
        // Portti links parsed from bibliography
        foreach ($this->record->xpath('//bibliography') as $node) {
            if (preg_match('/(.+) (http:\/\/wiki\.narc\.fi\/portti.*)/', (string)$node->p, $matches)) {
                $urls[$matches[2]] = $matches[1];
            }
        } 
        return $urls;
    }
    
    /**
     * Get the date range of the record as a year or range of years 
     *
     * @return string
     * @access protected
     */
    protected function getYearRange()
    {
        if (isset($this->fields['unit_sdaterange'])) {
            $range = explode(' ', $this->fields['unit_sdaterange'], 2);
            if (!$range) {
                return null;
            }
            $range[0] *= 86400;
            $range[1] *= 86400;
            $startDate = new DateTime("@{$range[0]}");
            $endDate = new DateTime("@{$range[1]}");

            $startYear = $startDate->format('Y');
            $endYear = $endDate->format('Y');
            $yearRange = '';
            if ($startYear != '-9999') {
                $yearRange .= $startYear;
            }
            // print out a range (xxxx-xxxx) if $endYear != $startYear
            if ($endYear != $startYear) {
                $yearRange .= '-';
                if ($endYear != '9999') {
                    $yearRange .= $endYear;
                }
            }
            return $yearRange;
        }
        return '';
    }
    
    /**
     * Get an array of information about record history, obtained in real-time
     * from the Hierarchy Driver.
     *
     * @return array
     * @access protected
     */
    protected function getRealTimeHistory()
    {
        global $configArray;
        $hierarchyType = $this->getHierarchyType();
        // Get Acquisitions Data
        $id = $this->getUniqueID();
        if ($hierarchy = HierarchyFactory::initHierarchy($hierarchyType)) {
            $result = $hierarchy->getPurchaseHistory($id);
            if (PEAR::isError($result)) {
                PEAR::raiseError($result);
            }
            return $result;
        }
        return array();
    }
    
    /**
     * Get an array of information about record holdings, obtained in real-time
     * from the Hierarchy Driver.
     *
     * @param array $patron An array of patron data
     *
     * @return array
     * @access protected
     */
    protected function getRealTimeHoldings($patron = false)
    {
        global $configArray;
    
        // Get ID and connect to catalog
        $id = $this->getUniqueID();
        $hierarchyType = $this->getHierarchyType();
        if ($hierarchy = HierarchyFactory::initHierarchy($hierarchyType)) {
            include_once 'sys/HoldLogic.php';
            $holdLogic = new HoldLogic($hierarchy);
            return $holdLogic->getHoldings($id, $patron);
        }
        return false;
    }
    
    /**
     * Get an array of status Information
     *
     * @return array
     * @access protected
     */
    public function getRealTimeStatus()
    {
        global $configArray;
        $hierarchyType = $this->getHierarchyType();
        // Get ID and connect to catalog
        $id = $this->getUniqueID();
        if ($hierarchy = HierarchyFactory::initHierarchy($hierarchyType)) {
            return $hierarchy->getStatus($id);
        }
        return false;
    }
    
    /**
     * Check if an item has holdings in order to show or hide the holdings tab
     *
     * @param array $patron An array for patron information
     *
     * @return bool
     * @access public
     */
    public function hasRealTimeHoldings($patron = false)
    {
        global $configArray;
        $hierarchyType = $this->getHierarchyType();
        // Get ID and connect to catalog
        $id = $this->getUniqueID();
        if ($hierarchy = HierarchyFactory::initHierarchy($hierarchyType)) {
            return $hierarchy->hasHolding($id, $patron);
        }
        return false;
    }
    
    /**
     * Get the collection data to display.
     *
     * @return void
     * @access public
     */
    public function getCollectionRecord()
    {
        global $interface;
    
        parent::getCollectionRecord();
        $interface->assign('collDateDescription', $this->getDateDescription());
        $interface->assign('collExtent', $this->getExtent());
    
        // Send back the template name:
        return 'RecordDrivers/Hierarchy/collection-record.tpl';
    }
    
    /**
     * Get the text of the part/section portion of the Date Description.
     *
     * @return string
     * @access protected
     */
    protected function getDateDescription()
    {
        return null;
    }
    
    /**
     * Get the text of the part/section portion of the Date Description.
     *
     * @return string
     * @access protected
     */
    protected function getExtent()
    {
        return null;
    }
    
    /**
     * Get an array of all the dedup data associated with the record.
     *
     * @return array
     * @access protected
     */
    protected function getDedupData()
    {
        return array();
    }
    
    /**
     * Get origination
     * 
     * @return string
     */
    protected function getOrigination()
    {
        return isset($this->record->did->origination) ? (string)$this->record->did->origination->corpname : '';
    }
        
    /**
     * Get origination Id
     * 
     * @return string
     */
    protected function getOriginationId()
    {
        return isset($this->record->did->origination) ? (string)$this->record->did->origination->corpname->attributes()->authfilenumber : '';
    }

    /**
     * Check if an item has holdings in order to show or hide the holdings tab
     *
     * @return bool
     * @access public
     */
    public function hasHoldings()
    {
        return false;
    }

    /**
     * Assign necessary Smarty variables and return a template name to
     * load in order to display the full record information on the Staff
     * View tab of the record view page.
     *
     * @return string Name of Smarty template file to display.
     * @access public
     */
    public function getStaffView()
    {
        global $interface;

        // Get Record as XML
        $xml = trim($this->fields['fullrecord']);

        // Prettify XML
        $doc = new DOMDocument;
        $doc->preserveWhiteSpace = false;
        if ($doc->loadXML($xml)) {
            $doc->formatOutput = true;
            $geshi = new GeSHi($doc->saveXML(), 'xml');
            $geshi->enable_classes(); 
            $interface->assign('record', $geshi->parse_code());
        }
        $interface->assign('details', $this->fields);
        
        return 'RecordDrivers/Ead/staff.tpl';
    }

    /**
     * Get access restriction notes for the record.
     *
     * @return array
     * @access protected
     */
    protected function getAccessRestrictions()
    {
        return isset($this->record->accessrestrict->p) ? $this->record->accessrestrict->p : array();
    }

    /**
     * Get notes on bibliography content.
     *
     * @return array
     * @access protected
     */
    protected function getBibliographyNotes()
    {
        $bibliography = array();
        foreach ($this->record->xpath('//bibliography') as $node) {
            // Filter out Portti links, they're displayed in links
            if (!preg_match('/(.+) (http:\/\/wiki\.narc\.fi\/portti.*)/', (string)$node->p)) {
                $bibliography[] = (string)$node->p;
            }
        } 
        return $bibliography;
    }

    /**
     * Get physical location related to the record.
     *
     * @return array
     * @access protected
     */
    protected function getPhysicalLocation()
    {
        return isset($this->record->did->physloc) ? $this->record->did->physloc : array();
    }

    /**
     * Get notes on finding aids related to the record.
     *
     * @return array
     * @access protected
     */
    protected function getFindingAids()
    {
        return isset($this->record->otherfindaid->p) ? $this->record->otherfindaid->p : array();
    }

    /**
     * Get identifier
     *
     * @return array
     * @access protected
     */
    protected function getIdentifier()
    {
        return isset($this->record->did->unitid->attributes()->{'identifier'}) 
            ? (string)$this->record->did->unitid->attributes()->{'identifier'}
            : (string)$this->record->did->unitid;
    }

    /**
     * Get an array of physical descriptions of the item.
     *
     * @return array
     * @access protected
     */
    protected function getPhysicalDescriptions()
    {
        $physDesc = parent::getPhysicalDescriptions();
        if (isset($this->fields['material'])) {
            $physDesc = array_merge($physDesc, $this->fields['material']);
        }
        return $physDesc;
    }

    /**
     * Get an array of summary strings for the record.
     *
     * @return array
     * @access protected
     */
    protected function getSummary()
    {
        // We need to return an array, so if we have a description, turn it into an
        // array as needed (it should be a flat string according to the default
        // schema, but we might as well support the array case just to be on the safe
        // side. If needed, handle a special case where the indexed description consists 
        // of several joined paragraphs:
        if (isset($this->fields['description'])
            && !empty($this->fields['description'])
        ) {
            return is_array($this->fields['description'])
                ? $this->fields['description'] : explode('   /   ', $this->fields['description']);
        }

        // If we got this far, no description was found:
        return array();
    }

}
