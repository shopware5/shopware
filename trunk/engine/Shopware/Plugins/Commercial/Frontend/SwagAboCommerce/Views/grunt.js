/*global module:false*/
module.exports = function(grunt) {
    grunt.initConfig({
        concat: {
            dist: {
                src: 'frontend/_resources/javascript/src/swag_abo_commerce.js',
                dest: 'frontend/_resources/javascript/swag_abo_commerce.min.js'
            }
        },

        min: {
            dist: {
                src: 'frontend/_resources/javascript/src/swag_abo_commerce.js',
                dest: 'frontend/_resources/javascript/swag_abo_commerce.min.js'
            }
        },
        lint: {
            files: [ 'grunt.js', 'frontend/_resources/javascript/src/swag_abo_commerce.js' ]
        },
        watch: {
            files: 'frontend/_resources/javascript/src/swag_abo_commerce.js',
            tasks: 'lint min'
        },

        jshint: {
            options: {
                curly: true,
                eqeqeq: true,
                immed: true,
                latedef: true,
                newcap: true,
                noarg: true,
                sub: true,
                undef: true,
                boss: true,
                eqnull: true,
                browser: true,
                debug: true
            },
            globals: {
                jQuery: true,
                console: true
            }
        },
        uglify: {}
    });


    // Default task.
    grunt.registerTask('default', 'lint min');
};