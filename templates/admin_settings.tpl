<div style="border:1px solid #CCC; padding:10px; margin:10px 0;">
{if $mas_banner_url|default:'' != ''}
<img src="{$mas_banner_url|escape:'html'}" alt="" width="600" height="120" style="max-width:100%;height:auto;border-radius:6px;margin-bottom:12px;">
{/if}
<h3>{$module->Lang('adminsettings')|escape:'html'}</h3>
{$formstart}
<p><label for="adminsection">{$adminsection_label|escape}</label><br/>{$adminsection_dropdown}<br/><em>{$adminsection_help|escape}</em></p>
<p><label for="showdonationstab">{$showdonationstab_label|escape}</label><br/>{$showdonationstab_checkbox}</p>
<p>{$submit}</p>
{$formend}
</div>
