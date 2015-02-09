﻿// For an introduction to the Blank template, see the following documentation:
// http://go.microsoft.com/fwlink/?LinkID=397704
// To debug code on page load in Ripple or on Android devices/emulators: launch your app, set breakpoints, 
// and then run 'window.location.reload()' in the JavaScript Console.
(function () {
    'use strict';
    var view;

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

    function init() {
        view = 'json';

        document.getElementById('getResponseBtn').addEventListener('click', function () {
            callServer(view);
        }, false);
    }

    function callServer(view) {
        var xmlhttp = new XMLHttpRequest();

        xmlhttp.onreadystatechange = responseReaction(xmlhttp);
        xmlhttp.open('GET', 'http://demola-finna-kktest.lib.helsinki.fi/vufind/apiscripts/?view=' + view, true);
        xmlhttp.send();
    }

    function responseReaction(xmlhttp) {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            document.getElementById('results').innerHTML = xmlhttp.responseText;
        }
    }
})();