/**
 * Initialize common functions and event handlers.
 */
// prevent console.log errors in ie
if (!window.console) console = {log: function() {}};
// disable caching for all AJAX requests
$.ajaxSetup({cache: false});

// set global options for the jQuery validation plugin
$.validator.setDefaults({
    errorClass: 'invalid'
});
    
// add a modified version of the original phoneUS rule 
// to accept only 10-digit phone numbers
$.validator.addMethod("phoneUS", function(phone_number, element) {
    phone_number = phone_number.replace(/[-\s().]+/g, ""); 
    return this.optional(element) || phone_number.length > 9 &&
        phone_number.match(/^(\([2-9]\d{2}\)|[2-9]\d{2})[2-9]\d{2}\d{4}$/);
}, 'Please specify a valid phone number');

$(document).ready(function(){
    // initialize autocomplete
    initAutocomplete();    

    // initialize clearable fields (embedded clear button)
    // Do this before setting focus
    initClearable();
    
    // support "jump menu" dropdown boxes
    $('select.jumpMenu').change(function(){ $(this).parent('form').submit(); });

    // attach click event to the "keep filters" checkbox
    $('#searchFormKeepFilters').change(function() { filterAll(this); });


    // Toggle Keep filters -option

    // detect when mouse is inside search area 
    $('#searchFormContainer').hover(
        function() {
            $(this).addClass("hover");
        },
        function() {
            $(this).removeClass("hover");
        }
    );

    // show when search field is focused
    $('#searchForm_input').focus(function(e) { toggleKeepFiltersOption(true); });

    // show when prefilter is changed
    $("#searchForm_filter").change(function(e) { toggleKeepFiltersOption(true); });

    // hide when mouse is clicked and search field is not focused and mouse is not inside search area
    $(document).mouseup(function() {
        if (!$('#searchForm_input').is(":focus") && !$('#searchFormContainer').hasClass("hover")) {
            toggleKeepFiltersOption(false);
        }
    });

    // preserve active search term and prefilter
    origSearchTerm = $('#searchForm_input').val();
    origPrefilter = $("#searchForm_filter").val();




    // attach click event to the search help links
    /*
    $('a.searchHelp').click(function(){
        window.open(path + '/Help/Home?topic=search', 'Help', 'width=625, height=510');
        return false;
    });
    */

    // attach click event to the advanced search help links
    /*
    $('a.advsearchHelp').click(function(){
        window.open(path + '/Help/Home?topic=advsearch', 'Help', 'width=625, height=510');
        return false;
    });
    */

    // assign click event to searchbox context help
    $('.showSearchHelp').click(function(e) {
        $('div.searchContextHelp').toggle();
        e.preventDefault();
    });
    // assign click event to searchbox context help close image
    $('.hideSearchHelp a').click(function(e) {
        $('div.searchContextHelp').hide();
        e.preventDefault();
    });
    
    // assign click event to "email search" links
    $('a.mailSearch').click(function(e) {
        var id = this.id.substr('mailSearch'.length);
        var $dialog = getLightbox('Search', 'Email', id, null, this.title);
        e.preventDefault();
    });

    // assign action to the "select all checkboxes" class
    $('input[type="checkbox"].selectAllCheckboxes').change(function(){
        var newValue = $(this).attr('checked') ? true : false;
        $(this.form).find('input[type="checkbox"]').attr('checked', newValue);
    });
    
    // attach mouseover event to grid view records
    $('.gridCellHover').mouseover(function() {
        $(this).addClass('gridMouseOver');
    });
    
    // attach mouseout event to grid view records
    $('.gridCellHover').mouseout(function() {
        $(this).removeClass('gridMouseOver');
    });  
    
    // assign click event to "viewCart" links
    $('a.viewCart').click(function(e) {
        var $dialog = getLightbox('Cart', 'Home', null, null, this.title, '', '', '', {viewCart:"1"});
        e.preventDefault();
    });
    
    // Print
    var url = window.location.href;
    if(url.indexOf('?' + 'print' + '=') != -1  || url.indexOf('&' + 'print' + '=') != -1) {
        $("link[media='print']").attr("media", "all");
        window.print();
    }
    
    // toggle facet visibility on click
    $('#sidebarFacets dt').on('click', function() {
        cont = $(this).parent('dl');
        cont.toggleClass('collapsed');
        cont.next('script').next('div.dynatree-facet').toggleClass('collapsed');
    });
    $('#sidebarFacets dt').css('cursor', 'pointer');
    
    //ContextHelp
    contextHelp.init();
    contextHelp.contextHelpSys.load();



});

