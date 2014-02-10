<!-- START of: Search/advanced.tpl -->

<div id="advancedSearchWrapper">
<form method="get" action="{$url}/Search/Results" id="advSearchForm" name="searchForm" class="search">
  <div class="advSearchHeader">
    <div class="content">
      <div class="grid_24">
        <h1>{translate text='Advanced Search'}</h1>{if in_array('searchAdvanced', $contextHelp)}<span id="contextHelp_searchAdvanced" class="showHelp">{translate text="Search Tips"}</span>{/if}
      </div>
    </div>
  </div>
  <div class="content">
    <div class="advSearchContent">
      {if $editErr}
        {assign var=error value="advSearchError_$editErr"}
        <div class="error">{translate text=$error}</div>
      {/if}
      {* An empty div. This is the target for the javascript that builds this screen *}
      <div class="grid_24">

        {* NDLBlankInclude *}
        {translate text='adv_search_instructions'}
        {* /NDLBlankInclude *}
    
        <h3 class="advTitle">{translate text="adv_search_terms"}</h3>
      </div>
      <div class="grid_24">
        <div class="advSearchSection first">
          <div id="groupJoin" class="searchGroups">
            <div class="searchGroupDetails">
              <label for="groupJoinOptions">{translate text="adv_bool_search_groups"}</label>
              <select id="groupJoinOptions" name="join">
                <option value="AND">{translate text="group_AND"}</option>
                <option value="OR"{if $searchDetails and $searchDetails.0.join == 'OR'} selected="selected"{/if}>{translate text="group_OR"}</option>
              </select>
            </div>
          </div>
          <div id="searchHolder" class="clearfix">
            {* fallback to a fixed set of search groups/fields if JavaScript is turned off *}
            <noscript>
              {if $searchDetails}{assign var=numGroups value=$searchDetails|@count}{/if}
              {if $numGroups < 3}{assign var=numGroups value=3}{/if}
              {section name=groups loop=$numGroups}
                {assign var=groupIndex value=$smarty.section.groups.index}
                <div class="group group{$groupIndex%2}" id="group{$groupIndex}">
                    <div class="searchRelated">
                      <div class="groupSearchDetails">
                        <div class="join">
                          <label for="search_bool{$groupIndex}">{translate text="Search Terms"}:</label>
                          <select id="search_bool{$groupIndex}" name="bool{$groupIndex}[]">
                            <option value="AND"{if $searchDetails and $searchDetails.$groupIndex.group.0.bool == 'AND'} selected="selected"{/if}>{translate text="search_AND"}</option>
                            <option value="OR"{if $searchDetails and $searchDetails.$groupIndex.group.0.bool == 'OR'} selected="selected"{/if}>{translate text="search_OR"}</option>
                            <option value="NOT"{if $searchDetails and $searchDetails.$groupIndex.group.0.bool == 'NOT'} selected="selected"{/if}>{translate text="search_NOT"}</option>
                          </select>
                        </div>
                      </div>
                      <div class="groupSearchHolder" id="group{$groupIndex}SearchHolder">
                      {if $searchDetails}
                        {assign var=numRows value=$searchDetails.$groupIndex.group|@count}
                      {/if}
                      {if $numRows < 3}{assign var=numRows value=3}{/if}
                      {section name=rows loop=$numRows}
                        {assign var=rowIndex value=$smarty.section.rows.index}
                        {if $searchDetails}{assign var=currRow value=$searchDetails.$groupIndex.group.$rowIndex}{/if}
                        <div class="advRow{if $rowIndex == $numGroups - 1} last{/if}">
                          <div class="label">
                            <label {if $rowIndex > 0}class="offscreen" {/if}for="search_lookfor{$groupIndex}_{$rowIndex}">{translate text="adv_search_label"}:</label>&nbsp;
                          </div>
                          <div class="terms">
                            <input id="search_lookfor{$groupIndex}_{$rowIndex}" type="text" value="{if $currRow}{$currRow.lookfor|escape}{/if}" size="50" name="lookfor{$groupIndex}[]"/>
                          </div>
                          <div class="field">
                            <label for="search_type{$groupIndex}_{$rowIndex}">{translate text="in"}</label>
                            <select id="search_type{$groupIndex}_{$rowIndex}" name="type{$groupIndex}[]">
                            {foreach from=$advSearchTypes item=searchDesc key=searchVal}
                              <option value="{$searchVal}"{if $currRow and $currRow.field == $searchVal} selected="selected"{/if}>{translate text=$searchDesc}</option>
                            {/foreach}
                            </select>
                          </div>
                          <span class="clearer"></span>
                        </div>
                      {/section}
                      </div>
                  </div>
                </div>
              {/section}
            </noscript>
          </div>
          <a id="addGroupLink" href="#" class="add offscreen" onclick="addGroup(); return false;">{translate text="add_search_group"}</a>
        </div>
      </div>
      <div class="grid_24"><h3 class="advTitle">{translate text="Other Search Criteria"}</h3></div>
      <div class="grid_24">
        <div class="advSearchSection">
          {if $facetList}
            <div id="facetWrapper" class="grid_12">
              {js filename="chosen/chosen.jquery.js"}
              {js filename="chosen_multiselects.js"}
              {foreach from=$facetList item="list" key="label"}
                <div class="facetsContainer span-3">
                  <label class="displayBlock" for="limit_{$label|replace:' ':''|escape}">{translate text=$label}:</label>
                  <select class="chzn-select span-3" data-placeholder="{translate text="No Preference"}" id="limit_{$label|replace:' ':''|escape}" name="orfilter[]" multiple="multiple" size="10">
                    {foreach from=$list item="value" key="display"}
                      <option value="{$value.filter|escape}"{if $value.selected} selected="selected"{/if}>{if $value.level > 0}&nbsp;&nbsp;&nbsp;{/if}{if $value.level > 1}&nbsp;&nbsp;&nbsp;{/if}{$value.translated|escape}</option>
                    {/foreach}
                  </select>
                </div>
              {/foreach}
              <div class="clear"></div>
              <div id="facetWrapperHelp">
                <div class="helpIcon"></div><span>{translate text="You can choose multiple languages and formats at once"}</span>
              </div>
            </div>
          {/if}        
          {if $illustratedLimit}
            <fieldset class="span-4">
              <legend>{translate text="Illustrated"}:</legend>
              {foreach from=$illustratedLimit item="current"}
                <input id="illustrated_{$current.value|escape}" type="radio" name="illustration" value="{$current.value|escape}"{if $current.selected} checked="checked"{/if}/>
                <label for="illustrated_{$current.value|escape}">{translate text=$current.text}</label><br/>
              {/foreach}
            </fieldset>
            <div class="clear"></div>
          {/if}
          {if $dateRangeLimit}
            {* Load the jslider UI widget *}
            {js filename="pubdate_slider.js"}
            {js filename="jshashtable-2.1_src.js"}
            {js filename="jquery.numberformatter-1.2.3.js"}
            {js filename="jquery.dependClass-0.1.js"}
            {js filename="draggable-0.1.js"}
            {js filename="jslider/jquery.slider.js"}   
            <div id="sliderWrapper" class="grid_10">
              <input type="hidden" name="sdaterange[]" value="search_sdaterange_mv"/>
              <label for="publishDatefrom" id="pubDateLegend">{translate text='Main Year'}</label>
              <input type="text" size="5" maxlength="11" class="yearbox" name="search_sdaterange_mvfrom" id="publishDatefrom" value="{if $spatialDateRangeLimit.0 && $spatialDateRangeLimit.0 != "-9999"}{$spatialDateRangeLimit.0|escape}{/if}" /> - 
              <input type="text" size="5" maxlength="11" class="yearbox" name="search_sdaterange_mvto" id="publishDateto" value="{if $spatialDateRangeLimit.1 && $spatialDateRangeLimit.1 != "9999"}{$spatialDateRangeLimit.1|escape}{/if}" />
              <br/>
              <div class="{*span-10*}" id="sliderContainer">
                <input id="publishDateSlider" class="dateSlider span-10" type="slider" name="sliderContainer" value="{if $spatialDateRangeLimit.0}{$spatialDateRangeLimit.0|escape}{else}0000{/if};{if $spatialDateRangeLimit.1}{$spatialDateRangeLimit.1|escape}{else}{$smarty.now|date_format:'%Y'}{/if}" />
              </div>
            </div>
          {/if}
        </div>
      </div>
      <div class="grid_24"><h3 id="mapSearch" class="advTitle"> {translate text='Geographic search'}</h3></div>
      <div class="grid_24">
        <div class="mapContainer advSearchSection" id="mapSearch">
          <div id="mapContainerTools">
            {js filename="jquery.geo.min.js"}
            {js filename="selection_map.js"}
            <label class="displayBlock" for="coordinates">{translate text='Coordinates:'}</label>
            {* help text, currently not included 
            <span class="small">Valitse kartalta tai syötä käsin muodossa: vasen yläkulma lat, vasen yläkulma lon, oikea alakulma lat, oikea alakulma lon</span>
            *}
            {php}
              // NB: The following seems to be working ok, but probably needs rethinking
              $filters = $this->get_template_vars('searchFilters');
              if (isset($filters['Other']) && is_array($filters['Other'])) {
                  foreach ($filters['Other'] as $key => $value) {
                      if (is_array($value) && strstr($value['field'], 'location_geo')) {
                          $value = substr(preg_replace('/^Intersects\(/', '', $value['value']), 0, -1);
                          $this->assign('coordinates', $value);
                      }
                  }
              }
            {/php}
            <input id="coordinates" name="coordinates" value="{if $coordinates}{$coordinates}{/if}"></input>
            <div id="selectionMapHelpWrapper" class="grid_12">
              <div class="selectionMapHelpIcon"></div>
              <span id="selectionMapHelp">
                <span id="selectionMapHelpPan">{translate text="adv_search_map_pan_help"}</span>
                <span id="selectionMapHelpPolygon" class="hide">{translate text="adv_search_map_polygon_help"}</span>
                <span id="selectionMapHelpRectangle" class="hide">{translate text="adv_search_map_rectangle_help"}</span>
              </span>
            </div>
            <div id="selectionMapTools">
              <input id="mapPan" type="radio" name="tool" value="pan" checked="checked"/>
              <label for="mapPan">{translate text='Move Map'}</label>
              <input id="mapPolygon" type="radio" name="tool" value="drawPolygon"/>
              <label for="mapPolygon">{translate text='Select Polygon'}</label>
              <input id="mapRectangle" type="radio" name="tool" value="dragBox"/>
              <label for="mapRectangle">{translate text='Select Rectangle'}</label>
            </div>
          </div>
          <div id="selectionMapContainer">
            <div id="zoomSlider">
              <div id="zoomControlPlus" class="ui-state-default ui-corner-all ui-icon ui-icon-plus"></div>
              <div id="zoomRange">
                <div id="zoomPath"></div>
              </div>
              <div id="zoomControlMinus" class="ui-state-default ui-corner-all ui-icon ui-icon-minus"></div>
            </div>

            <div id="selectionMap">              
            </div>
          </div>
        </div>
        {if $lastSort}<input type="hidden" name="sort" value="{$lastSort|escape}" />{/if}
      </div>
      <div class="advSearchFooter grid_24">
        <input type="submit" class="button buttonFinna searchButton right" name="submit" value="{translate text="Find"}"/>
      </div>
    </div>
  </div>
  <div class="clear"></div>
