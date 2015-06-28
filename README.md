# Секретный проект [Avaritia](https://ru.wikipedia.org/wiki/%D0%90%D0%BB%D1%87%D0%BD%D0%BE%D1%81%D1%82%D1%8C)

Совершенно секретный проект и ниже читать запрещено.

## Требования

-   php >= 5.4
-   nginx
-   php-fpm (с пробросом DOCUMENT_URI)


## Настройка

### PHP

-   ```bash
    short_open_tag = On
    ```
-   Ошибки обрабатываются через trigger_error
    ```bash
    error_log = /path/to/whatever
    ```

### nginx

-   Пример конфигурационного файла:
    ```Nginx
    server {
        listen avaritia;
        server_name avaritia;
        root /var/www/public;
        index /index.php;

        location ~* "^/js/.*\.js(\.map){0,1}$" { }
        location ~* ^/fonts/.*\.(ttf)$ { }
        location ~* ^/css/.*\.(css|woff|svg|eot)$ { }
        location ~* ^/images/.*\.(ico|png|bmp|jpg|gif)$ { }

        location /  {
            fastcgi_param AVARITIA_ENVIRONMENT production;
            fastcgi_param SCRIPT_FILENAME /var/www/public/index.php;
            fastcgi_pass unix:/var/run/php5-fpm.sock;
            include fastcgi_params;
            fastcgi_intercept_errors on;
        }
    }
    ```

## Кто здесь?

Антон Загорский amberovsky@gmail.com