function toggleMenu(elemId) {
    var elem = $("#"+elemId);
    if (elem.hasClass("offscreen")) {
        elem.removeClass("offscreen");
    } else {
        elem.addClass("offscreen");
    }
}

function moreFacets(name) {
    $("#more"+name).hide();
    $("#narrowGroupHidden_"+name).removeClass("offscreen");
}

function lessFacets(name) {
    $("#more"+name).show();
    $("#narrowGroupHidden_"+name).addClass("offscreen");
}

function filterAll(element, formId) {
    //  Look for filters (specifically checkbox filters)
    if (formId == null) {
        formId = "searchForm";
    }
    $("#" + formId + " :input[type='checkbox'][name='filter[]']")
        .attr('checked', element.checked);

    // switch to default sort mode
    var field = $("#searchForm").find("input[name='sort']");
    field.attr("disabled", $('#searchFormKeepFilters').is(":checked") ? false : "disabled");
}

function extractParams(str) {
    var params = {};
    if (str == null) {
        return params;
    }
    var classes = str.split(/\s+/);
    var i;
    for(i = 0; i < classes.length; i++) {
        if (classes[i].indexOf(':') > 0) {
            var pair = classes[i].split(':');
            params[pair[0]] = pair[1];
        }
    }
    return params;
}

// return unique values from the given array
function uniqueValues(array) {
    var o = {}, i, l = array.length, r = [];
    for(i=0; i<l;i++) {
        o[array[i]] = array[i];
    }
    for(i in o) {
        r.push(o[i]);
    }
    return r;
}

function initAutocomplete() {
	var searchInput = $('#searchForm_input.autocomplete');
	if (searchInput.length === 0)
		return;
	var searchForm = $('#searchForm');
    var lastXhr = null;
	var params = extractParams(searchInput.attr('class'));
	var maxItems = params.maxItems > 0 ? params.maxItems : 10;
	var minLength = params.minLength > 0 ? params.minLength : 3;
	ac = searchInput.autocomplete({
		minLength: minLength,
		select: function(e, ui) { 
		    if (e.keyCode === 13 && searchInput.val() != ui.item.label) {
               searchForm.submit();
               return false;
            }
			searchInput.val(ui.item.label);
			searchForm.submit(); 
		},
	    source: function(request, response) {
	        var type = params.type;
	        if (!type && params.typeSelector) {
	            type = $('#' + params.typeSelector).val();
	        } 
            // Abort previous access if one is defined
            if (lastXhr !== null && lastXhr.hasOwnProperty("abort")) {
                lastXhr.abort();
            }
            var prefilterValue = $('select#searchForm_filter option:selected').val();
            lastXhr = $.ajax({
                cache: false,
	            url: path + '/AJAX/JSON_Autocomplete',
	            data: {method:'getSuggestions',type:type,q:request.term,prefilter:prefilterValue},
	            dataType:'json',
	            success: function(json) {
	                if (json.status == 'OK' && json.data.length > 0) {
	                    response(json.data.slice(0, maxItems));
	                } else {
	                    ac.autocomplete('close');
	                }
	            }
	            });
	    },
        

        open: function(event, ui) {
            $(this).autocomplete("widget").css({
                "width": 420
            });
        }

	    });

	ac.data( "autocomplete" )._renderItem = function(ul, item) {
        ul.css({"width": 420, "overflow": "hidden"});

        var label = item.label.replace(new RegExp("(?![^&;]+;)(?!<[^<>]*)(" 
        				+ $.ui.autocomplete.escapeRegex(this.term) 
        				+ ")(?![^<>]*>)(?![^&;]+;)", "gi"), "<strong>$1</strong>");
        return $("<li></li>")
                .data("item.autocomplete", item)
                .append("<a>" + label + "</a>")
                .appendTo(ul);

    };

}

function initClearable(){
    $('.clearable').clearSearch();
    // update value
    valueContent = $(".clearable").attr("value");
    if (valueContent == null) {
      $(".clearable").val("").change();
    };
}

