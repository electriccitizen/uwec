[comment]: <> (Author: David Kirkwood)

# Citizen Migrate - MSSQL
This document outlines the process of connecting to a Microsoft SQL server containing the legacy data.
## References
The following articles were helpful in learning and troubleshooting:
- _[How to install SQL Server on a Mac](https://database.guide/how-to-install-sql-server-on-a-mac/)_
- _[How to install Azure Data Studio on your Mac](https://database.guide/how-to-install-azure-data-studio-on-a-mac/)_
- _[How to Create a Database with Azure Data Studio](https://database.guide/how-to-install-azure-data-studio-on-a-mac/)_
- _[How to Restore a Database with Azure Data Studio on a Mac](https://database.guide/how-to-restore-a-sql-server-database-on-a-mac-using-azure-data-studio/)_
- _[Docker Express: Running a Local SQL Server on Your M1 Mac](https://medium.com/geekculture/docker-express-running-a-local-sql-server-on-your-m1-mac-8bbc22c49dc9)_
- _[Linux and macOS Installation Tutorial for the Microsoft Drivers for PHP for SQL Server](https://docs.microsoft.com/en-us/sql/connect/php/installation-tutorial-linux-mac?view=sql-server-ver16)_
- _[Install the Microsoft ODBC driver for SQL Server (Linux)](https://docs.microsoft.com/en-us/sql/connect/odbc/linux-mac/installing-the-microsoft-odbc-driver-for-sql-server?view=sql-server-ver15)_
- _[Docker php installation extension steps](https://programmer.group/docker-php-installation-extension-steps.html)_
- _[Using Connection String Keywords with SQL Server Native Client](https://docs.microsoft.com/en-us/sql/relational-databases/native-client/applications/using-connection-string-keywords-with-sql-server-native-client?view=sql-server-ver16)_
- _[unixODBC (7) - Linux Man Pages](https://www.systutorials.com/docs/linux/man/7-unixODBC/)_
- _[Synchronize data from MS-SQL into Drupal](https://www.lakedrops.com/en/blog/synchronize-data-ms-sql-drupal)_
- _[Migration Custom Source Plugin](https://redfinsolutions.com/blog/migration-custom-source-plugin)_
- _[Connecting to a Transact SQL Database to Drupal](https://redfinsolutions.com/blog/connecting-transact-sql-database-drupal)_
- _[Easysoft ODBC-SQL Server Driver](https://www.easysoft.com/support/kb/kb01071.html)_
- _[Accessing Microsoft SQL Server (mssql) from PHP under Apache on Unix or Linux](https://www.easysoft.com/developer/languages/php/sql_server_unix_tutorial.html#pdo)_
- _[SQL SErver Driver for PHP Connection Option Encrypt](https://blogs.iis.net/bswan/sql-server-driver-for-php-connection-options-encrypt)_
- _[Docker Command Line Documentation](https://docs.docker.com/engine/reference/commandline/docker/)_
- _[How to Clean Your Docker Data](https://dockerwebdev.com/tutorials/clean-up-docker/)_

## Local Development Environment

The specs of the local development machine used for this documentation are below. Some of these instructions may need to
be adjusted for your particular machine.
- Machine: Mac with M1 chip (arm64)
- OS: Monterey 12.5
- Shell: zsh
  ```zsh
  zsh --version
  # zsh 5.8.1 (x86_64-apple-darwin21.0)
  ```
- Local Networking: Docksal running Docker containers
  ```zsh
  # Docker version 4.11.1
  docker -v
  # Docker version 20.10.17

  # Docksal version
  fin --version
  # Docksal version: v1.17.0
  # fin version:     1.110.1
  ```

## Create the _Microsoft SQL Server_

1. Pull the image for the MSSQL database from DockerHub. **Mac M1** machines need the
   [Azure SQL Edge container](https://hub.docker.com/_/microsoft-azure-sql-edge), but the image
   for [Microsoft SQL Server](https://hub.docker.com/_/microsoft-mssql-server) is also available.
    ```zsh
    # Azure SQL Edge (Mac with M1 chip, arm64).
    docker pull mcr.microsoft.com/azure-sql-edge

    # Microsoft SQL Server.
    # docker pull mcr.microsoft.com/mssql/server
    ```
4. Spin up the SQL Server container. (For [Microsoft SQL Server](https://hub.docker.com/_/microsoft-mssql-server), follow the instructions under _How to use this image_)
    ```zsh
      # Azure SQL Edge (Mac M1 chip, arm64).
      docker run --cap-add SYS_PTRACE -e 'ACCEPT_EULA=1' -e 'MSSQL_SA_PASSWORD=P@ssword' -p 1433:1433 --name azuresqledge -d mcr.microsoft.com/azure-sql-edge
    ```
5. Ensure the container is running.
   ```zsh
   # List all containers.
   docker ps
   ```

## Install _Azure Data Studio_ and set up the database

4. With the container running, download and install [Azure Data Studio](https://docs.microsoft.com/en-us/sql/azure-data-studio/download-azure-data-studio?view=sql-server-ver16) to your local machine.
   <br>**NOTE:** _A note on the linked page states Azure Data Studio does **not** support ARM architecture. However, it did work as part of developing this documentation._
5. With Azure Data Studio running, [create a new connection to the server](https://database.guide/how-to-install-azure-data-studio-on-a-mac/) (see _Connect to SQL Server_).
6. Copy your database dump file (*.bak) into the docker container running your MSSQL server.
    ```zsh
    # Copy the backup file to the MSSQL container.
    # Adjust paths as necessary.
    # Replace 'azuresqledge' with the name of your mssql container name.
    docker cp path/to/file/on/your/machine/filename.bak azuresqledge:/var/backups/filename.bak
    ```
7. Using the backup file, [create/restore the database](https://database.guide/how-to-restore-a-sql-server-database-on-a-mac-using-azure-data-studio/) from the file. <br>
   **If database fails to restore:** Running out of memory is a common problem. You may need to increase the available memory to Docker or [clean up old containers](https://dockerwebdev.com/tutorials/clean-up-docker/).

## Install ODBC drivers to your Docksal cli image.
___
&#9757;
_**IMPORTANT:** You must repeat this section every time you update your Docksal CLI Image!_
___
8. Connecting to the MSSQL server requires the installation of the ODBC drivers to Docksal's cli container. This can be done from the command line.
    ```zsh
    # Log in to cli.
    fin bash
    ```
9. Install the prerequisites.
    ```zsh
    # Install ODBC drivers for Linux (Debian).
    sudo su
    curl https://packages.microsoft.com/keys/microsoft.asc | apt-key add -
    # Debian 11 packages.
    curl https://packages.microsoft.com/config/debian/11/prod.list > /etc/apt/sources.list.d/mssql-release.list
    exit

    sudo apt-get update
    sudo ACCEPT_EULA=Y apt-get install -y msodbcsql18

   # optional: for bcp and sqlcmd
    sudo ACCEPT_EULA=Y apt-get install -y mssql-tools18
    echo 'export PATH="$PATH:/opt/mssql-tools18/bin"' >> ~/.bashrc
    source ~/.bashrc

   # optional: for unixODBC development headers
    sudo apt-get install -y unixodbc-dev
    ```
10. Install and enable the [PHP drivers for Microsoft SQL Server](https://docs.microsoft.com/en-us/sql/connect/php/installation-tutorial-linux-mac?view=sql-server-ver16) (See Step 3).
    ```zsh
    # Install.
    sudo pecl install sqlsrv
    sudo pecl install pdo_sqlsrv

    # Enable the extensions.
    sudo su
    printf "; priority=20\nextension=sqlsrv.so\n" > /usr/local/etc/php/conf.d/docker-php-ext-sqlsrv.ini
    printf "; priority=30\nextension=pdo_sqlsrv.so\n" > /usr/local/etc/php/conf.d/docker-php-ext-pdo_sqlsrv.ini
    exit
    ```
11. Verify the extensions have been enabled.
    ```zsh
    # Should see pdo_sqlsrv and sqlsrv.
    php -m | grep sqlsrv

    # Exit out of the Docksal cli shell.
    exit
    ```
    You also can verify the extension have been enabled by placing `phpinfo();` in the _docroot/index.php_ page and bringing up any page of the site.<br>

## Install Drupal modules and configure the database connection

12. With a working SQL server, source database and the ODBC drivers installed, it's time to install [the module to help Drupal connect](https://www.drupal.org/project/sqlsrv)
    as well as a [module that provides a custom source plugin](https://www.drupal.org/project/custom_sql_migrate_source_plugin) for writing custom migrations.
    ```zsh
    # Install the sqlsrv module to Drupal.
    composer require drupal/sqlsrv
    # Install the custom_sql_migrate_source_plugin module.
    composer require drupal/custom_sql_migrate_source_plugin

    #Enable the module.
    fin drush en sqlsrv,custom_sql_migrate_source_plugin
    ```
13. You will likely need to install the patch for the _sqlsrv_ module. Add the following line to your _composer.json_ file in the **patches** section:
    ```json
    "drupal/sqlsrv": {
        "#3291199: Fixes a connection error in sqlsvr": "https://www.drupal.org/files/issues/2022-06-18/sqlsrv-undefined-key-return-3291199-4.patch"
    }
    ```
14. Configure the database connection for the migration source. Use the same username and password as you did to connect in Azure Data Studio.
    The `;Encrypt=false` keyword needs to be added if you experience certificate errors while testing the connection in the next step.
    You can also include `;TrustServerCertificate=true` if certificate errors persist. If necessary, [other keywords can be added](https://docs.microsoft.com/en-us/sql/relational-databases/native-client/applications/using-connection-string-keywords-with-sql-server-native-client?view=sql-server-ver16).
    Separate each key=value pair with a semi-colon (;). Be sure _not_ to include a closing semi-colon at the end of the line as it will be added
    later by the `\Drupal\sqlsrv\Driver\Database\Connection::open()` method.
    ```php
    # Migration source database connection array.
    # The structure of the 'database' value with ODBC keywords
    #   is 'database_name;directive=value;directive=value'.
    # Example: 'Drupal;Encrypt=false;TrustedServerCertificate=true'.
    $databases['migrate']['default'] = [
      'database' => 'Drupal;Encrypt=false', // Do not include the closing ';'.
      'username' => 'sa', // 'sa' is the default unless you changed it.
      'password' => 'P@ssword',
      'host' => '192.168.64.100', // This is the IP for the Docksal network.
      'port' => '1433', // Default for MSSQL.
      'namespace' => 'Drupal\\sqlsrv\\Driver\\Database\\sqlsrv',
      'autoload' => 'modules/contrib/sqlsrv/src/Driver/Database/sqlsrv', // Adjust path as necessary.
      'prefix' => '',
      'driver' => 'sqlsrv',
    ];
    ```
15. Test if Drupal can connect to the database and perform a simple query.<br>
    Open the PHP shell.
    ```zsh
    # Log in to the php shell.
    fin drush php
    ```
    Copy/Paste this line after the prompt (>>>).
    ```php
    # Attempt to connect to the database.
    $db = \Drupal\Core\Database\Database::getConnection('default', 'migrate');
    # Desired return will look something like this.
    # => Drupal\sqlsrv\Driver\Database\sqlsrv\Connection

    # If successful, try a simple query using dynamic query syntax.
    # Example query for D7 bundle types.
    $db->select('node_type', 'nt')->fields('nt', ['name'])->execute()->fetchCol();
    # Desired return will be an array.
    ```
    If successful, exit the PHP shell.
    ```zsh
    exit
    ```
