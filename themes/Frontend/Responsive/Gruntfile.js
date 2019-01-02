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
                        nodeDir + '/jquery/LICENSE.txt'
                    ],
                    dest: vendorDir + '/js/jquery',
                    flatten: true
                }]
            },
            'normalize-less': {
                files: [{
                    expand: true,
                    src: [
                        nodeDir + '/normalize.css.less/normalize.less',
                        nodeDir + '/normalize.css.less/README.md',
                        nodeDir + '/normalize.css.less/LICENSE.md'
                    ],
                    dest: vendorDir + '/less/normalize-less',
                    flatten: true
                }]
            },
            'pocketgrid-less': {
                files: [{
                    expand: true,
                    src: [
                        nodeDir + '/pocketgrid-less/pocketgrid.less',
                        nodeDir + '/pocketgrid-less/README.md',
                        nodeDir + '/pocketgrid-less/LICENSE'
                    ],
                    dest: vendorDir + '/less/pocketgrid',
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
            },
            'flatpickr': {
                files: [{
                    expand: true,
                    src: [
                        nodeDir + '/flatpickr/dist/flatpickr.min.js',
                        nodeDir + '/flatpickr/LICENSE.md',
                        nodeDir + '/flatpickr/README.md'
                    ],
                    dest: vendorDir + '/js/flatpickr',
                    flatten: true
                }]
            },
            'open-sans-fontface': {
                files: [{
                    expand: true,
                    cwd: nodeDir + '/open-sans-fontface/fonts',
                    src: [
                        '*/**.ttf',
                        '*/**.woff2',
                        '*/**.woff'
                    ],
                    dest: vendorDir + '/fonts/open-sans-fontface'
                }]
            },
            'open-sans-fontface-readme': {
                files: [{
                    expand: true,
                    src: nodeDir + '/open-sans-fontface/README.md',
                    dest: vendorDir + '/fonts/open-sans-fontface',
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

    grunt.registerTask('createFontHtaccess', 'Creates a .htaccess file for the OpenSans font', function() {
        var htaccessFile = vendorDir + '/fonts/open-sans-fontface/.htaccess';
        if (!grunt.file.isFile(htaccessFile)) {

            grunt.file.write(htaccessFile,
                '<FilesMatch "\\.(ttf|eot|svg|woff|woff2)$">' + "\n    " +
                    '<IfModule mod_expires.c>' + "\n        " +
                        'ExpiresActive on' + "\n        " +
                        'ExpiresDefault "access plus 1 year"' + "\n    " +
                    '</IfModule>' + "\n\n    " +

                    '<IfModule mod_headers.c>' + "\n        " +
                        'Header set Cache-Control "max-age=31536000, public"' + "\n        " +
                        'Header unset ETag' + "\n    " +
                    '</IfModule>' + "\n\n    " +

                    'FileETag None' + "\n" +
                '</FilesMatch>'
            );

            // Output a success message
            grunt.log.oklns(grunt.template.process(
                'File "<%= file %>" was created successfully.',
                { data: { file: htaccessFile } }
            ));
        }
    });

    grunt.registerTask('default', [ 'clean', 'createVendorDir', 'copy', 'createFontHtaccess' ]);
    grunt.loadNpmTasks('grunt-contrib-clean');
    grunt.loadNpmTasks('grunt-contrib-copy');
};
