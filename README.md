# MAS_MultiDash

## Project Purpose

Give CMS Made Simple site administrators a shared, real-time workspace inside the admin panel so multiple editors can see who is online, follow live activity, and collaborate on files without leaving CMSMS.

## Description

MAS_MultiDash is a CMS Made Simple admin module that adds live presence, typing indicators, an activity feed, and a collaborative file manager to the backend. Admins see who is working in the module, get updates as others open or save files, and can edit shared documents with revision checks to reduce overwrite conflicts.

The module follows the MAS admin layout (Settings, Dashboard, File manager, Admin Settings, Donations, tabbed Help/About) and uses HTTP polling by default in the CMS admin for broad hosting compatibility, with optional SSE when your server supports bare module output.

## Requirements

- CMS Made Simple 2.2.10+
- PHP 7.4–8.6
- **MAS_Common** module (same site)

## Install

1. Copy this folder to `modules/MAS_MultiDash/` on your CMSMS site.
2. Install or upgrade via **Extensions → Module Manager**.
3. Ensure **MAS_Common** is installed first.
4. Grant **Use MAS_MultiDash** and **Manage MAS_MultiDash** to the appropriate admin groups.

## Features

- **Dashboard**: live admin presence, activity feed, collaborative scratch pad
- **File manager**: module sandbox under `data/files/` plus optional CMS upload subpaths
- **Realtime transport**: auto (polling in admin), polling only, or SSE only
- **MAS admin UI**: Settings, Admin Settings, Donations, tabbed Help/About

## Realtime transport

In CMS admin, **Auto** mode uses HTTP polling (reliable inside `moduleinterface.php`). Optional SSE is available via Settings when configured and supported by your host.

## Version

See [CHANGELOG.md](CHANGELOG.md) and `moduleinfo.ini`. Current release: **1.0.4**.

## License

MIT — see [LICENSE](LICENSE).
