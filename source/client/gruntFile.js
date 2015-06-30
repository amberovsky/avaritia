/**
 * @author amberovsky
 *
 * Сборка проекта
 */

var
    DIR_PUBLIC  = './public/js/avaritia/', // целевой каталог
    DIR_CLIENT  = './source/client/src', // каталог с исходными файлами
    p           = require('path'),
    revision    = null; // текущая ревизия

module.exports = function (grunt) {
    if (!grunt.option('revision')) {
        grunt.fail.fatal('нет параметра с ревизией (запуск не через ./bin/*.sh?)');
    }

    revision = grunt.option('revision');

    grunt.loadNpmTasks('grunt-contrib-clean');
    grunt.loadNpmTasks('grunt-contrib-copy');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadTasks('tasks');

    // возвращаемся в корень проекта, чтобы grunt не ругался на выход за пределы рабочего каталога
    grunt.file.setBase('../../');

    grunt.initConfig({
        pkg: require('./package.json'),

        clean: {
            js: [DIR_PUBLIC]
        },

        copy: {
            js: {
                src: ['index.js', 'customer.js', 'api.js'].map(function ($file) { return DIR_CLIENT + '/' + $file; }).concat(['./configuration/config.js']),
                dest: DIR_PUBLIC,
                expand: true,
                flatten: true,
                rename: function(dest, src) {
                    return dest + src.replace(/(\..*)$/, '_' + revision + '$1');
                }
            }
        },

        uglify: {
            options: {
                beautify: true,
                compress: false,
                mangle: false
            },

            prod: {
                files: [{
                    expand: true,
                    cwd: DIR_PUBLIC,
                    src: '*.js',
                    dest: DIR_PUBLIC
                }]
            }
        }
    });

    grunt.registerTask('development', 'билд проекта для development окружения', ['revision', 'clean', 'copy']);
    grunt.registerTask('production', 'билд проекта для production окружения', ['development', 'uglify']);
};
