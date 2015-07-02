#!/usr/bin/env bash

#
# @author amberovsky
#
# Откат на предыдущую ревизию
# Скрипт должен лежать где-то снаружи
#

# базовый каталог деплоя (со слешом на конце)
deploy_base_dir=/home/www/avaritia/

# симлинк проекта (указанный в конфиге nginx)
symlink=${deploy_base_dir}www

# команды рестарта php-fpm
restart_fpm="sudo /usr/sbin/service php5-fpm reload"

# получаем список ревизий в массив
revisions=($( (ls ${deploy_base_dir} | grep -E '^[[:digit:]]+$' | sort -r)))

if [ ${#revisions[@]} -lt 2 ]; then
    printf "не могу откатить ревизию - в каталоге всего одна ревизия\n"
    exit
fi

# текущая ревизия
revision_current=${revisions[0]}

# предыдущая ревизия
revision_previous=${revisions[1]}

printf "откатываю ${revision_current} на ${revision_previous}\n"

# симлинк
printf "симлинк\n"
ln -sfn ${deploy_base_dir}${revision_previous} ${symlink}

# рестарт php-fpm
printf "рестарт php-fpm\n"
${restart_fpm}

printf "удаляю старую ревизию\n"
rm -rf ${deploy_base_dir}${revision_current}
