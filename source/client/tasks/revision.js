/**
 * @author amberovsky
 *
 * Запись файлов ревизии при билде проекта
 *
 */

module.exports = function (grunt) {
    grunt.registerTask('revision', 'запись файлов ревизии проекта', function () {
        var
            fs = require('fs'),
            os = require('os'),
            DIR = __dirname + '/../../../configuration/',
            revision = grunt.option('revision');

        try {
            fs.writeFileSync(DIR + 'revision.php', '<? define(\'REVISION\', ' +  revision + '); ' + os.EOL);
            fs.writeFileSync(DIR + 'config.js', 'var config = { revision: ' + revision + ' };' + os.EOL);
        } catch (exception) {
            grunt.fail.fatal(exception);
        }
    });
};
