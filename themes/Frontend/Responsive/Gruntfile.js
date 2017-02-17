module.exports = function (grunt) {
    'use strict';

    var vendorDir = 'frontend/_public/vendors',
        nodeDir = 'node_modules';

    grunt.initConfig({
        clean: {
            vendors: [ vendorDir ]
        },
        copy: {
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
            'open-sans-fontface': {
                files: [{
                    expand: true,
                    cwd: nodeDir + '/open-sans-fontface/fonts',
                    src: [
                        '*/**.ttf',
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
                '<FilesMatch "\\.(ttf|eot|svg|woff)$">' + "\n    " +
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
