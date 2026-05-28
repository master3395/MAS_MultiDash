<div class="pageoverflow mas-md-settings">
  {if $mas_banner_url|default:'' != ''}
  <img src="{$mas_banner_url|escape:'html'}" alt="" width="600" height="120" style="max-width:100%;height:auto;border-radius:6px;margin-bottom:12px;">
  {/if}
  <h2>{$mod->Lang('friendlyname')|escape:'html'} <span style="font-weight:normal;font-size:12px;">v{$mas_module_version|escape}</span></h2>
  <p>{$mod->Lang('settings_intro')|escape}</p>
  {$start_form}
  <p><label>{$mod->Lang('pref_enabled')|escape}</label> {$input_enabled}</p>
  <p><label>{$mod->Lang('pref_transport')|escape}</label> {$input_transport}</p>
  <p><label>{$mod->Lang('pref_poll_ms')|escape}</label> {$input_poll_ms}</p>
  <p><label>{$mod->Lang('pref_idle')|escape}</label> {$input_idle}</p>
  <p><label>{$mod->Lang('pref_max_bytes')|escape}</label> {$input_max_bytes}</p>
  <p><label>{$mod->Lang('pref_cms_roots')|escape}</label><br/>{$input_cms_roots}<br/><em>{$mod->Lang('pref_cms_roots_help')|escape}</em></p>
  <p><label>{$mod->Lang('pref_allow_php')|escape}</label> {$input_allow_php}</p>
  <p>{$submit}</p>
  {$end_form}
</div>
