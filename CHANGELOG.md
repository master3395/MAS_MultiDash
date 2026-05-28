# MAS_MultiDash Changelog

## 1.0.4 - 28/05/2026

### Fixed
- EventSource MIME errors in CMS admin: auto transport now uses HTTP polling on `moduleinterface.php` (SSE still available via Settings → SSE only).
- Stream/API URLs: decode `&amp;`, append `suppressoutput=1`, single shared transport (no duplicate EventSource from Dashboard + File manager tabs).
- Stream action sends SSE headers before bootstrap; auth/disabled errors use SSE error events.

## 1.0.3 - 28/05/2026

### Fixed
- File manager JavaScript: `loadDir is not a function` (methods defined before first call).
- SSE/API: append unprefixed `showtemplate=false` for admin `moduleinterface.php`; override `SuppressAdminOutput` for `stream` and `ajax_api`.
- SSE: clear output buffers before streaming; poll fallback only starts when EventSource fails (not in parallel with auto mode).

## 1.0.2 - 28/05/2026

### Fixed
- Module Manager Help/About: language strings for tabs and labels (`about_tab_*`, `about_label_*`, `help_*`). Caused by `require_once` on shared defaults skipping merge for this module's `$lang` array.

## 1.0.1 - 28/05/2026

### Fixed
- Donations tab: added missing language strings (`sponsors`, `donationstext`, `hidedonationssubmit`).
- File manager: API/stream URLs now use `showtemplate=false` so JSON and SSE work in admin.
- File manager: default sandbox root in dropdown; clearer errors when API fails.
- Donations hide form now posts to `defaultadmin` so the tab can be hidden correctly.

## 1.0.0 - 28/05/2026

### Added
- Initial release: real-time admin presence, typing indicators, and live activity feed.
- Collaborative file manager with module sandbox and optional CMS upload subpaths.
- Revision-based document save with conflict detection.
- SSE transport with HTTP polling fallback for varied hosting environments.
- Full MAS admin layout: Settings, Admin Settings, Donations tab, tabbed Help/About.
