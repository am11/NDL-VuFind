// For an introduction to the Blank template, see the following documentation:
// http://go.microsoft.com/fwlink/?LinkID=397704
// To debug code on page load in Ripple or on Android devices/emulators: launch your app, set breakpoints, 
// and then run 'window.location.reload()' in the JavaScript Console.
(function() {
    'use strict';

    window.onload = function(){
        init();
    };
 
    document.addEventListener('deviceready', onDeviceReady.bind(this), false);

    function onDeviceReady() {
        // Handle the Cordova pause and resume events
        document.addEventListener('pause', onPause.bind(this), false);
        document.addEventListener('resume', onResume.bind(this), false);

        init();
    };

    function onPause() {
        // This application has been suspended. Save application state here.
    };

    function onResume() {
        // This application has been reactivated. Restore application state here.
    };

    function getOptions(method, outFormat) {
        document.getElementById('txtStatus').innerHTML = "";
        document.getElementById('txtStatusText').innerHTML = "";
        document.getElementById('txtResult').innerHTML = "";
 
        var options = {};
        options['api-key'] = "xxx-xxxx-xxx"; // random keys
        options.method = method;
        options['output-format'] = outFormat;
 
        if (options.method === 'getContentTypes') {
            // there isn't any additional options to be add for this method
        } else if (options.method === 'getSearchTypes') {
            // there isn't any additional options to be add for this method
        } else if (options.method === 'getSearchResults') {
            var contentType = document.querySelector('select[name=slcContentTypes] > option:checked').value;
            options['content-type'] = [];
            options['content-type'].push(contentType);
 
            var slcGroupAssociation = document.getElementById('slcGroupAssociation');
            var groupAssociation = slcGroupAssociation.options[slcGroupAssociation.selectedIndex].value;
            options['search-group'] = { 'group-association': groupAssociation };
            options['search-group']['group-items'] = [];

            var searchGroups = document.querySelectorAll('fieldset[name=searchGroup]');
 
            for(var i = 0; i < searchGroups.length; i++){
 
                var termGroup = [];
                var itemTuples = searchGroups[i].querySelectorAll('fieldset[name=itemTuple]');

                for(var j = 0; j < itemTuples.length; j++){
                    var term = itemTuples[j].querySelector('input[name=txtSearchTerm]').value;
                    var type = itemTuples[j].querySelector('select[name=slcSearchTermType] > option:checked').value;
                    termGroup.push({
                        'search-term': term,
                        'search-type': type
                    });
                }

                var groupItem = {};
                groupItem['term-group'] = termGroup;
                groupItem['term-association'] = searchGroups[i].querySelector('select[name=slcTermAssociation] > option:checked').value;
                options['search-group']['group-items'].push(groupItem);

                termGroup = []; // reset term group
            }

        } else if (options.method === 'getReadingList') {

        }

        return options;
    }
 
    function init() {
        document.getElementById('btnSearch').addEventListener('click', function() {
            executeSearch();
        }, false);
 
        document.getElementById('btnClear').addEventListener('click', function() {
            clearSearch();
        }, false);
 
        callServer(getOptions("getSearchTypes", "xml"), function(searchTypes){
            var error = searchTypes.querySelectorAll('parsererror');
            if(error.size > 0){
                console.log("error while calling the method getSearchTypes");
                console.log(error[0].innerHTML);
                document.getElementById('txtStatus').innerHTML = "";
                document.getElementById('txtStatusText').innerHTML = "error";
                document.getElementById('txtResult').innerHTML = "0";
            }
            else{
                   var selects = document.querySelectorAll('select[name=slcSearchTermType]');
                   for (var i = 0; i < selects.length; i++) {
                        var types = searchTypes.querySelectorAll('*');
                        for(var j = 0; j < types.length;j++){
                            var option = document.createElement('option');
                            option.text = types[j].innerHTML;
                            option.value = types[j].tagName;
                            selects[i].add(option);
                        }
                   }
            }
        });
 
        callServer(getOptions("getContentTypes", "xml"), function(contentTypes){
            var error = contentTypes.querySelectorAll('parsererror');
            if(error.length > 0){
                console.log("error while calling the method getContentTypes");
                console.log(error[0].innerHTML);
                document.getElementById('txtStatus').innerHTML = "";
                document.getElementById('txtStatusText').innerHTML = "error";
                document.getElementById('txtResult').innerHTML = "0";
            }
            else{
                var select = document.querySelector('select[name=slcContentTypes]');
                var typesKey = contentTypes.querySelectorAll('key');
                var typesValue = contentTypes.querySelectorAll('value');
                for(var i = 0; i < typesKey.length && i < typesValue.length; i++){
                    var option = document.createElement('option');
                    option.text = typesValue[i].innerHTML;
                    option.value = typesKey[i].innerHTML;
                    select.add(option);
                }
            }
        });
    }

    function callServer(queryString, postProcesFunction) {
        var url = "http://demola-finna-kktest.lib.helsinki.fi/vufind/api.php?";
        var xmlhttp;
 
        if(window.XMLHttpRequest){
            xmlhttp = new XMLHttpRequest();
        }
        else
        {// Let's support IE6 and IE5!
            xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        }

        xmlhttp.onreadystatechange = function() {
            if (xmlhttp.readyState === 4 && xmlhttp.status === 200) {
                postProcesFunction(parseResponse(xmlhttp.responseText, queryString["output-format"]));
                document.getElementById('txtStatus').innerHTML = xmlhttp.status;
                document.getElementById('txtStatusText').innerHTML = xmlhttp.statusText;
                document.getElementById('txtResult').innerHTML = xmlhttp.responseText.length;
            }
        };
 
        //document.getElementById('print').innerHTML = url + JSON.stringify(queryString);
        xmlhttp.open('GET', url + JSON.stringify(queryString), true);
        xmlhttp.send();

    }
 
    function executeSearch(){
        var url = "http://demola-finna-kktest.lib.helsinki.fi/vufind/api.php?";
        var format = document.querySelector('select[name=outputFormat] > option:checked').value;;
        var queryString = getOptions("getSearchResults", format);

        var a = document.querySelector('a[name=linkToResult]');
        a.setAttribute("href", url + JSON.stringify(queryString));
        a.innerHTML = "link to file";
        callServer(queryString, function(searchResult){
            var resultDisplay = document.querySelector('div[name=resultDisplay]');
            var fwrapper = document.createElement('fieldset');
                
            for(var i = 0; i < searchResult.length; i++){
                var f = document.createElement('fieldset');
 
                for(var key in searchResult[i]){
                   console.log(key);
                   var p = document.createElement('p');
                   p.innerHTML = "<b>" + key + "</b> = " + searchResult[i][key];
                   f.appendChild(p);
                }
                fwrapper.appendChild(f);
            }
 
            resultDisplay.appendChild(fwrapper);
        });
    }
 
    function clearSearch(){
        var resultDisplay = document.querySelector('div[name=resultDisplay]');
        while (resultDisplay.hasChildNodes()) {
            resultDisplay.removeChild(resultDisplay.lastChild);
        }
    }

    function parseResponse(data, view) {
        switch (view) {
            case 'json':
                //return JSON.stringify(data);
                return JSON.parse(data);
            case 'xml':
                return new DOMParser().parseFromString(data, 'text/xml').documentElement;
            case 'yml':
            case 'yaml':
                //return YAML.stringify(YAML.parse(data));
                return YAML.parse(data);
            default:
        }
    }

    function htmlEscape(str) {
        return view !== 'xml' ? str :
           String(str)
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }
})();
                     
