UWEC Local Development
======================
Updated by Brian, 2026-08-12

# Project Details
- **NAME:** uwec
- **URL:** http://dev-uwec.pantheonsite.io/
- **LOCAL URL:** https://uwec.ddev.site/
- **BRANCH:** main
- **HOSTING:** [Pantheon Dashboard](https://dashboard.pantheon.io/sites/dfeadf45-ac5d-48f4-a701-c121589cff0e#dev/code)
- **CIRCLE CI:** [Logs](https://app.circleci.com/pipelines/github/electriccitizen/uwec)
- **DRUPAL:** 11 (core 11.3.x)
- **PHP:** 8.3
- **DB:** MariaDB 10.11
- **DRUSH:** 13.x

## Requirements and platform docs

- [EC: Local development requirements](https://docs.google.com/document/d/1_yeISu5bW5637TCeXByi82LUUfD1jeeSDHh5IeiPz4o/edit?usp=sharing)
- [EC: Developing on Pantheon](https://docs.google.com/document/d/1oTBHep57WENbf8PnM4LSn2Zx6x5EKA1rSYDEMvBEsUY/edit)

## ** **REBUILD PROJECT** **

(July 2025) This project is undergoing a major design transition and we are doing the work on a semi-permanent Pantheon Multidev. Eventually this will be merged back to the `main` branch once the work is complete.

### Details

Github Branch: [rebuild](https://github.com/electriccitizen/uwec/tree/rebuild)

Multidev Dashboard: [rebuild-uwec](https://dashboard.pantheon.io/sites/dfeadf45-ac5d-48f4-a701-c121589cff0e#rebuild)

Multidev URL: https://rebuild-uwec.pantheonsite.io

### Development

This is a new process that may need refining. For now, developers can push directly to the `rebuild` branch, or submit PRs against that branch.

**Track the `main` branch:**

It will be important to keep the `rebuild` branch updated with changes on the `main` branch. My advice for now is to do a daily merge of `main` into `rebuild`. This will avoid painful merge conflicts down the road.

```angular2html
git checkout uwec-rebuild
git fetch origin
git merge origin/main

```

Important: Don't merge or delete the open PR against this branch, and don't delete the multidev!

# Local Development Setup

Follow these steps to install a local development environment with DDev.

```
cd ~/Projects
git clone git@github.com:electriccitizen/uwec.git uwec
cd uwec
ddev start
ddev composer install
ddev auth ssh
```

## Download and import the database

```
ddev drush @uwec.live sql-dump > database.sql
ddev import-db --file=database.sql
ddev drush cr
ddev drush cim
ddev drush uli
```

Open the generated login URL and you should be set to go.

## Domain alias for non-standard local ports (only if needed)
Standard DDEV routes the site at `https://uwec.ddev.site` (port 443). **If** your local DDEV is forced onto a non-standard port — e.g., another project is already bound to 443 — check the port shown by `ddev describe` and update the `uwec_ddev_site_port` domain alias at `/admin/config/domain/alias/edit/uwec_ddev_site_port` to match. Save the form, then `ddev drush cr`.

If your DDEV is on the standard port, **no action is needed** — the default alias is harmless.

## Setting session cookies in development.services.yml (only for non-standard ports)
If your DDEV is on a non-standard port and you run into login / session issues, add to `web/sites/development.services.yml`:

```yaml
parameters:
  http.response.debug_cacheability_headers: true
  session.storage.options:
    cookie_domain: '.uwec.ddev.site:<your-port>'
```

Then `ddev drush cr` and hard-reload the browser.

# Refreshing your local environment
Whenever you start a new task, you'll want to refresh your local environment to pull in the latest changes from other developers.

```
cd ~/Projects/uwec
git checkout main
git pull
ddev start
ddev composer install
ddev auth ssh
```

DB Pull - Optional
```
ddev drush @uwec.live sql-dump > database.sql
ddev import-db --file=database.sql
```
End DB Pull

```
ddev drush cr
ddev drush cim
ddev drush uli
```

Open the generated login URL and you should be set to go.

See the "Domain alias" note above if your DDEV is on a non-standard port.

# Theming
The active theme for this project is **citizen_dart**:
`~/Projects/uwec/web/themes/custom/citizen_dart`

See the THEME-INSTALL.md file inside of the theme root for install instructions.
[THEME-INSTALL.md](/web/themes/custom/citizen_dart/THEME-INSTALL.md)

# Drush aliases

To interact with Pantheon via drush, you can use the Drush aliases that are auto-generated for each environment. For example:

**DEV, TEST, LIVE**

These aliases are always available via:

```
@uwec.dev
@uwec.test
@uwec.live
```
Note that not all projects will have all environments enabled.

**Multidevs (branch-named, NOT `pr-NNN`)**

Whenever you push a non-default branch, CircleCI creates a Pantheon multidev named after the **branch**, not the PR number. The `configure_env_vars` job in `.circleci/config.yml` lowercases the branch, maps `/` and `_` to `-`, strips any other character, forces a leading `x`, and truncates to 11 characters.

So branch `UWEC-383-monthly-updates` becomes multidev `xuwec-383-m`, served at `https://xuwec-383-m-uwec.pantheonsite.io/`, and you interact with it via:

```
@uwec.xuwec-383-m
```

Don't guess the name — list them with `terminus multidev:list uwec`. Note that `terminus env:info uwec.pr-484` will fail with "Could not find an environment" even when the PR built fine, because no `pr-NNN` environment is ever created here.

Two things to expect when smoke-testing a multidev over its `*.pantheonsite.io` hostname. The homepage and content nodes return **403**, because Domain Access does not recognize that host as a registered domain — this is normal, not a regression. `/user/login` returns 200, and `drush @uwec.<multidev> status` confirms the deployed core version, so use those instead. Real functional QA runs locally against the `uwec.ddev.site` domain alias.

Because these names are branch-derived rather than PR-derived, Pantheon's PR cleanup does not reap them. Stale `x...` multidevs accumulate against the site's multidev cap, so clean them up periodically — but never delete `rebuild`.
# Enabling Xdebug

Enable xdebug by running `ddev xdebug`. It will remain enabled for the entirety of your session and you can re-enable when needed. This should remain off in the DDEV config.

Auto Configuration for PHPStorm:

1. Turn on the listener in PHPStorm
2. Add a breakpoint at the top of web/index.php
3. Visit a page on the
4. This should prompt a dialog that sets up your server
5. The defaults should work

For other platforms and documentation see:

[DDEV DOCS](https://ddev.readthedocs.io/en/stable/users/debugging-profiling/step-debugging/)

# Backstop Testing

Refer to [EC-BACKSTOP.md](/tests/backstop/EC-BACKSTOP.md) for complete instructions for Visual Regression Testing using Backstop JS.
