module.exports = function(grunt) {
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),

        bower: {
            install: {
                options: {
                    targetDir: './frontend/_public/vendors',
                    verbose: true,
                    install: true,
                    cleanup: true
                }
            }
        },
        clean: [
            './frontend/_public/vendors/doc-ready',
            './frontend/_public/vendors/eventEmitter',
            './frontend/_public/vendors/eventie',
            './frontend/_public/vendors/get-size',
            './frontend/_public/vendors/get-style-property',
            './frontend/_public/vendors/matches-selector',
            './frontend/_public/vendors/outlayer',
            './frontend/_public/vendors/jquery.transit'
        ]
    });

    grunt.loadNpmTasks('grunt-bower-task');
    grunt.loadNpmTasks('grunt-contrib-clean');

    grunt.registerTask('default', [ 'bower', 'clean' ]);
};