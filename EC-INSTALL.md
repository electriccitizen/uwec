UWEC Local Development
=============================
Reviewed by David, 2023-07-18

# Project Details
- **NAME:** uwec
- **URL:** http://dev-uwec.pantheonsite.io/
- **LOCAL URL:** https://uwec.ddev.site:33001/
- **BRANCH:** main
- **HOSTING:** [Pantheon Dashboard](https://dashboard.pantheon.io/sites/dfeadf45-ac5d-48f4-a701-c121589cff0e#dev/code)
- **CIRCLE CI:** [Logs](https://app.circleci.com/pipelines/github/electriccitizen/uwec)

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

## !!-Mac users may need to modify the main domain alias
If you're on a Mac using DDEV and the site is being served through a non-standard port (i.e. 33001),
you may need to modify the main domain alias in the site's configuration. A configuration for
`uwec.ddev.site:33001` has been added to the local configuration split. But if your port number is
different, you will need to modify the number in the site configuration. Go to
`/admin/config/domain/alias/edit/uwec_ddev_site_port` and change the port number to match your local DDEV port.
Be sure save the configuration, then clear the cache.

If you're on a Mac using DDEV and the site is *not* served through a no-standard port,
go to `/admin/config/domain/alias/edit/uwec_ddev_site_port` and delete the 33001 alias.

## Setting session cookies in development.services.yml
You will need to add the following code in your development.services.yml to access the site due to domain configuration (starting on line 5).

parameters:
  http.response.debug_cacheability_headers: true
  session.storage.options:
    cookie_domain: '.uwec.ddev.site:33001'

You may need to clear cache, hard reset your browser, fin project start, shutdown and restart your computer and/or sacrifice a chicken to get it to kick in.

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

## !!-Mac users may need to modify the main domain alias
If you're on a Mac using DDEV and the site is being served through a non-standard port (i.e. 33001),
you may need to modify the main domain alias in the site's configuration. A configuration for
`uwec.ddev.site:33001` has been added to the local configuration split. But if your port number is
different, you will need to modify the number in the site configuration. Go to
`/admin/config/domain/alias/edit/uwec_ddev_site_port` and change the port number to match your local DDEV port.
Be sure save the configuration, then clear the cache.

# Theming
The active theme for this project is **citizen_dart**:
`~/Projects/uwec/web/themes/custom/citizen_dart`

See the THEME-INSTALL.md file inside of the theme root for install instructions.
[THEME-INSTALL.md](/web/themes/citizen_dart/THEME-INSTALL.md)

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

**PR-NNN** (Multidevs)

Whenever you create a Github pull request, a new Pantheon multidev is created in the format `PR-NNN`  (e.g. PR-123) You can interact with this environment via:

```
@uwec.pr-123
```
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
