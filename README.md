# MAS_MultiDash

CMS Made Simple admin module for real-time collaborative dashboard features: live presence, typing indicators, activity feed, and a collaborative file manager.

## Requirements

- CMS Made Simple 2.2+
- PHP 7.4–8.6
- **MAS_Common** module (same site)

## Install

1. Copy this folder to `modules/MAS_MultiDash/` on your CMSMS site.
2. Install or upgrade via **Extensions → Module Manager**.
3. Ensure **MAS_Common** is installed first.

## Features

- **Dashboard**: live admin presence, activity feed, transport auto/polling/SSE
- **File manager**: sandbox under `data/files/` plus optional CMS upload roots
- **Admin Settings**: MAS standard layout (Settings, Admin Settings, Donations)
- **Help / About**: tabbed docs via MAS_Common

## Realtime transport

In CMS admin, **Auto** mode uses HTTP polling (reliable inside `moduleinterface.php`). Optional SSE is available when configured and outside the wrapped admin context.

## Version

See `CHANGELOG.md` and `moduleinfo.ini`.

## License

MIT — see [LICENSE](LICENSE).
