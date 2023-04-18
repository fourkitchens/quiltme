#!/bin/bash

cd web/themes/custom/quiltme
lando npm ci
lando npm run storybook:build
