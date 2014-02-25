module.exports = function(grunt) {
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),

        // Check our javascript files against jshint
        jshint: {
            files: [
                'Gruntfile.js',
                'frontend/_public/src/js/**/*.js'
            ],
            options: {
                // Override jshint defaults
                globals: {
                    Handlebars: true,
                    jQuery: true,
                    console: true,
                    window: true,
                    document: true
                }
            }
        },

        // Lint our less files
        lesslint: {
            src: [ 'frontend/_public/src/less/**/*.less' ]
        },

        // Lint our css files
        csslint: {
            src: [ 'frontend/_public/src/css/**/*.css' ],
            options: {
                "box-sizing": false,
                "important": true,
                "zero-units": false
            }
        },

        // Compile less files
        less: {
            development: {
                options: {
                    report: 'min',
                    ieCompat: true,
                    compress: true,
                    dumpLineNumbers: 'all',
                    sourceMap: true,
                    sourceMapFilename: 'frontend/_public/dist/all.map'
                },
                files: {
                    'frontend/_public/dist/all.css': 'frontend/_public/src/less/all.less'
                }
            }
        },

        // Watch less, js and css files for development
        watch: {
            files: [
                '<%= jshint.files %>',
                '<%= lesslint.src %>',
                '<%= csslint.src %>'
            ],
            tasks: [ 'jshint', 'lesslint', 'csslint', 'less' ]
        },

        // Minifies javascript files
        uglify: {
            development: {
                options: {
                    report: 'min',
                    sourceMap: true,
                    sourceMapName: 'frontend/_public/dist/jquery.all.map'
                },
                files: {
                    'frontend/_public/dist/jquery.all.js': [ 'frontend/_public/src/js/**/*.js' ]
                }
            }
        }
    });

    // Load tasks
    grunt.loadNpmTasks('grunt-contrib-jshint');
    grunt.loadNpmTasks('grunt-contrib-csslint');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-contrib-less');
    grunt.loadNpmTasks('grunt-lesslint');
    grunt.loadNpmTasks('grunt-contrib-uglify');

    // Register own tasks
    grunt.registerTask('test', [ 'jshint', 'lesslint', 'csslint' ]);
    grunt.registerTask('default', [ 'jshint', 'lesslint', 'csslint', 'less:development', 'uglify:development' ]);
};