function addSearchTerm(name){
    var id = name.getAttribute('id');
    var elementParent = document.getElementById(id);
    
    var f = document.createElement("fieldset");
    f.setAttribute("name", "itemTuple");
                     
    var element = elementParent.querySelectorAll('fieldset[name="itemTuple"]')[0];
    f.innerHTML = element.innerHTML;
    elementParent.insertBefore(f, elementParent.childNodes[elementParent.childNodes.length - 4]);
}

function addSearchGroup(){
    var elementParent = document.getElementsByClassName("app")[0];
    var num = elementParent.childNodes.length - 18;
    var d = document.createElement("div");
    d.innerHTML =   "<fieldset name='searchGroup' id='searchGroup" + (num + 1) + "'> \
                        <fieldset name='itemTuple'> \
                            <div> \
                                Search Term: \
                                <input type='text' name='txtSearchTerm' /> \
                                in \
                                <select name='slcSearchTermType'></select> \
                            </div> \
                        </fieldset> \
                        <p onclick='addSearchTerm(searchGroup" + (num + 1) + ")'>Add more</p> \
                        <div> \
                            Term Association: \
                            <select name='slcTermAssociation'> \
                                <option value='AND'>ALL terms (AND)</option> \
                                <option value='OR'>ANY terms (OR)</option> \
                                <option value='NOT'>NO terms (NOT)</option> \
                            </select> \
                        </div> \
                     </fieldset>";
    elementParent.insertBefore(d, elementParent.childNodes[elementParent.childNodes.length - 12]);
    
    var element = elementParent.querySelectorAll('select[name="slcSearchTermType"]')[0];
    d.innerHTML = d.innerHTML.replace('<select name="slcSearchTermType"></select>', "<select name='slcSearchTermType'>" + element.innerHTML + "</select>");
}