function initSearchInputListener() {
    var searchInput = $('#searchForm_input');
    var disableListener;
    $(window).keypress(function(e) {
        var letter = String.fromCharCode(e.which);
        if (e && (!$(e.target).is('input[type="text"], textarea') && searchInput.length > 0) 
            && !$(".ui-dialog").is(":visible") && !disableListener) {
            
            // Move cursor to the end of the input
            var tmpVal = searchInput.val();
            searchInput.val(' ').focus().val(tmpVal + letter);
            
            // Scroll to the search form
            $('html, body').animate({
                scrollTop: searchInput.offset().top - 20
            }, 150);
           
            e.preventDefault();
       } 
       disableListener = false;
    });
    
    // Disable on pressing a modifier key
    $(window).keydown(function(e) {
        if (e.metaKey || e.ctrlKey || e.altKey || e.which == 224 || e.which == 91) {
            disableListener = true;
         }
     });
    
    // Re-enable on keyup
    $(window).keyup(function(e) {
        disableListener = false;
    });
    
}


function htmlEncode(value){
    if (value) {
        return jQuery('<div />').text(value).html();
    } else {
        return '';
    }
}

// mostly lifted from http://docs.jquery.com/Frequently_Asked_Questions#How_do_I_select_an_element_by_an_ID_that_has_characters_used_in_CSS_notation.3F
function jqEscape(myid) {
    return String(myid).replace(/(:|\.)/g,'\\$1');
}

function printIDs(ids) {

    var url = '';
    if(ids.length == 0) {
        return false;
    }
    if(ids.length == 1) {
            url =  path + '/Record/' + encodeURIComponent(ids[0]) + '?type=ids&print=true';
    }
    else {
        $(ids).each(function() {
           url += encodeURIComponent(this) + '+'; 
        });
        url =  path + '/Search/Results?lookfor=' + url + '&type=ids&print=true';
    }
    window.open(url);
    return true;
}

