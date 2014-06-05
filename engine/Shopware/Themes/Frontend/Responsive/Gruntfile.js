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
            src: [ 'frontend/_public/src/less/all.less' ],
            options: {
                imports: [
                    'frontend/_public/src/less/_variables',
                    'frontend/_public/src/less/_mixins',
                    'frontend/_public/src/less/_components',
                    'frontend/_public/src/less/_modules'
                ]
            }
        },

        // Lint our css files
        csslint: {
            src: [ 'frontend/_public/src/css/**/*.css' ],
            options: {
                "box-sizing": false,
                "important": true,
                "zero-units": false,
                'universal-selector': false
            }
        },

        // Compile less files
        less: {
            development: {
                options: {
                    report: 'min',
                    ieCompat: true,
                    compress: false,
                    dumpLineNumbers: 'all',
                    sourceMap: true,
                    outputSourceFiles: true,
                    sourceMapFilename: 'frontend/_public/dist/all.css.map',
                    sourceMapBasepath: 'frontend/_public/dist/'
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
                'frontend/_public/src/less/**/*.less',
                '<%= csslint.src %>'
            ],
            tasks: [ 'jshint', 'lesslint', 'csslint', 'less', 'uglify:development' ]
        },

        // Minifies javascript files
        uglify: {
            development: {
                options: {
                    mangle: false,
                    report: 'min',
                    sourceMap: true,
                    sourceMapName: 'frontend/_public/dist/all.js.map'
                },
                files: {
                    'frontend/_public/dist/all.js': [
                        'frontend/_public/vendors/jquery/dist/jquery.js',
                        'frontend/_public/vendors/handlebars/handlebars.js',
                        'frontend/_public/vendors/picturefill/picturefill.js',
                        'frontend/_public/vendors/jquery.transit/jquery.transit.js',
                        'frontend/_public/vendors/jquery.event.move/js/jquery.event.move.js',
                        'frontend/_public/vendors/jquery.event.swipe/js/jquery.event.swipe.js',
                        'frontend/_public/src/js/**/*.js'
                    ]
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