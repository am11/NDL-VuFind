<!-- START of: MyResearch/catalog-login.tpl -->

{if $offlineMode == "ils-offline"}
  <div class="sysInfo">
    <h2>{translate text="ils_offline_title"}</h2>
    <p><strong>{translate text="ils_offline_status"}</strong></p>
    <p>{translate text="ils_offline_login_message"}</p>
    <p><a href="mailto:{$supportEmail}">{$supportEmail}</a></p>
  </div>
{else}
  <h3>{translate text='Library Catalog Profile'}</h3>
  {if $loginError}
    <p class="error">{translate text=$loginError}</p>
  {/if}
  <p>{translate text='cat_establish_account'}</p>
  <form method="post" action="">
    {if $loginTargets}
      <select id="login_target" name="login_target">
      {foreach from=$loginTargets item=target}
        <option value="{$target}"{if $target == $defaultLoginTarget} selected="selected"{/if}>{translate text=$target prefix='source_'}</option>
      {/foreach}
      </select>
      <br class="clear"/>
    {/if}
  
    <label class="displayBlock" for="profile_cat_username">{translate text='Library Catalog Username'}:</label>
    <input id="profile_cat_username" type="text" name="cat_username" value="" size="25"/>
    <label class="displayBlock" for="profile_cat_password">{translate text='Library Catalog Password'}:</label>
    <input id="profile_cat_password" type="password" name="cat_password" value="" size="25"/>
    <br/>
    <input type="submit" name="submit" value="{translate text="Save"}"/>

    {if $followup}
      <input type="hidden" name="followup" value="{$followup|escape}"/>
      {if $followupModule}<input type="hidden" name="followupModule" value="{$followupModule|escape}"/>{/if}
      {if $followupAction}<input type="hidden" name="followupAction" value="{$followupAction|escape}"/>{/if}
      {if $id}<input type="hidden" name="recordId" value="{$id|escape:"html"}"/>{/if}
    {/if}

  </form>
{/if}

<!-- END of: MyResearch/catalog-login.tpl -->
