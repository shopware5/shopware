module.exports = function (grunt) {
    'use strict';

    var vendorDir = 'frontend/_public/vendors',
        nodeDir = 'node_modules';

    grunt.initConfig({
        clean: {
            vendors: [ vendorDir ]
        },
        copy: {
            'jquery.event.move': {
                files: [{
                    expand: true,
                    src: [
                        nodeDir + '/jquery.event.move/js/jquery.event.move.js',
                        nodeDir + '/jquery.event.move/README.md'
                    ],
                    dest: vendorDir + '/js/jquery.event.move',
                    flatten: true
                }]
            },
            'jquery.event.swipe': {
                files: [{
                    expand: true,
                    src: [
                        nodeDir + '/jquery.event.swipe/js/jquery.event.swipe.js',
                        nodeDir + '/jquery.event.swipe/README.md'
                    ],
                    dest: vendorDir + '/js/jquery.event.swipe',
                    flatten: true
                }]
            },
            'jquery.transit': {
                files: [{
                    expand: true,
                    src: [
                        nodeDir + '/jquery.transit/jquery.transit.js',
                        nodeDir + '/jquery.transit/README.md'
                    ],
                    dest: vendorDir + '/js/jquery.transit',
                    flatten: true
                }]
            },
            'jquery': {
                files: [{
                    expand: true,
                    src: [
                        nodeDir + '/jquery/dist/jquery.min.js',
                        nodeDir + '/jquery/dist/jquery.min.map',
                        nodeDir + '/jquery/README.md',
                        nodeDir + '/jquery/MIT-LICENSE.txt'
                    ],
                    dest: vendorDir + '/js/jquery',
                    flatten: true
                }]
            },
            'picturefill': {
                files: [{
                    expand: true,
                    src: [
                        nodeDir + '/picturefill/dist/picturefill.min.js',
                        nodeDir + '/picturefill/LICENSE',
                        nodeDir + '/picturefill/README.md'
                    ],
                    dest: vendorDir + '/js/picturefill',
                    flatten: true
                }]
            }
        }
    });

    grunt.registerTask('createVendorDir', 'Creates the necessary vendor directory', function() {
        // Create the vendorDir when it's not exists.
        if (!grunt.file.isDir(vendorDir)) {
            grunt.file.mkdir(vendorDir);

            // Output a success message
            grunt.log.oklns(grunt.template.process(
                'Directory "<%= directory %>" was created successfully.',
                { data: { directory: vendorDir } }
            ));
        }
    });

    grunt.registerTask('default', [ 'clean', 'createVendorDir', 'copy' ]);
    grunt.loadNpmTasks('grunt-contrib-clean');
    grunt.loadNpmTasks('grunt-contrib-copy');
};
