#!/usr/bin/env bash

#
# @author amberovsky
#
# Деплой проекта
# Скрипт должен лежать где-то снаружи
#

# отваливаемся при ошибке на любом этапе
set -e

# ревизия - текущий таймстамп
revision=$(date +%s)
printf "!!! начинаем деплой: $revision\n"

# базовый каталог деплоя (со слешом на конце)
deploy_base_dir=/home/www/avaritia/

# каталог деплоя
deploy_dir=${deploy_base_dir}${revision}

# симлинк проекта (указанный в конфиге nginx)
symlink=${deploy_base_dir}www

# команды рестарта php-fpm
restart_fpm="sudo /usr/sbin/service php5-fpm reload"

# 1. создаём каталог
printf "#1 каталог деплоя ${deploy_dir}\n"
mkdir ${deploy_dir}
cd ${deploy_dir}

# 2. стягиваем проект
printf "#2 стягиваем проект\n"
git clone git@github.com:amberovsky/avaritia.git ./ -vvv

# 3. сборка клиентских файлов & запись ревизии
printf "#3 сборка клиентских файлов & запись ревизии\n"
./source/client/node_modules/grunt-cli/bin/grunt --gruntfile ./source/client/gruntFile.js production --revision=${revision}

# 4. симлинк
printf "#4 симлинк\n"
ln -fsn ${deploy_dir} ${symlink}

# 6. рестарт php-fpm
printf "#6 рестарт php-fpm\n"
${restart_fpm}

# конец
printf "!!! деплой ${revision} завершён\n\n"
