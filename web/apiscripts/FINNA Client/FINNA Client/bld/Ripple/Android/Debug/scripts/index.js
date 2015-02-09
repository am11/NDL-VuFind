// For an introduction to the Blank template, see the following documentation:
// http://go.microsoft.com/fwlink/?LinkID=397704
// To debug code on page load in Ripple or on Android devices/emulators: launch your app, set breakpoints, 
// and then run 'window.location.reload()' in the JavaScript Console.
(function () {
    'use strict';

    init();
    document.addEventListener('deviceready', onDeviceReady.bind(this), false);

    function onDeviceReady() {
        // Handle the Cordova pause and resume events
        document.addEventListener('pause', onPause.bind(this), false);
        document.addEventListener('resume', onResume.bind(this), false);

        // TODO: Cordova has been loaded. Perform any initialization that requires Cordova here.
        init();
    };

    function onPause() {
        // TODO: This application has been suspended. Save application state here.
    };

    function onResume() {
        // TODO: This application has been reactivated. Restore application state here.
    };

    function getOptions() {
        return {
            view: document.querySelector('input[name=view]:checked').value,
            contentType: document.querySelector('select[name=contentType] > option:checked').value,
            contentSubType: document.querySelector('select[name=contentSubType] > option:checked').value,
            lookFor: document.querySelector('input[name=query]').value
        };
    }

    function init() {
        document.getElementById('getResponseBtn').addEventListener('click', function () {
            callServer(getOptions());
        }, false);
    }

    function callServer(queryString) {
        var xmlhttp = new XMLHttpRequest();

        xmlhttp.onreadystatechange = function () {
            if (xmlhttp.readyState === 4 && xmlhttp.status === 200) {
                document.getElementById('rawResults').innerHTML = htmlEscape(xmlhttp.responseText);
                document.getElementById('results').innerHTML = parseResponse(xmlhttp.responseText, queryString.view);
            }
        };

        xmlhttp.open('GET', 'http://demola-finna-kktest.lib.helsinki.fi/vufind/apiscripts/?view' + queryString.view, true);
        xmlhttp.send();
    }

    function parseResponse(data, view) {
        switch (view) {
            case 'json':
                return JSON.stringify(data);
            case 'xml':
                return new DOMParser().parseFromString(data, 'text/xml').documentElement.innerHTML;
            case 'yml':
            case 'yaml':
                return YAML.stringify(YAML.parse(data));
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
