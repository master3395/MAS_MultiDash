<div id="mas-md-app" class="mas-md-app" data-room="dashboard"
  data-api-url="{$mas_md_api_url|escape:'html'}"
  data-stream-url="{$mas_md_stream_url|escape:'html'}"
  data-poll-ms="{$mas_md_poll_ms|escape}"
  data-transport="{$mas_md_transport|escape:'html'}"
  data-can-manage="{$mas_md_can_manage|escape}">
  <div class="mas-md-grid">
    <section class="mas-md-panel">
      <h3>{$mod->Lang('live_admins')|escape}</h3>
      <ul id="mas-md-presence-list" class="mas-md-presence-list"></ul>
    </section>
    <section class="mas-md-panel mas-md-panel-wide">
      <h3>{$mod->Lang('activity_feed')|escape}</h3>
      <ul id="mas-md-activity-feed" class="mas-md-activity-feed"></ul>
    </section>
  </div>
  <section class="mas-md-panel">
    <h3>{$mod->Lang('scratch_pad')|escape}</h3>
    <p class="mas-md-hint">{$mod->Lang('scratch_hint')|escape}</p>
    <button type="button" id="mas-md-scratch-new" class="pagebutton">{$mod->Lang('scratch_new')|escape}</button>
    <div id="mas-md-typing-dashboard" class="mas-md-typing"></div>
    <textarea id="mas-md-scratch-editor" class="mas-md-editor" rows="12" placeholder="{$mod->Lang('editor_placeholder')|escape}"></textarea>
    <p><button type="button" id="mas-md-scratch-save" class="pagebutton">{$mod->Lang('save')|escape}</button></p>
    <div id="mas-md-conflict-dashboard" class="mas-md-conflict" style="display:none;"></div>
  </section>
</div>