</form>
</div>
{literal}
<script type="text/html" id="new_search_tmpl">
<div class="advRow last">
    <div class="label">
        <label class="<%=(groupSearches[group] > 0 ? "hide" : "")%>" for="search_lookfor<%=group%>_<%=groupSearches[group]%>"><%=searchLabel%>:</label>&nbsp;
    </div>
    <div class="terms">
        <input type="text" id="search_lookfor<%=group%>_<%=groupSearches[group]%>" name="lookfor<%=group%>[]" size="50" value="<%=jsEntityEncode(term)%>" />
    </div>
    <div class="field">
        <label for="search_type<%=group%>_<%=groupSearches[group]%>"><%=searchFieldLabel%></label>
        <select id="search_type<%=group%>_<%=groupSearches[group]%>" name="type<%=group%>[]">
        <% for ( key in searchFields ) { %>
            <option value="<%=key%>"<%=key == field ? ' selected="selected"' : ""%>"><%=searchFields[key]%></option>
        <% } %>
        </select>
    </div>
<span class="clearer"></span>
</div>
</script>
<script type="text/html" id="new_group_tmpl">
    <div id="group<%=nextGroupNumber%>" class="group group<%=nextGroupNumber % 2%>">
        <div class="searchRelated">
            <div class="groupSearchDetails">
                <div class="join">
                    <label for="search_bool<%=nextGroupNumber%>"><%=searchMatch%>:</label>
                    <select id="search_bool<%=nextGroupNumber%>" name="bool<%=nextGroupNumber%>[]">
                        <% for ( key in searchJoins ) { %>
                            <option value="<%=key%>"<%=key == join ? ' selected="selected"' : ""%>"><%=searchJoins[key]%></option>
                        <% } %>
                    </select>
                </div>
            </div>
            <div id="group<%=nextGroupNumber%>SearchHolder" class="groupSearchHolder"></div>
        </div>
        <div class="addSearch"><a href="#" class="add" id="add_search_link_<%=nextGroupNumber%>" onclick="addSearchJS(this); return false;"><%=addSearchString%></a></div>
        <div class="deleteSearchGroup"><a href="#" class="delete" id="delete_link_<%=nextGroupNumber%>" onclick="deleteGroupJS(this); return false;"><%=deleteSearchGroupString%></a></div>
    </div>
