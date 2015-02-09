<?php

require_once 'key_factory.php';
require_once 'XML/Serializer.php';


require_once 'sys/ISBN.php';
require_once 'RecordDrivers/Factory.php';
require_once 'services/MyResearch/lib/Search.php';
require_once 'services/MyResearch/lib/User.php';
require_once 'sys/VuFindDate.php';

require_once 'services/Admin/Config.php';
require_once 'sys/ConfigArray.php';
require_once 'sys/ConnectionManager.php';
require_once 'sys/Interface.php';
require_once 'sys/SearchObject/Factory.php';
require_once 'sys/Translator.php';
require_once 'sys/VuFindCache.php';

/**
 * FINNA API Response class
 *
 * This is a simple datastructure which
 * represents API response. The objects
 * of this class are meant to be used
 * by API class internally.
 *
 */
final class APIResponse
{
    public $contentType;
    public $statusCode;
    public $message;
}

/**
 * FINNA API class
 *
 * This is the main controller class
 * for handling API calls based on the
 * requested content types.
 *
 */
final class API extends Singleton
{

    public function callAPI($params)
    {
        global $configArray;
        global $translator;

        $configArray = readConfig();
        $translator = new I18N_Translator(
            array($configArray['Site']['local'] . '/lang', $configArray['Site']['local'] . '/lang_local'),
                $configArray['Site']['language'],
                $configArray['System']['debug']
            );

        $results = $this->getResults($params);
        $response = $this->getResponse($params, $results);

        header('Content-Type: ' . $response->contentType);

        echo $response->message;
    }

    private function getResults($params)
    {
        $key_manager = KeyFactory::getInstance();

        if(!$key_manager->validate($params['api-key'], $params['token']))
        {
            return array('error' => 'Authentication failed.');
        }

        $params['method'] = isset($params['method']) ? $params['method'] : '';

        switch($params['method'])
        {
            case 'getContentTypes'    :    return $this->getContentTypes()          ;  break;
            case 'getSearchTypes'     :    return $this->getSearchTypes()           ;  break;
            case 'getSearchResults'   :    return $this->getSearchResults($params)  ;  break;
            case 'getReadingList'     :    return $this->getReadingList($params)    ;  break;
            default                   :    return array('error' => 'No such method found.');
        }
    }

    private function getContentTypes()
    {
        $searchObject = SearchObjectFactory::initSearchObject();

        $searchObject->initAdvancedFacets();
        $searchObject->processSearch();

        $list = $searchObject->getFacetList();
        $nestedList = $list['format']['list'];
        $finalArray = array();

        while (list($key, $val) = each($nestedList))
        {
            $hash = base_convert(md5($val['untranslated']), 16, 10);

            if($val['untranslated'][0] === '0')
            {
                $finalArray[$hash]['key'] = $val['untranslated'];
                $finalArray[$hash]['value'] = $val['value'];
            }
            else
            {
                $slugs = explode('/', $val['untranslated']);
                $key = '0/' . $slugs[1] . '/';
                $parentHash = base_convert(md5($key), 16, 10);

                $finalArray[$parentHash][$hash]['key'] = $val['untranslated'];
                $finalArray[$parentHash][$hash]['value'] = $val['value'];
            }
        }

//        $searchObject->close();

        return $finalArray;
    }

    private function getSearchTypes()
    {
        $searchObject = SearchObjectFactory::initSearchObject();
        $initialArray = $searchObject->getAdvancedTypes();
        $finalArray = array();

        while (list($key, $val) = each($initialArray))
        {
            $finalArray[$key] = translate($val);
        }

        return $finalArray;
    }

    private function getSearchResults($params)
    {
        global $interface;
        global $configArray;

        $siteLocal = $configArray['Site']['local'];
        $s = new SearchEntry();

        $configArray['Site']['url'] = $s->schedule_base_url;

        $interface = new UInterface($siteLocal);
        // transcode API params to $_REQUEST

        $_REQUEST = array('module' => 'Search', 'action' => 'Results');
        $_REQUEST['page'] = isset($params['page']) ? $params['page'] : '';
        $_REQUEST['join'] = isset($params['search-group']['group-association']) ?
                            $params['search-group']['group-association'] : 'and';

        foreach ($params['content-type'] as $key => $value) {
            $params['content-type'][$key] = 'format:"' . $value . '"';
        }

        $_REQUEST['orfilter'] = $params['content-type'];

        if(isset($params['search-group']))
        {
            foreach ($params['search-group']['group-items'] as $key0 => $value0) {
                $_REQUEST['lookfor' . $key0] = array();
                $_REQUEST['bool' . $key0][0] = isset($value0['term-association']) ?
                                               $value0['term-association'] : 'and';
                foreach ($value0['term-group'] as $key1 => $value1) {
                    $_REQUEST['lookfor' . $key0][$key1] = $value1['search-term'];
                    $_REQUEST['type' . $key0][$key1] = $value1['search-type'];
                }
            }
        }

        $_REQUEST['orfilter'] = $params['content-type'];

        $searchObject = SearchObjectFactory::initSearchObject();
        $searchObject->init();

        $result = $searchObject->processSearch(false, true);
//var_dump($searchObject->getSimilarItems());
// $this->db = ConnectionManager::connectToIndex();
// Retrieve the record from the index
//if ($record = $this->db->getRecord('jykdok.758658')) {
//$this->recordDriver = RecordDriverFactory::initRecordDriver($record);
//echo var_dump($this->recordDriver->getSimilarItems());
//}

//echo var_dump($searchObject->getCheckboxFacets());
        //$finalArray = array();
//echo var_dump($searchObject->getThumbnail('large'));
        //foreach($result['response']['docs'] as $key=>$value);
//echo var_dump(array_keys($result['response']));
        return isset($result['response']) ? $result['response']['docs'] : null;
    }

    private function getReadingList($params)
    {
        return null;
    }

    private function getResponse($params, $results)
    {
        $params['output-format'] = isset($params['output-format']) ? $params['output-format'] : '';

        switch($params['output-format'])
        {
            case 'xml'     :    return $this->serializeAsXML($results) ;    break;
            case 'yaml'    :
            case 'yml'     :    return $this->serializeAsYAML($results);    break;
            case 'json'    :
            default        :    return $this->serializeAsJSON($results);
        }
    }

    private function serializeAsJSON($results)
    {
        return $this->buildResponse('application/json', json_encode($results, 128));
    }

    private function serializeAsXML($results)
    {
        $options = array(
            'indent'          => '    ',
            'addDecl'         => true,
            'returnResult'    => true,
            'defaultTagName'  => 'item',
        );
        $serializer = &new XML_Serializer($options);

        return $this->buildResponse('text/xml', $serializer->serialize($results));
    }

    private function serializeAsYAML($results)
    {
        return $this->buildResponse('text/yaml', yaml_emit($results));
    }

    private function buildResponse($contentType, $message)
    {
        $response = new APIResponse();
        $response->statusCode = 200;
        $response->contentType = $contentType . '; charset=utf-8';
        $response->message = $message;

        return $response;
    }
}


header('Access-Control-Allow-Origin: *'); 
$api = API::getInstance();

$api->callAPI(json_decode(urldecode($_SERVER['QUERY_STRING']), 2));

?>
