/**
 * @author amberovsky
 *
 * Сборка проекта
 */

var
    DIR_PUBLIC      = './public/', // целевой каталог
    DIR_PUBLIC_JS   = DIR_PUBLIC + 'js/avaritia/', // целевой каталог с js файлами
    DIR_PUBLIC_CSS  = DIR_PUBLIC + 'css/avaritia/', // целевой каталог с css файлами
    DIR_CLIENT      = './source/client/src/', // каталог с исходными файлами
    DIR_CLIENT_JS   = DIR_CLIENT + 'js/', // каталог с иходными js файлами
    DIR_CLIENT_CSS  = DIR_CLIENT + 'css/', // каталог с исходными css файлами
    p               = require('path'),
    revision        = null; // текущая ревизия

module.exports = function (grunt) {
    if (!grunt.option('revision')) {
        grunt.fail.fatal('нет параметра с ревизией (запуск не через ./bin/*.sh?)');
    }

    revision = grunt.option('revision');

    var
        /**
         * @param {string} dest целевой каталог
         * @param {string} src  целевое имя файла
         *
         * @returns {string} целевое имя файла с ревизией
         */
        renameWithRevision = function(dest, src) {
            return dest + src.replace(/(\..*)$/, '_' + revision + '$1');
        };

    grunt.loadNpmTasks('grunt-contrib-clean');
    grunt.loadNpmTasks('grunt-contrib-copy');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadTasks('tasks');

    // возвращаемся в корень проекта, чтобы grunt не ругался на выход за пределы рабочего каталога
    grunt.file.setBase('../../');

    grunt.initConfig({
        pkg: require('./package.json'),

        clean: {
            js: [DIR_PUBLIC_JS],
            css: [DIR_PUBLIC_CSS]
        },

        copy: {
            js: {
                src: ['index.js', 'customer.js', 'api.js', 'executor.js']
                        .map(function ($file) { return DIR_CLIENT_JS + $file; })
                        .concat(['./configuration/config.js']),
                dest: DIR_PUBLIC_JS,
                expand: true,
                flatten: true,
                rename: renameWithRevision
            },

            css: {
                src: DIR_CLIENT_CSS + 'avaritia.css',
                dest: DIR_PUBLIC_CSS,
                expand: true,
                flatten: true,
                rename: renameWithRevision
            }
        },

        uglify: {
            options: {
                beautify: true,
                compress: false,
                mangle: false
            },

            production: {
                files: [{
                    expand: true,
                    cwd: DIR_PUBLIC_JS,
                    src: '*.js',
                    dest: DIR_PUBLIC_JS
                }]
            }
        }
    });

    grunt.registerTask('development', 'билд проекта для development окружения', ['revision', 'clean', 'copy']);
    grunt.registerTask('production', 'билд проекта для production окружения', ['development', 'uglify']);
};
