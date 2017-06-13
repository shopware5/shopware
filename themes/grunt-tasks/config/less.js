module.exports = {
    production: {
        options: {
            compress: true,
            relativeUrls: true
        },
        files: '<%= lessTargetFile %>'
    },
    development: {
        options: {
            dumpLineNumbers: 'all',
            relativeUrls: true,
            sourceMap: true,
            sourceMapFileInline: true,
            sourceMapRootpath: '../'
        },
        files: '<%= lessTargetFile %>'
    }
};
