# Citizen Migrate Module
This module is the container for all custom code needed to migrate content from the client's legacy system.

## Module Dependencies
Dependencies are included in the module's `.info` file. For reference, the modules are listed here:
* Migrate migrate (core)
* Migrate Drupal migrate_drupal (core)
* [Migrate Plus](https://www.drupal.org/project/migrate_plus) `migrate_plus` (contrib)
* [Migrate Tools](https://www.drupal.org/project/migrate_tools) `migrate_tools` (contrib)
* [Migrate Upgrade](https://www.drupal.org/project/migrate_upgrade) `migrate_upgrade` (contrib)
* [Migrate Source CSV](https://www.drupal.org/project/migrate_source_csv) `migrate_source_csv` (contrib)

The _Migrate Source CSV_ module is listed as a dependency, but is only necessary if any of your migrations use a CSV file as a source. It's been included as a dependency
because CSV sources are quite common even when the main data source is a database.

### Additional MSSQL modules (only for MSSQL data sources)
For Microsoft data sources like MSSQL, you will two additional modules:
* [Drupal Driver for SQL Server and SQL Azure](https://www.drupal.org/project/sqlsrv) `sqlsrv` (contrib)
* [Custom SQL Migrate Source Plugin](https://www.drupal.org/project/custom_sql_migrate_source_plugin) `custom_sql_migrate_source_plugin` (contrib)
___
&#9757;
See the _EC-MIGRATE-MSSQL_ file for detailed instructions on setting up MSSQL sources.
___

## Recommended Patches
1. D7 Field Collection Translations.
    ```json
    "drupal/paragraphs": {
      "#3084772: Adds the d7_field_collection_item_translations": "https://www.drupal.org/files/issues/2020-11-02/migrate-translated-fc-3084772-3.patch"
    },
    ```
2. Errors when migrating paragraphs from D7 field collections. 
   ```json
   "drupal/entity_reference_revisions": {
       "#3299758: Fixes array_flip() errors when migrating paragraphs.": "https://www.drupal.org/files/issues/2022-09-08/3299758-4.patch"
   },
   ``` 
3. Various patches to core to help with common migration tasks. 
    ```json
    "drupal/core": {
        "#2890844: Migration Lookup patch to allow multiple values": "https://www.drupal.org/files/issues/2022-02-10/2890844-88.drupal.patch",
        "#3198608: Adds the ability to add track_last_imported key to migration source plugin config.": "https://www.drupal.org/files/issues/2022-05-31/3198608-43.patch",
        "#2780839: Fix slow performance of migrating with the --idList flag.": "https://www.drupal.org/files/issues/2022-04-25/2780839-9.4.x-optimize-migrate-source-filter-53.patch",
    }
    ```
___
&#9757;
See the _EC-MIGRATE-MSSQL_ file for additional, required patches when migrating from a Microsoft database.
___

## Shared Configuration
Use the shared configuration file, `citizen_migrate.migrate_shared_configuration.yml`, to consolidate settings across multiple migration files.
This is especially helpful for large migrations because it allows you to modify common values once from a central place. Each client migration is different,
so use the provided file as a starting point. Add or modify the configuration settings as needed per client.

To use one or more blocks of shared configuration settings in your migration file, be sure to add the config keys to your migration's 'include' section.
For example, to include the 'cm_defaults' and 'user_defaults' config settings, add the following to your migration file:
```yaml
include:
  - cm_defaults
  - user_defaults 
```
### What to include in a shared configuration block
Shared configuration blocks can contain settings for the `source`, `process` and `destination` sections, either individually or together. The `node_defaults` block is an example of how to configure values for more than one section.
### Override a shared configuration setting
If you need to override any of the individual settings for a particular migration, you can do so by setting the key in the migration file. For example, if you wanted to
override the `log_data` value in the `cm_defaults` shared configuration block, your migration file would include this:
```yaml
include:
   - cm_defaults

source:
   - log_data: false
```
___
&#9757;
You can only override shared config values in the migration file, itself. Trying to override one shared config value with another shared config won't work.
___

### Some keys _can't_ be set in shared configuration
Some keys _must_ be set in the migration file for your migration to work. For example, the `plugin:` value under a migration's `source` section can **not** be set in the shared config file.
Without it, your migration will not be listed when you run the `drush migrate:status` command. 

## Migration Logging
This module provides post-migration logging for every migration. Included is an Event Subscriber that responds to migration events after a row has been saved and has access to all migrated values—source and destination. It logs the data in each row to a CSV file in the `public://migration-logs` directory.
These files can be helpful both during development and QA as they provide a broad view of the process. 

### Disable data logging
At times, you may find it useful to disable data logging during development. 
* To disable all post-migration logging,
set the `log_data` key in the shared configuration file to false. 
* Alternatively, you can disable logging for an individual migration by adding the `log_data: false` setting to the source section of the migration file.
___
&#9757;
Be sure to rebuild the cache `drush cr` any time you alter the migration yaml files.
___
## Content Processing

## CMTools Custom Services

## Custom Plugins

## Data Sources

### MySQL Sources

### CSV Sources

### JSON Sources

### MSSQL Source
Occasionally, a client will need to migrate from a Microsoft database server. Setting up a local environment to do development is a bit involved, but can be done in about an hour if things go well.
Refer to the _EC-MIGRATE-MSSQL_ file for instructions on how to do that.