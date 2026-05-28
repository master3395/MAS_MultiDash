<div id="mas-md-file-app" class="mas-md-app" data-room="filemanager"
  data-api-url="{$mas_md_api_url|escape:'html'}"
  data-stream-url="{$mas_md_stream_url|escape:'html'}"
  data-poll-ms="{$mas_md_poll_ms|escape}"
  data-transport="{$mas_md_transport|escape:'html'}"
  data-can-manage="{$mas_md_can_manage|escape}">
  <div class="mas-md-file-layout">
    <aside class="mas-md-file-sidebar">
      <label>{$mod->Lang('file_root')|escape}</label>
      <select id="mas-md-file-root">
        <option value="sandbox">{$mod->Lang('root_sandbox')|escape}</option>
      </select>
      <p id="mas-md-file-error" class="mas-md-hint" style="color:#a00;display:none;"></p>
      <div id="mas-md-file-breadcrumb" class="mas-md-breadcrumb"></div>
      <ul id="mas-md-file-list" class="mas-md-file-list"></ul>
    </aside>
    <main class="mas-md-file-main">
      <div class="mas-md-editor-wrap">
        <h3 id="mas-md-editor-title">{$mod->Lang('editor_title')|escape}</h3>
        <div id="mas-md-typing-file" class="mas-md-typing"></div>
        <textarea id="mas-md-file-editor" class="mas-md-editor" rows="18" placeholder="{$mod->Lang('editor_placeholder')|escape}"></textarea>
        <p>
          <button type="button" id="mas-md-file-save" class="pagebutton">{$mod->Lang('save')|escape}</button>
          <span id="mas-md-revision-label" class="mas-md-meta"></span>
        </p>
        <div id="mas-md-conflict-file" class="mas-md-conflict" style="display:none;"></div>
      </div>
    </main>
  </div>
  <aside class="mas-md-presence-sidebar">
    <h4>{$mod->Lang('live_admins')|escape}</h4>
    <ul id="mas-md-presence-file" class="mas-md-presence-list"></ul>
  </aside>
</div>
