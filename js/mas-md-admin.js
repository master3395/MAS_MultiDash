(function () {
  'use strict';

  function qs(sel, root) {
    return (root || document).querySelector(sel);
  }

  function qsa(sel, root) {
    return Array.prototype.slice.call((root || document).querySelectorAll(sel));
  }

  function apiUrl(base, params) {
    var u = base;
    var sep = u.indexOf('?') >= 0 ? '&' : '?';
    var parts = [];
    Object.keys(params).forEach(function (k) {
      parts.push(encodeURIComponent(k) + '=' + encodeURIComponent(params[k]));
    });
    return u + sep + parts.join('&');
  }

  function MasMdClient(el) {
    this.el = el;
    this.apiBase = el.getAttribute('data-api-url') || '';
    this.streamUrl = el.getAttribute('data-stream-url') || '';
    this.pollMs = parseInt(el.getAttribute('data-poll-ms'), 10) || 3000;
    this.transport = el.getAttribute('data-transport') || 'auto';
    this.room = el.getAttribute('data-room') || 'dashboard';
    this.lastEventId = 0;
    this.docKey = '';
    this.docRevision = 0;
    this.fileRoot = 'sandbox';
    this.filePath = '';
    this.typingTimer = null;
    this.es = null;
    this.pollTimer = null;
  }

  function normalizeUrl(u) {
    return String(u || '').replace(/&amp;/g, '&');
  }

  MasMdClient.prototype.normalizeUrls = function () {
    this.apiBase = normalizeUrl(this.apiBase);
    this.streamUrl = normalizeUrl(this.streamUrl);
  };

  MasMdClient.prototype.shouldUseSse = function () {
    if (this.transport === 'poll') {
      return false;
    }
    if (this.transport === 'sse') {
      return true;
    }
    // Auto: CMS admin moduleinterface rarely supports EventSource cleanly (HTML theme wrap).
    if (window.location.pathname.indexOf('moduleinterface.php') !== -1) {
      return false;
    }
    return !!window.EventSource && this.streamUrl !== '';
  };

  MasMdClient.prototype.call = function (action, data, method) {
    data = data || {};
    data.mas_action = action;
    data.room_id = this.room;
    var opts = { method: method || 'GET', credentials: 'same-origin' };
    if (method === 'POST') {
      opts.method = 'POST';
      opts.headers = { 'Content-Type': 'application/x-www-form-urlencoded' };
      opts.body = Object.keys(data).map(function (k) {
        return encodeURIComponent(k) + '=' + encodeURIComponent(data[k]);
      }).join('&');
      return fetch(this.apiBase, opts).then(parseJsonResponse);
    }
    return fetch(apiUrl(this.apiBase, data), opts).then(parseJsonResponse);
  };

  function parseJsonResponse(r) {
    return r.text().then(function (text) {
      try {
        return JSON.parse(text);
      } catch (e) {
        return { ok: false, error: 'invalid_json', status: r.status, preview: text.substring(0, 120) };
      }
    });
  }

  MasMdClient.prototype.showFileError = function (msg) {
    var el = qs('#mas-md-file-error');
    if (!el) return;
    if (!msg) {
      el.style.display = 'none';
      el.textContent = '';
      return;
    }
    el.style.display = 'block';
    el.textContent = msg;
  };

  MasMdClient.prototype.start = function () {
    var self = this;
    this.normalizeUrls();
    this.call('heartbeat', { typing: 0, payload: '{}' });
    if (this.shouldUseSse()) {
      this.startSse();
    } else {
      this.startPoll();
    }
    setInterval(function () {
      self.call('heartbeat', { typing: self._typing ? 1 : 0, payload: '{}' });
    }, 15000);
    window.addEventListener('beforeunload', function () {
      navigator.sendBeacon && self.call('leave', {});
    });
  };

  MasMdClient.prototype.startPoll = function () {
    var self = this;
    if (this.pollTimer) return;
    this.pollTimer = setInterval(function () {
      self.call('poll', { since_id: self.lastEventId }).then(function (j) {
        if (j && j.ok) self.applyPoll(j);
      }).catch(function () {});
    }, this.pollMs);
  };

  MasMdClient.prototype.startSse = function () {
    var self = this;
    if (!this.shouldUseSse()) {
      this.startPoll();
      return;
    }
    if (this.es) {
      return;
    }
    try {
      var url = apiUrl(this.streamUrl, { room_id: this.room, since_id: this.lastEventId });
      if (url.indexOf('showtemplate=false') === -1) {
        url += (url.indexOf('?') >= 0 ? '&' : '?') + 'showtemplate=false';
      }
      if (url.indexOf('suppressoutput=1') === -1) {
        url += '&suppressoutput=1';
      }
      this.es = new EventSource(url);
      this.es.addEventListener('presence', function (e) {
        try {
          var d = JSON.parse(e.data);
          self.renderPresence(d.all || d.room || []);
        } catch (err) {}
      });
      this.es.addEventListener('activity', function (e) {
        try {
          var ev = JSON.parse(e.data);
          self.pushActivity(ev);
        } catch (err) {}
      });
      this.es.onerror = function () {
        if (self.es) {
          self.es.close();
          self.es = null;
        }
        self.startPoll();
      };
    } catch (err) {
      this.startPoll();
    }
  };

  MasMdClient.prototype.applyPoll = function (j) {
    if (j.presence_all) this.renderPresence(j.presence_all);
    else if (j.presence) this.renderPresence(j.presence);
    if (j.events && j.events.length) {
      var self = this;
      j.events.forEach(function (ev) { self.pushActivity(ev); });
    }
  };

  MasMdClient.prototype.renderPresence = function (list) {
    var hosts = qsa('.mas-md-presence-list', this.el.parentElement || document);
    if (!hosts.length) hosts = qsa('.mas-md-presence-list');
    var html = '';
    (list || []).forEach(function (p) {
      var cls = p.status === 'idle' ? 'status-idle' : '';
      var typ = p.typing ? '<span class="typing-badge">typing</span>' : '';
      html += '<li class="' + cls + '"><strong>' + escapeHtml(p.display_name) + '</strong> ' +
        escapeHtml(p.room_id) + typ + '</li>';
    });
    if (!html) html = '<li><em>—</em></li>';
    hosts.forEach(function (ul) { ul.innerHTML = html; });
  };

  MasMdClient.prototype.pushActivity = function (ev) {
    if (!ev || !ev.id) return;
    this.lastEventId = Math.max(this.lastEventId, ev.id);
    var feed = qs('#mas-md-activity-feed');
    if (!feed) return;
    var li = document.createElement('li');
    var d = new Date((ev.ts || 0) * 1000);
    li.textContent = formatTime(d) + ' — ' + (ev.display_name || '') + ': ' + (ev.summary || '');
    feed.appendChild(li);
    while (feed.children.length > 80) feed.removeChild(feed.firstChild);
    feed.scrollTop = feed.scrollHeight;
  };

  MasMdClient.prototype.bindTyping = function (textarea) {
    var self = this;
    if (!textarea) return;
    textarea.addEventListener('input', function () {
      self._typing = true;
      clearTimeout(self.typingTimer);
      self.call('heartbeat', { typing: 1, payload: '{}' });
      self.typingTimer = setTimeout(function () {
        self._typing = false;
        self.call('heartbeat', { typing: 0, payload: '{}' });
      }, 2000);
    });
  };

  MasMdClient.prototype.saveDoc = function (content, conflictEl, revisionEl) {
    var self = this;
    if (!this.docKey) return;
    return this.call('doc_save', {
      doc_key: this.docKey,
      base_revision: this.docRevision,
      content: content,
      root: this.fileRoot,
      path: this.filePath
    }, 'POST').then(function (j) {
      if (j.conflict && j.document) {
        if (conflictEl) {
          conflictEl.style.display = 'block';
          conflictEl.textContent = 'Conflict: server has revision ' + j.document.revision + '. Reload or merge manually.';
        }
        return;
      }
      if (j.document) {
        self.docRevision = j.document.revision;
        if (revisionEl) revisionEl.textContent = 'rev ' + self.docRevision;
        if (conflictEl) conflictEl.style.display = 'none';
      }
    });
  };

  MasMdClient.prototype.initDashboard = function (skipTransport) {
    var self = this;
    var editor = qs('#mas-md-scratch-editor', this.el);
    var btnNew = qs('#mas-md-scratch-new', this.el);
    var btnSave = qs('#mas-md-scratch-save', this.el);
    var conflict = qs('#mas-md-conflict-dashboard', this.el);
    this.bindTyping(editor);
    if (btnNew) {
      btnNew.addEventListener('click', function () {
        self.call('scratch_create', {}, 'POST').then(function (j) {
          if (j.document) {
            self.docKey = j.doc_key || j.document.doc_key;
            self.docRevision = j.document.revision;
            editor.value = j.document.content || '';
          }
        });
      });
    }
    if (btnSave && editor) {
      btnSave.addEventListener('click', function () {
        self.saveDoc(editor.value, conflict, null);
      });
    }
    if (!skipTransport) {
      this.start();
    }
  };

  MasMdClient.prototype.initFileManager = function (skipTransport) {
    var self = this;
    var rootSel = qs('#mas-md-file-root', this.el);
    var list = qs('#mas-md-file-list', this.el);
    var crumb = qs('#mas-md-file-breadcrumb', this.el);
    var editor = qs('#mas-md-file-editor', this.el);
    var title = qs('#mas-md-editor-title', this.el);
    var btnSave = qs('#mas-md-file-save', this.el);
    var revLabel = qs('#mas-md-revision-label', this.el);
    var conflict = qs('#mas-md-conflict-file', this.el);
    this.fileRel = '';

    function renderList(entries) {
      if (!list) return;
      list.innerHTML = '';
      if (self.fileRel) {
        var up = document.createElement('li');
        up.textContent = '..';
        up.addEventListener('click', function () {
          var parts = self.fileRel.split('/').filter(Boolean);
          parts.pop();
          self.fileRel = parts.join('/');
          self.loadDir();
        });
        list.appendChild(up);
      }
      (entries || []).forEach(function (e) {
        var li = document.createElement('li');
        li.textContent = e.name + (e.dir ? '/' : '');
        if (e.dir) li.className = 'dir';
        li.addEventListener('click', function () {
          if (e.dir) {
            self.fileRel = self.fileRel ? self.fileRel + '/' + e.name : e.name;
            self.loadDir();
          } else {
            self.openFile(e.name);
          }
        });
        list.appendChild(li);
      });
    }

    this.loadDir = function () {
      if (!rootSel) return;
      self.fileRoot = rootSel.value || 'sandbox';
      if (crumb) crumb.textContent = self.fileRoot + '/' + (self.fileRel || '');
      self.room = 'file:' + self.fileRoot + ':' + (self.fileRel || '');
      self.call('file_list', { root: self.fileRoot, path: self.fileRel || '' }).then(function (j) {
        if (j && j.ok && j.entries) {
          self.showFileError('');
          renderList(j.entries);
        } else if (j && j.error) {
          self.showFileError('List failed: ' + j.error);
          renderList([]);
        }
      });
    };

    this.openFile = function (name) {
      var rel = self.fileRel ? self.fileRel + '/' + name : name;
      self.filePath = rel;
      self.room = 'file:' + self.fileRoot + ':' + rel;
      if (title) title.textContent = rel;
      self.call('file_read', { root: self.fileRoot, path: rel }).then(function (j) {
        if (j && j.document) {
          self.docKey = j.doc_key;
          self.docRevision = j.document.revision;
          if (editor) editor.value = j.document.content || '';
          if (revLabel) revLabel.textContent = 'rev ' + self.docRevision;
        }
      });
    };

    this.loadDir();
    this.call('file_roots', {}).then(function (j) {
      if (!rootSel) return;
      if (!j || !j.ok || !j.roots || !j.roots.length) {
        self.showFileError(j && j.error ? ('API: ' + j.error) : 'Could not load folder roots. Check module is enabled and permissions.');
        return;
      }
      self.showFileError('');
      var current = rootSel.value || 'sandbox';
      rootSel.innerHTML = '';
      j.roots.forEach(function (r) {
        var o = document.createElement('option');
        o.value = r.key;
        o.textContent = r.label;
        rootSel.appendChild(o);
      });
      rootSel.value = current;
      if (!rootSel.value && j.roots[0]) {
        rootSel.value = j.roots[0].key;
      }
      self.loadDir();
    }).catch(function () {
      self.showFileError('Network error loading folder roots.');
    });

    if (rootSel) rootSel.addEventListener('change', function () { self.fileRel = ''; self.loadDir(); });
    this.bindTyping(editor);
    if (btnSave && editor) {
      btnSave.addEventListener('click', function () {
        self.saveDoc(editor.value, conflict, revLabel);
      });
    }
    if (!skipTransport) {
      this.start();
    }
  };

  function escapeHtml(s) {
    return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
  }

  function formatTime(d) {
    var p = function (n) { return n < 10 ? '0' + n : '' + n; };
    return p(d.getHours()) + ':' + p(d.getMinutes()) + ':' + p(d.getSeconds());
  }

  function boot() {
    var dash = qs('#mas-md-app');
    var fm = qs('#mas-md-file-app');
    var hub = null;

    if (dash) {
      hub = new MasMdClient(dash);
      hub.initDashboard(true);
    }
    if (fm) {
      var fc = new MasMdClient(fm);
      if (hub) {
        fc.apiBase = hub.apiBase;
        fc.streamUrl = hub.streamUrl;
        fc.pollMs = hub.pollMs;
        fc.transport = hub.transport;
      } else {
        hub = fc;
      }
      fc.initFileManager(true);
    }
    if (hub) {
      hub.normalizeUrls();
      hub.start();
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
