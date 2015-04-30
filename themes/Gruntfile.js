module.exports = function (grunt) {
    grunt.option.init({
        shopId: 1
    });

    var file =  '../web/cache/config_' + grunt.option('shopId') + '.json';
    var config = grunt.file.readJSON(file);

    var lessTargetFile = {};
    lessTargetFile['../' + config['lessTarget']] = '../web/cache/all.less';

    var jsFiles = [];
    config['js'].forEach(function (item) {
        jsFiles.push('../' + item);
    });

    var jsTargetFile = {};
    jsTargetFile['../' + config['jsTarget']] = jsFiles;

    var content = '';
    config['less'].forEach(function (item) {
        content += '@import "../' + item + '";';
        content += "\n";
    });
    grunt.file.write('../web/cache/all.less', content);

    var variables = {
        'font-directory': '"../../themes/Frontend/Responsive/frontend/_public/src/fonts"',
        'OpenSansPath': '"../../themes/Frontend/Responsive/frontend/_public/vendors/fonts/open-sans-fontface"'
    };

    for (var key in config['config']) {
        variables[key] = config['config'][key];
    }

    grunt.initConfig({
        uglify: {
            target: {
                files: jsTargetFile
            }
        },
        less: {
            development: {
                options: {
                    modifyVars: variables
                },
                files: lessTargetFile
            },
            debug: {
                options: {
                    modifyVars: variables,
                    dumpLineNumbers: 'all',
                    sourceMap: true,
                    sourceMapFileInline: true
                },
                files: lessTargetFile
            }
        },
        watch: {
            less: {
                files: [
                    '../engine/Shopware/Plugins/**/*.less',
                    '../themes/Frontend/**/*.less'
                ],
                tasks: ['less:development']
            },
            js: {
                files: [
                    '../engine/Shopware/Plugins/**/*.js',
                    '../themes/Frontend/**/*.js'
                ],
                tasks: ['uglify']
            }
        }
    });

    grunt.loadNpmTasks('grunt-contrib-less');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-contrib-uglify');

    grunt.registerTask('default', ['less:development', 'uglify', 'watch']);
};
