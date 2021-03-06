<?php
/**
 * Solr Autocomplete Module
 *
 * PHP version 5
 *
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
 * @package  Autocomplete
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/autocomplete Wiki
 */
require_once 'sys/Autocomplete/Interface.php';
require_once 'sys/Autocomplete/SolrAutocomplete.php';

/**
 * Solr Autocomplete Module
 *
 * This class provides suggestions by using the local Solr index.
 *
 * @category VuFind
 * @package  Autocomplete
 * @author   Kalle Pyykkönen <kalle.pyykkonen@helsinki.fi>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/autocomplete Wiki
 */
class FinnaSolrAutocomplete extends SolrAutocomplete implements AutocompleteInterface
{

    /**
     * getSuggestions
     *
     * This method returns an array of strings matching the user's query for
     * display in the autocomplete box. Modified to use the field that includes
     * the query string.
     *
     * @param string $query The user query
     *
     * @return array        The suggestions for the provided query
     * @access public
     */
    public function getSuggestions($query)
    {
        $this->searchObject->disableLogging();
        $this->searchObject->setBasicQuery(
            $this->mungeQuery($query), $this->handler
        );
        $this->searchObject->setSort($this->sortField);
        foreach ($this->filters as $current) {
            $this->searchObject->addFilter($current);
        }

        // restrict query to display fields by building a hidden OR filter
        $hiddenFilter = '';
        foreach ($this->displayField as $field) {
            if ($hiddenFilter !== '') {
                $hiddenFilter .= ' OR ';
            }
            $hiddenFilter .= "$field:'$query'";
        }
        $this->searchObject->addHiddenFilter($hiddenFilter);

        // Perform the search:
        $result = $this->searchObject->processSearch(true);
        $resultDocs = isset($result['response']['docs']) ?
            $result['response']['docs'] : array();
        $this->searchObject->close();

        // Build the recommendation list:
        $results = array();
        $normalizedQuery = self::normalize($query);
        foreach ($resultDocs as $current) {
            foreach ($this->displayField as $field) {
                if (isset($current[$field])) {
                    $fields = is_array($current[$field]) ? $current[$field] : array($current[$field]);
                    foreach ($fields as $fieldContent) {
                        $normalizedFieldContent = self::normalize($fieldContent);
                        if (stristr($normalizedFieldContent, $normalizedQuery)) {
                            $results[] = $fieldContent;
                            break 2;
                        }                            
                    }
                }
            }
        }
        foreach ($results as &$str) {
            $str = str_replace(array(', ', ':', ';', '%', '*', '"', '=', '~'), ' ', $str);
            $str = preg_replace('/\s+/', ' ', $str);
            $str = rtrim($str, ' .');
        }

        return array_unique($results);
    }
    function normalize ($str) {
        return preg_replace('~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml|caron);~i', '$1', 
                            htmlentities($str, ENT_COMPAT, 'UTF-8'));
    }
}