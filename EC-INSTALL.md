UWEC Local Development
=============================
Reviewed by David, 2023-07-19

# Project Details
- **NAME:** uwec 
- **URL:** http://dev-uwec.pantheonsite.io/
- **LOCAL URL:** http://uwec.docksal.site
- **BRANCH:** main
- **HOSTING:** [Pantheon Dashboard](https://dashboard.pantheon.io/sites/dfeadf45-ac5d-48f4-a701-c121589cff0e#dev/code)
- **CIRCLE CI:** [Logs](https://app.circleci.com/pipelines/github/electriccitizen/uwec)

## Requirements and platform docs

- [EC: Local development requirements](https://docs.google.com/document/d/1_yeISu5bW5637TCeXByi82LUUfD1jeeSDHh5IeiPz4o/edit?usp=sharing)
- [EC: Developing on Pantheon](https://docs.google.com/document/d/1oTBHep57WENbf8PnM4LSn2Zx6x5EKA1rSYDEMvBEsUY/edit)

# Local Development Setup

Follow these steps to install a local development environment.

`cd ~/Projects`

`git clone git@github.com:electriccitizen/uwec.git`

`cd uwec`

`composer install`

`fin start`

`fin hosts add`

## Download and import the database

`fin drush @uwec.dev sql-dump > database.sql`

`fin db import database.sql`

`fin drush cr`

## Import local configuration

`fin drush cim`

## Log into website as admin

`fin drush uli`

Open the generated login URL and you should be set to go.

# Refreshing your local environment
Whenever you start a new task, you'll want to refresh your local environment to pull in the latest changes from other developers.

`cd ~/Projects/uwec`

`git checkout main`

`git pull`

`fin restart`

`composer install`

DB Pull - Optional
`fin drush @uwec.dev sql-dump > database.sql`
`fin db import database.sql`
End DB Pull

`fin drush cr`

`fin drush cim`

`fin drush uli`

Open the generated login URL and you should be set to go.

# Theming
The active theme for this project is **citizen_dart**:
`~/Projects/uwec/web/themes/citizen_dart`

See the THEME-INSTALL.md file inside of the theme root for install instructions.
[THEME-INSTALL.md](/web/themes/citizen_dart/THEME-INSTALL.md)

# Drush aliases

To interact with Pantheon via drush, you can use the Drush aliases that are auto-generated for each environment. For example:

**DEV, TEST**

* There is no LIVE environment for the UWEC site.

These aliases are always available via:

```
@uwec.dev
@uwec.test
```
Note that not all projects will have all environments enabled.

**PR-NNN** (Multidevs) 

Whenever you create a Github pull request, a new Pantheon multidev is created in the format `PR-NNN`  (e.g. PR-123) You can interact with this environment via:

```
@uwec.pr-123
```

# Project Legend

## Docksal Images
- DB - docksal/mariadb:10.4
- CLI - docksal/cli:stable-php7.4
- SOLR - docksal/solr:1.0-solr3

See `~/Projects/uwec/.docksal/docksal.yml`

## settings.docksal.php
- database connection
- hash_salt
- base_url
- development services
- error level
- rebuild_access
- permissions_hardening
- trusted_host_pattern
- file paths
- migration discovery

See `/Projects/uwec/web/sites/default/settings.docksal.php`

# Enabling Xdebug

Copy the `.docksal/docksal-local.yml.default` file to the .docksal folder as `docksal-local.yml` and ensure that `XDEBUG_ENABLED=1`

Open `.docksal/etc/php/php.ini` and uncomment the three lines of code directly under [xdebug]:

```
[xdebug]
xdebug.mode=debug
xdebug.discover_client_host=1
xdebug.client_host=192.168.64.100
```

Run `fin restart` to restart the Docksal project.

# Backstop Testing

Refer to [EC-BACKSTOP.md](/tests/backstop/EC-BACKSTOP.md) for complete instructions for Visual Regression Testing using Backstop JS. 

