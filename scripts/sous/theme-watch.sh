#!/bin/bash

cd web/themes/custom/quiltme
lando npm ci
lando npm run develop
