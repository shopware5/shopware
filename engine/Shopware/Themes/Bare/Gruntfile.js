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

        // Watch less, js and css files for development
        watch: {
            files: [
                '<%= jshint.files %>',
                '<%= lesslint.src %>',
                '<%= csslint.src %>'
            ],
            tasks: [ 'jshint', 'lesslint', 'csslint' ]
        }
    });

    // Load tasks
    grunt.loadNpmTasks('grunt-contrib-jshint');
    grunt.loadNpmTasks('grunt-contrib-csslint');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-lesslint');

    // Register own tasks
    grunt.registerTask('test', [ 'jshint', 'lesslint', 'csslint' ]);
};