</script>
{/literal}
{* Step 1: Define our search arrays so they are usuable in the javascript *}
<script type="text/javascript">
//<![CDATA[
    var searchFields = new Array();
    {foreach from=$advSearchTypes item=searchDesc key=searchVal}
    searchFields["{$searchVal}"] = "{translate text=$searchDesc}";
    {/foreach}
    var searchJoins = new Array();
    searchJoins["AND"]  = "{translate text="search_AND"}";
    searchJoins["OR"]   = "{translate text="search_OR"}";
    searchJoins["NOT"]  = "{translate text="search_NOT"}";
    var addSearchString = "{translate text="add_search"}";
    var searchLabel     = "{translate text="adv_search_label"}";
    var searchFieldLabel = "{translate text="in"}";
    var deleteSearchGroupString = "{translate text="del_search"}";
    var searchMatch     = "{translate text="search_match"}";
    var searchFormId    = 'advSearchForm';
//]]>
</script>
{* Step 2: Call the javascript to make use of the above *}
{js filename="advanced_search.js"}
{* Step 3: Build the page *}
<script type="text/javascript">
//<![CDATA[
  {if $searchDetails && isset($searchDetails.0.group)}
    {foreach from=$searchDetails item=searchGroup}
      {foreach from=$searchGroup.group item=search name=groupLoop}
        {if $smarty.foreach.groupLoop.iteration == 1}
    var new_group = addGroup('{$search.lookfor|escape:"javascript"}', '{$search.field|escape:"javascript"}', '{$search.bool}');
        {else}
    addSearch(new_group, '{$search.lookfor|escape:"javascript"}', '{$search.field|escape:"javascript"}');
        {/if}
      {/foreach}
    {/foreach}
  {else}
    var new_group = addGroup();
    addSearch(new_group);
    addSearch(new_group);
  {/if}
  // show the add group link
  $("#addGroupLink").removeClass("offscreen");
  {if $languagesSorted}
    {literal}
    $(function() {
      // Add a separator to the language facet list
      printSeparator();

      // Update separator position on select
      $('#limit_Language').change(function() {
          printSeparator();
      });

      function printSeparator() {
          var lastOrdered = {/literal}{$languagesSorted}{literal} - 1;
          $('.chzn-results li').removeClass('langSeparator');
          $('#limit_Language_chzn_o_'+lastOrdered).nextAll('.active-result')
            .first().addClass('langSeparator');
      }
    });
    {/literal}
  {/if}
//]]>
</script>

<!-- END of: Search/advanced.tpl -->
