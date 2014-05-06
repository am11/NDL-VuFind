var metalibPage;
var metalibInited = false;

function metalibSearch(step, set, saveHistory, scrollToTop) 
{
    // Remove possible error messages from previous search
    $('#deferredResults #content.error').empty();
    $('#deferredResults #content').removeClass("error").removeClass("fatalError");
    $('#deferredResults #content .metalibError').remove();


    var currentPage = metalibPage;
    var changeSet = set !== null && set != metalibSet;

    if (step !== null) {
        metalibPage += step;
        metalibPage = Math.max(1, metalibPage);
    }

    // Load results using Ajax if: 
    //   - search set was not changed
    //   - browser supports history writing
    // Otherwise, whole page is reloaded.
    var historySupport = window.history && window.history.pushState;
    var useAJAXLoad = !metalibInited || historySupport;

    var replace = {};
    replace.set = encodeURIComponent(set ? set : metalibSet);       
    metalibSet = replace.set;
    
    replace.page = metalibPage;        

    // Tranform current url into an 'Ajaxified' version.
    // Update url parameters 'page' and 'set' if needed.
    var parts = fullPath.split('&');
    var url = parts.shift();
    if (useAJAXLoad && !changeSet) {
        url = url.replace('/MetaLib/Search?', '/AJAX/AJAX_MetaLib?');
    }

    for (var i=0; i<parts.length; i++) {
        var param = parts[i].split('=');
        var key = param[0];
        var val = param[1];
        if (key == 'method') {
            continue;
        }

        // add parameters that are included as such
        if (!(key in replace)) {
            url += '&' + key + '=' + val;
        }
    }
    
    // add modified parameters
    $.each(replace, function(key, val) {
        url += '&' + key + '=' + val;
    });

    url += '&method=metalib';

    
    var scrollToRecord = window.location.hash;


    if (!useAJAXLoad || changeSet) {
        top.location = url;        
        return false;
    } else {
        if (scrollToRecord) {
            url += window.location.hash;
        }
    }

    metalibToggleLoading(true);
    
    var contentHolder = $('.resultListContainer .content');
    contentHolder.removeClass('no-hits');
    $('#deferredResults #content').load(url, function(response, status, xhr, datatype) {
        // hide search set from loader
        $('.metalibLoading .setNotification').toggle(false);

        metalibToggleLoading(false);
        
        if (status == 'error') {
            var obj = $(response).find('.fatalError');
            var errMsg;
            if (obj.length) {
                // Handle PEAR error page by copying error message content
                errMsg = obj.html();
            } else {
                // Use generic error message
                errMsg = trError;
            }
            $(this).html(errMsg);
            $(this).addClass("error").addClass("fatalError");        
        } else {
            if ($('ul.recordSet').length) {
                metalibInitPagination();
                metalibScrollToRecord();
            } else {
                contentHolder.addClass('no-hits');
                $('.searchTerms').html(trNoHits);
            }
        }
    });
    
    // Save history if supported
    if (saveHistory && historySupport) {
        var state = {page: metalibPage};
        if (metalibSet) {
            state.set = metalibSet;
        }
        var title = '';
        // Restore ajaxified URL before saving history
        var tmp = url.replace('/AJAX/AJAX_MetaLib?', '/MetaLib/Search?');
        window.history.pushState(state, title, tmp);
    }
   
    if (scrollToTop === true) {
        $('html, body').scrollTop($('#searchForm_input').offset().top - 20);
    }

}

function metalibScrollToRecord()
{
    if (window.location.hash) {
        var rec = $("div[id='" + window.location.hash.substr(1) + "']");
        if (rec.length) {
            $(document).scrollTop(rec.offset().top);
        }
    }
}

function metalibToggleLoading(mode)
{
    if (mode) {
        var txt = trMetalibLoading;
        txt = txt.replace('{1}', metalibPage);
        
        $('.metalibLoading h4').html(txt);
        $('.metalibLoading .setNotification span').html(metalibSearchsets[metalibSet]);
    }
    
    var loader = $('.metalibLoading');
    loader.toggleClass("show", mode);
    var h = Math.max(100, $('#deferredResults #content').height()-150);
    loader.height(h);
}

function metalibInitPagination()
{
    function _scrollToTop(obj) {
        // scroll page to top if event was sent from bottom pagination
        return obj.parent().parent().hasClass("paginationBottom");
    }

    // Top pagination
    $(".paginationBack a").on("click", function(e) { 
        metalibSearch(-1, null, true, _scrollToTop($(this))); 
        e.preventDefault(); 
    });

    $(".paginationNext a").on("click", function(e) { 
        metalibSearch( 1, null, true, _scrollToTop($(this))); 
        e.preventDefault(); 
    });

    // Bottom pagination
    $(".paginationFirst a").on("click", function(e) { 
        metalibPage = 1; 
        metalibSearch(null, null, true, _scrollToTop($(this))); 
        e.preventDefault(); 
    });
    
    $(".paginationLast a").on("click", function(e) { 
        var page = $(this).html(); 
        metalibPage = parseInt(page.substring(1, page.length-1));
        metalibSearch(null, null, true, _scrollToTop($(this))); 
        e.preventDefault(); 
    });

    $(".paginationPages a").on("click", function(e) { 
        metalibPage = parseInt($(this).html()); 
        metalibSearch(null, null, true, _scrollToTop($(this))); 
        e.preventDefault(); 
    });
}

function metalibChangeSet(set)
{
    metalibPage = 1;
    metalibSearch(null, set, true, false);
}

function metalibInit(page)
{   
    metalibPage = page;
    $("#searchSets input").on("click", function() { 
        metalibChangeSet($(this).val()); 
    });

    metalibSearch(null, metalibSet, true, false);
    metalibInited = true;

    window.onpopstate = function(e){
        if (e.state){
            metalibPage = e.state.page;
            var set = e.state.set ? e.state.set : null;
            metalibSet = set;

            metalibSearch(null, set, false, true);
        }
    };
}


