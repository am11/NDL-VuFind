<!-- START of: Search/tabnavi.tpl -->

<div class="ui-tabs ui-widget tabNavi" role="navigation" aria-label="{translate text='Search Type Navigation'}">
  <div class="content">
    <div class="grid_24">
      <ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix">

      {* Check from URI if in Advanced search *}
      {if strpos($smarty.server.REQUEST_URI, "Advanced") !== false}
        {* Advanced Search with possible PCI/MetaLib alternatives *}

{* FIXME: Links probably wrong wrong wrong... *}
        {* Tab: Solr Advanced*}
        <li class="active ui-state-default ui-corner-top{if $module=='Search'} ui-tabs-selected ui-state-active{/if}">
          <a id="booksTabAdvanced" href="{$path}/Search/Advanced">{translate text="Local Records"}</a>
        </li>

        {* Tab: PCI Advanced *}
        {if $pciEnabled}
        <li class="active ui-state-default ui-corner-top{if $module=='PCI' && $pageTemplate!='list-dual.tpl'} ui-tabs-selected ui-state-active{/if}">
          <a id="articlesTabAdvanced" href="{$path}/PCI/Advanced">Primo Central</a>
        {/if}

        {* Tab: MetaLib Advanced *}
        {if $metalibEnabled && $lookfor ==''}
        <li class="active ui-state-default ui-corner-top{if $module=='MetaLib'} ui-tabs-selected ui-state-active{/if}"><a id="metalibTabAdvanced" href="{$path}/MetaLib/Advanced">{translate text="MetaLib Searches"}</a></li>
        {/if}
        {if $metalibEnabled && $lookfor !=''}
        <li class="active ui-state-default ui-corner-top{if $module=='MetaLib'} ui-tabs-selected ui-state-active{/if}"><a id="metalibTabAdvanced" href="{$path}/MetaLib/Advanced?lookfor={$lookfor}&set=uusimaa&submit=Hae&retainFilters=0">{translate text="MetaLib Searches"}</a></li>
        {/if}
{* /FIXME *}

      {else}
        {* Basic Search with possible PCI/MetaLib alternatives *}

        {* Tab: dualResults *}
        {if $dualResultsEnabled && $searchType != 'advanced'} {* Check not in advanced results *}
        <li class="active ui-state-default ui-corner-top{if $module=='Search' && $pageTemplate=='list-dual.tpl'} ui-tabs-selected ui-state-active{/if}">
          {if $module=="MetaLib"}
          <a id="allResultsTab" href="{$searchWithoutFilters|escape|replace:"/MetaLib/Search":"/Search/DualResults"|replace:"prefilter=":"prefiltered="}&dualResults=1">
          {elseif $module=="PCI"}
          <a id="allResultsTab" href="{$searchWithFilters|escape|replace:"/PCI/Search":"/Search/DualResults"|replace:"prefilter=":"prefiltered="|replace:"view=grid":"view=list"|regex_replace:"/(&amp;)?limit=[0-9]*/":""}&dualResults=1">
          {else}
          <a id="allResultsTab" href="{$searchWithFilters|escape|replace:"/Search/Results":"/Search/DualResults"|replace:"prefilter=":"prefiltered="|replace:"view=grid":"view=list"|regex_replace:"/(&amp;)?limit=[0-9]*/":""}&dualResults=1">
          {/if}
          {translate text="Local and Primo"}</a>
        </li>
        {/if}
        
        {* Tab: Solr *}
        <li class="active ui-state-default ui-corner-top{if $module=='Search' && $pageTemplate!='list-dual.tpl'} ui-tabs-selected ui-state-active{/if}">
          {if $module=="MetaLib"}
          <a id="booksTab" href="{$searchWithoutFilters|escape|replace:"/MetaLib/Search":"/Search/Results"}{if $dualResultsEnabled}&dualResults=0{/if}">
          {else}
          <a id="booksTab" href="{$more|replace:"&dualResults=0":""}{$searchWithFilters|escape|replace:"/PCI/Search":"/Search/Results"|replace:"prefilter=":"prefiltered="}{if $dualResultsEnabled}&dualResults=0{/if}">
          {/if}
          {translate text="Local Records"}</a>
        </li>

        {* Tab: PCI *}
        {if $pciEnabled && $searchType != 'advanced'}
        <li class="active ui-state-default ui-corner-top{if $module=='PCI' && $pageTemplate!='list-dual.tpl'} ui-tabs-selected ui-state-active{/if}"> 
          {if $module=="MetaLib"}
          <a id="articlesTab" href="{$searchWithoutFilters|escape|replace:"/MetaLib/Search":"/PCI/Search"|replace:"prefilter=":"prefiltered="}{if $dualResultsEnabled}&dualResults=0{/if}">
          {else}
          <a id="articlesTab" href="{$pci_more|replace:"&dualResults=0":""}{$searchWithFilters|escape|replace:"/Search/Results":"/PCI/Search"|replace:"/Search/DualResults":"/PCI/Search"|replace:"prefilter=":"prefiltered="}{if $dualResultsEnabled}&dualResults=0{/if}">
          {/if}
          Primo Central</a>
        </li>
        {/if}

        {* Tab: MetaLib *}
        {if $metalibEnabled}
        <li class="active ui-state-default ui-corner-top{if $module=='MetaLib'} ui-tabs-selected ui-state-active{/if}">
          {if $lookfor ==''}
          <a id="metalibTab" href="{$path}/MetaLib/Home">
          {else}
          <a id="metalibTab" href="{$path}/MetaLib/Search?lookfor={$lookfor}&set=uusimaa&submit=Hae&retainFilters=0">
          {/if}
          {translate text="MetaLib Searches"}</a></li>
        {/if}

      {/if}
      
      </ul>
    </div>
  </div>
</div>

<!-- END of: Search/tabnavi.tpl -->
