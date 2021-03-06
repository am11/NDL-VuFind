<!-- START of: MetaLib/listentry.tpl -->
      <div class="listentry recordId" id="record{$record.ID.0|escape}">
       <div class="checkboxFilter">
        <div class="resultCheckbox">
        <input id="checkbox_{$record.ID.0|regex_replace:'/[^a-z0-9]/':''|escape}" type="checkbox" name="ids[]" value="{$record.ID.0|escape}" class="checkbox_ui"/>
        <label for="checkbox_{$record.ID.0|regex_replace:'/[^a-z0-9]/':''|escape}">{translate text="Select"}: {$recordTitle|escape}</label>
        <input type="hidden" name="idsAll[]" value="{$record.ID.0|escape}" />
        </div>
       </div>
        <div class="coverDiv">
          <div class="resultNoImage"><p>{translate text='No image'}</p></div>
          <div class="resultImage">
              <a class="title fancybox fancybox.image" data-dates="{assign var=pdxml value="PublicationDate_xml"}{if $record.$pdxml}({if $record.$pdxml.0.month}{$record.$pdxml.0.month|escape}/{/if}{if $record.$pdxml.0.day}{$record.$pdxml.0.day|escape}/{/if}{if $record.$pdxml.0.year}{$record.$pdxml.0.year|escape}){/if}{elseif $record.PublicationDate}{$record.PublicationDate.0|escape}{/if}" 
                data-title="{$record.Title.0|truncate:100:"..."|escape:"html"}" 
                data-url="{$url}/MetaLib/Record?id={$record.ID.0|escape:'url'}" 
                data-linktext="{translate text='To the record'}" 
                data-author="{foreach from=$record.Author item=author name="loop"}{$author|escape:'html'}{if !$smarty.foreach.loop.last}, {/if}{/foreach}"
                data-building="{foreach from=$record.Source item=source name="sourceloop"}{$source|escape:'html'}{if !$smarty.foreach.loop.last}, {/if}{/foreach}"
                href="{$path}/bookcover.php?size=large{if $record.ISBN.0}&amp;isn={$record.ISBN.0|@formatISBN}{/if}{if $record.ContentType.0}&amp;contenttype={$record.ContentType.0|escape:"url"}{/if}" 
                id="thumbnail_link_{$record.ID.0|escape:"url"}" 
                rel="gallery">
              <img src="{$path}/bookcover.php?size=small{if $record.ISBN.0}&amp;isn={$record.ISBN.0|@formatISBN}{/if}{if $record.ContentType.0}&amp;contenttype={$record.ContentType.0|escape:"url"}{/if}" class="summcover" alt="{translate text="Cover Image"}"/>
              </a>
          </div>
        </div>
        <div class="resultColumn2">
          
            <a href="{$url}/MetaLib/Record?id={$record.ID.0|escape:"url"}"
            class="title">{if !$record.Title.0}{translate text='Title not available'}{else}{$record.Title.0|highlight}{/if}</a>
           <br/>
            {if $record.Author}
            {translate text='by'}
            {foreach from=$record.Author item=author name="loop"}
              <a href="{$url}/MetaLib/Search?lookfor={$author|unhighlight|escape:"url"}">{$author|highlight}</a>{if !$smarty.foreach.loop.last},{/if} 
            {/foreach}
            <br/>
            {/if}

            {if $record.PublicationTitle}{translate text='Published in'} {$record.PublicationTitle.0|highlight}<br/>{/if}
            {assign var=pdxml value="PublicationDate_xml"}
            {if $record.$pdxml}({if $record.$pdxml.0.month}{$record.$pdxml.0.month|escape}/{/if}{if $record.$pdxml.0.day}{$record.$pdxml.0.day|escape}/{/if}{if $record.$pdxml.0.year}{$record.$pdxml.0.year|escape}){/if}{elseif $record.PublicationDate}{$record.PublicationDate.0|escape}{/if}
            
            {foreach from=$record.Source item=source name="sourceloop"}
              <br/>{$source} 
            {/foreach}

            {if $record.Snippet}
            <blockquote>
              <span class="quotestart">&#8220;</span>{$record.Snippet.0}<span class="quoteend">&#8221;</span>
            </blockquote>
            {/if}

          <span class="iconlabel format{$record.ContentType.0|getSummonFormatClass|escape}">{translate text=$record.ContentType.0}</span>
        </div>
        
      {if $listEditAllowed}
        <div class="last floatright editItem">
          <a href="{$url}/MyResearch/Edit?id={$record.ID.0|escape:"url"}{if !is_null($listSelected)}&amp;list_id={$listSelected|escape:"url"}{/if}" class="edit tool"></a>
          {* Use a different delete URL if we're removing from a specific list or the overall favorites: *}
          <a
          {if is_null($listSelected)}
            href="{$url}/MyResearch/Favorites?delete={$record.ID.0|escape:"url"}"
          {else}
            href="{$url}/MyResearch/MyList/{$listSelected|escape:"url"}?delete={$record.ID.0|escape:"url"}"
          {/if}
          class="delete tool" onclick="return confirm('{translate text='confirm_delete'}');"></a>
        </div>
      {/if}
        <div class="clear"></div>
        <span class="Z3988" title="{$record.openUrl|escape}"></span>
      </div>
<!-- END of: MetaLib/listentry.tpl -->
