# Секретный проект [Avaritia](https://ru.wikipedia.org/wiki/%D0%90%D0%BB%D1%87%D0%BD%D0%BE%D1%81%D1%82%D1%8C)

Совершенно секретный проект и ниже читать запрещено.

## Требования (docker вполне подойдёт)

- php >= 5.4
- nginx
- php-fpm (с пробросом DOCUMENT_URI)
- php5-memcache
- memcached
- php5-mysqlnd
- mysql-server >= 5.5
- x64
- npm

## Настройка

### PHP

- Короткие открывающие теги

    ```
    short_open_tag = On
    ```
    
- Ошибки обрабатываются через trigger_error

    ```
    error_log = /path/to/whatever
    ```

### nginx

- Пример конфигурационного файла:

    ```Nginx
    server {
      listen avaritia;
      server_name avaritia;
      root /var/www/public;
      index /index.php;
      
      add_header X-Content-Type-Options nosniff;
      add_header X-Frame-Options DENY;
      add_header X-XSS-Protection "1; mode=block;";
      add_header X-Content-Security-Policy "allow 'self';";
      add_header X-WebKit-CSP "allow 'self';";
      
      valid_referers blocked avaritia;
      if ($invalid_referer) {
          set $possible_csrf 1;
      }
      if ($request_method = POST) {
          set $possible_csrf "${possible_csrf}2";
      }
      if ($possible_csrf = 12) {
          return 403;
      }

      location ~* "^/js/.*\.js(\.map){0,1}$" { }
      location ~* ^/fonts/.*\.(ttf)$ { }
      location ~* ^/css/.*\.(css|woff|woff2|svg|eot)$ { }
      location ~* ^/images/.*\.(ico|png|bmp|jpg|gif)$ { }
      location ~* ^(/favicon.ico)|(robots.txt)$ {}

      location /  {
        fastcgi_param AVARITIA_ENVIRONMENT production;
        fastcgi_param SCRIPT_FILENAME /var/www/public/index.php;
        fastcgi_pass unix:/var/run/php5-fpm.sock;
        include fastcgi_params;
        fastcgi_intercept_errors on;
      }
    }
    ```

## Описание проекта

### Разработка

- Стягиваем проект с форка

    ```Bash
    git clone https://github.com/<login>/avaritia.git
    ```

- Устанавливаем зависимости npm, выполняем в `./source/client/` команду `npm i`
- Для сборки `./bin/build_dev.sh`

### Конфигурация

- Конфигурация определяется через параметр окружения `AVARITIA_ENVIRONMENT`.
  В каталоге `configuration/` ищется каталог с таким названием и подгружаются все php конфиги

#### Memcache `memcached.php`

- Каждый инстанс - это пул из минимум одного сервера. Сервера в пуле объявляются перечислением конфигураций в одном разделе инстанса

- См `Memcached.php` и `MemcachedFactory.php`. Используется расширение `memcache`, потому что *ООП* нельзя

#### Mysql `mysql.php`

- В секции `MysqlFactory\CONFIGURATION_INSTANCES` задаётся список инстансов с указанием хоста, порта, логина, пароля

- В секции `MysqlFactory\CONFIGURATION_SHARD_INSTANCES` задаётся список шардовых инстансов с указанием всех шардов

- Везде устанавливается UTF-8

- См `Mysql.php` и `MysqlFactory.php`. Используется расширение `mysql`, потому что *ООП* нельзя

#### Router `router.php`

- В секции `Router\CONFIGURATION_CLI` задаётся список доступных консольных скриптов

- В секции `Router\CONFIGURATION_WEB` задаётся список веб-роутов. Роуты матчатся в порядке first match wins. Проверяется только совпадение части контроллера. Прописать action в шаблон нельзя

#### ServiceManager `sm.php`

- В секции `ServiceManager\CONFIGURATION_FACTORIES` задаётся список фабрик. При инстанциировании в конструктор фабрик передаётся объект ServiceManager

- В ServiceManager можно использовать callable для создания объектов 

### Шелл-скрипты

- `bin/script.sh` запуск скриптов из каталога `src/Script`. Пример:

    ```Bash
    ./bin.script.sh Whatever
    ```
    
    Имеющиеся скрипты:
    * `Init`: первичная инициализация проекта
    * `Warm`: прогрев кешей
    
- `bin/build_dev.sh` сборка проекта на машине разработчика

- `bin/deploy.sh` выкладка на проде

- `bin/rollback.sh` откат на предыдущую ревизию

## Кто здесь?

Антон Загорский amberovsky@gmail.com
