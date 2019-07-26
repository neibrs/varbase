#!/bin/bash

BASEDIR=$(pwd);

echo "$(tput setaf 2)Checking composer version and updating if needed:$(tput sgr 0)";
composer self-update;
echo "$(tput setaf 2)Checking varbase-updater version and updating if needed:$(tput sgr 0)";
composer update vardot/varbase-updater;

#running the updater;
bash ${BASEDIR}/vendor/vardot/varbase-updater/scripts/update/varbase-updater.sh