var contextHelp = {
        
    init: function() {
        $('body').append('<table cellspacing="0" cellpadding="0" id="contextHelp"><tbody><tr class="top"><td class="left"></td><td class="center"><div class="arrow up"></div></td><td class="right"></td></tr><tr class="middle"><td></td><td class="body"><div id="closeContextHelp"></div><div id="contextHelpContent"></div></td><td></td></tr><tr class="bottom"><td class="left"></td><td class="center"><div class="arrow down"></div></td><td class="right"></td></tr></tbody></table>');
    },
    
    hover: function(listenTo, widthOffset, heightOffset, direction, align, msgText) {     
        $(listenTo).mouseenter(function() {
            contextHelp.contextHelpSys.setPosition(listenTo, widthOffset, heightOffset, direction, align, '', false);
            contextHelp.contextHelpSys.updateContents(msgText);
        });
        $(listenTo).mouseleave(function() {
            contextHelp.contextHelpSys.hideMessage();
        });
    }, 
    
    flash: function(id, widthOffset, heightOffset, direction, align, msgText, duration) {
        this.contextHelpSys.setPosition(id, widthOffset, heightOffset, direction, align);
        this.contextHelpSys.updateContents(msgText);
        setTimeout(this.contextHelpSys.hideMessage, duration);
    },
    
    contextHelpSys: {
        CHTable:"#contextHelp",
        CHContent:"#contextHelpContent",
        arrowUp:"#contextHelp .arrow.up",
        arrowDown:"#contextHelp .arrow.down",
        closeButton:"#closeContextHelp",
        showCloseButton: true,
        curElement:null,
        curOffsetX:0,
        curOffsetY:0,
        curDirection:"auto",
        curAlign:"auto",
        curMaxWidth:null,
        isUp:false,
        load:function(){
            $(contextHelp.contextHelpSys.closeButton).click(contextHelp.contextHelpSys.hideMessage);
            $(window).resize(contextHelp.contextHelpSys.position);},
        setPosition:function(element, offsetX, offsetY, direction, align, maxWidth, showCloseButton){
            if(element==null){element=document;}
            if(offsetX==null){offsetX=0;}
            if(offsetY==null){offsetY=0;}
            if(direction==null){direction="auto";}
            if(align==null){align="auto";}
            if(showCloseButton==null){showCloseButton=true;}
            contextHelp.contextHelpSys.curElement=$(element);
            contextHelp.contextHelpSys.curOffsetX=offsetX;
            contextHelp.contextHelpSys.curOffsetY=offsetY;
            contextHelp.contextHelpSys.curDirection=direction;
            contextHelp.contextHelpSys.curAlign=align;
            contextHelp.contextHelpSys.curMaxWidth=maxWidth;
            contextHelp.contextHelpSys.showCloseButton=showCloseButton;},
        position:function(){
            if(!contextHelp.contextHelpSys.isUp||!contextHelp.contextHelpSys.curElement.length){return;}
            var offset=contextHelp.contextHelpSys.curElement.offset();
            var left=parseInt(offset.left)+parseInt(contextHelp.contextHelpSys.curOffsetX);
            var top=parseInt(offset.top)+parseInt(contextHelp.contextHelpSys.curOffsetY);
            var direction=contextHelp.contextHelpSys.curDirection;
            var align=contextHelp.contextHelpSys.curAlign;
            if(contextHelp.contextHelpSys.curMaxWidth){
                $(contextHelp.contextHelpSys.CHTable).css("width",contextHelp.contextHelpSys.curMaxWidth);}
            else{
                $(contextHelp.contextHelpSys.CHTable).css("width","auto");}
            if(direction=="auto"){
                if(parseInt(top)-parseInt($(contextHelp.contextHelpSys.CHTable).height()<$(document).scrollTop())){
                    direction="down";}
                else{direction="up";}
            }
            if(direction=="up"){
                top = parseInt(top) - parseInt($(contextHelp.contextHelpSys.CHTable).height());
                $(contextHelp.contextHelpSys.arrowUp).css("display","none");
                $(contextHelp.contextHelpSys.arrowDown).css("display","block");}
            else{
                if(direction=="down"){
                    top = parseInt(top) + parseInt(contextHelp.contextHelpSys.curElement.height());
                    $(contextHelp.contextHelpSys.arrowUp).css("display","block");
                    $(contextHelp.contextHelpSys.arrowDown).css("display","none");}
                }
            if(align=="auto"){
                if(left+parseInt($(contextHelp.contextHelpSys.CHTable).width()>$(document).width())){
                    align="left";}
                else{align="right";}
            }
            if(align=="right"){
                left-=24;
                $(contextHelp.contextHelpSys.arrowUp).css("background-position","0 0");
                $(contextHelp.contextHelpSys.arrowDown).css("background-position","0 -6px");
            }
            else{
                if(align=="left"){
                    left-=parseInt($(contextHelp.contextHelpSys.CHTable).width());
                    left+=24;
                    $(contextHelp.contextHelpSys.arrowUp).css("background-position","100% 0");
                    $(contextHelp.contextHelpSys.arrowDown).css("background-position","100% -6px");}
            }
            if(contextHelp.contextHelpSys.showCloseButton) {
                $(contextHelp.contextHelpSys.closeButton).show();
            } else {
                $(contextHelp.contextHelpSys.closeButton).hide();
            }
            $(contextHelp.contextHelpSys.CHTable).css("left",left + "px");
            $(contextHelp.contextHelpSys.CHTable).css("top",top + "px");},
            
        updateContents:function(msg){
            contextHelp.contextHelpSys.isUp=true;
            $(contextHelp.contextHelpSys.CHContent).empty();
            $(contextHelp.contextHelpSys.CHContent).append(msg);
            contextHelp.contextHelpSys.position();
            $(contextHelp.contextHelpSys.CHTable).hide();
            $(contextHelp.contextHelpSys.CHTable).fadeIn();
            },
        hideMessage:function(){
            if(contextHelp.contextHelpSys.isUp){
                $(contextHelp.contextHelpSys.CHTable).fadeOut();
                contextHelp.contextHelpSys.isUp=false;}
        }
    }
};

function toggleKeepFiltersOption(mode) {
    // force visible if search term or prefilter has been modified
    var currentSearchTerm = $('#searchForm_input').val();
    var currentPrefilter = $("#searchForm_filter").val();
    if (origSearchTerm != currentSearchTerm || origPrefilter != currentPrefilter) {
        mode = true;
    }

    var obj = $("#searchForm").find(".keepFilters");
    if (!mode) {
        // already hidden?
        if (!obj.is(":visible")) {
            return;
        }
        // search field focused?
        if ($('#searchForm_input').is(":focus")) {
            return;
        }
    }
    if (mode) {
        obj.stop().slideDown(300);
    } else {
        obj.stop().slideUp(300, function() { $(this).hide(); });

    }

}