#!/usr/bin/env bash

#
# @author amberovsky
#
# Билд проекта на машине разработчика
# Скрипт надо выполнять в папке с проектом
#

# ревизия - текущий таймстамп
revision=$(date +%s)

# отваливаемся при ошибке на любом этапе
set -e

printf "билд ревизии ${revision}\n"

# 1. сборка клиентских файлов & запись ревизии
printf "#1 сборка клиентских файлов & запись ревизии\n"
./source/client/node_modules/grunt-cli/bin/grunt --gruntfile ./source/client/gruntFile.js development --revision=${revision}
