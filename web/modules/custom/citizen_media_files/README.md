# Citizen Media Files

Portable Electric Citizen package for the media file lifecycle: editors can
see where media is used, replace a file while keeping its URL, and delete a
file so it actually disappears — including from the hosting platform's edge
cache, which is what historically made replaced and deleted files "come back"
(files are edge-cached for up to a year by default on Acquia and Pantheon).

## What it bundles

- **entity_usage** — tracks where each media entity is used. Media with 0
  usage on the current published version is safe to delete.
- **media_entity_file_replace** — "Replace file" on the media edit form. The
  overwrite option keeps the same filename and URL (no `-0`/`-1` suffixes).
- **media_file_delete** — deletes the underlying file when a media entity is
  deleted.
- **Edge purging (this module)** — when a file is replaced or deleted, its
  URL (and image style derivative URLs) is purged from the platform edge
  cache, host-detected at runtime:
  - **Acquia** (`AH_SITE_ENVIRONMENT` present): queues `url` invalidations
    through the purge module. Requires `purge` + `acquia_purge` with the
    site's purgers configured (Platform CDN and/or Varnish); both EC-standard
    purgers support `url` invalidations. Processed by the site's purge
    processors (late runtime / cron).
  - **Pantheon** (`PANTHEON_ENVIRONMENT` present): calls
    `pantheon_clear_edge_paths()`. If the platform ever removes that
    function, the module logs a loud warning instead of failing silently.
  - **Anything else** (local/DDEV, unknown host): logs what would have been
    purged.

  Force a backend for testing with `$settings['citizen_media_files_host'] =
  'acquia'|'pantheon'|'none';` in settings.php.

## Install-time defaults

`hook_install()` adapts to the site instead of shipping static config:

- Applies the EC baseline `entity_usage.settings` (UWEC reference config) —
  only when the site has not already configured entity_usage.
- Enables the "Replace file" widget on the form display of every file-backed
  media type.
- Grants `access entity usage statistics` and Media File Delete's permissions
  to a `site_manager`-style role when one exists.
- Adds a "Usage count" column (with View Usage link) to the media admin view,
  per the UWEC reference view.

Everything it could not apply safely is logged to the `citizen_media_files`
channel — check the log after install and finish those pieces manually.

## What purging cannot fix

Browser caches that already hold a file (served with a long max-age before
this module was installed), Google's index (use a Search Console removal
request for sensitive documents), and third-party archives. Consider
shortening the file max-age in `.htaccess` alongside this module so browsers
re-check files at a sane interval.

## Editor workflow (train clients on this)

1. Check usage before deleting: media list "Usage count" column or the Usage
   tab. 0 usage on the published version = safe.
2. Replace: media edit form, "Replace file", keep the overwrite box checked,
   file types must match.
3. Delete: deleting the media entity offers to delete the file too.
4. Sensitive removals additionally need a Google Search Console removal
   request.
