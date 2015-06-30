#!/usr/bin/env bash

#
# @author amberovsky
#
# Запуск скрипта. Нужно указывать окружение, например AVARITIA_ENVIRONMENT=development ./bin/script.sh Test
#

set -e

if [[ $# -ne 1 ]]; then
    echo "Не указано имя скрипта, пример AVARITIA_ENVIRONMENT=development ./bin/script.sh Test"
    exit
fi

`which php` -f ./public/index.php script $1
