# uwec 

[![CircleCI](https://circleci.com/gh/electriccitizen/uwec.svg?style=shield)](https://circleci.com/gh/electriccitizen/uwec)
[![Dashboard uwec](https://img.shields.io/badge/dashboard-uwec-yellow.svg)](https://dashboard.pantheon.io/sites/dfeadf45-ac5d-48f4-a701-c121589cff0e#dev/code)
[![Dev Site uwec](https://img.shields.io/badge/site-uwec-blue.svg)](http://dev-uwec.pantheonsite.io/)



## Secrets
Some secrets are stored in [Pantheon Terminus](https://docs.pantheon.io/terminus) using the [Secrets Manager Plugin](https://github.com/pantheon-systems/terminus-secrets-manager-plugin).
To see the list of secrets, you can run `terminus secret:site:list uwec`.
To set a secret, you can run a command like this `terminus secret:site:set --scope=web -- uwec SECRET_NAME "SECRET_VALUE_HERE"`.
To update an existing secret, it's the same as the above command, but without the --scope=web
These secret values are injected into Drupal in sites/default/settings.php.
