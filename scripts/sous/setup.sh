#!/bin/bash

git config --global init.defaultBranch main
git init
lando start
lando npm --prefix ./ install
lando emulsify init quiltme --platform drupal
lando npm --prefix ./web/themes/custom/quiltme install
lando compound-install
lando drush site:install --existing-config -y
lando drush cache-rebuild
