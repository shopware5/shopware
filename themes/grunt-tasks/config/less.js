const md5File = require('md5-file');

const functions = {
    swhash: function (_, filename) {
        const path = filename._fileInfo.currentDirectory + filename.value;
        filename.value = md5File.sync(path);
        return filename;
    }
};

module.exports = {
    production: {
        options: {
            compress: true,
            relativeUrls: true,
            customFunctions: functions
        },
        files: '<%= lessTargetFile %>'
    },
    development: {
        options: {
            dumpLineNumbers: 'all',
            relativeUrls: true,
            sourceMap: true,
            sourceMapFileInline: true,
            sourceMapRootpath: '../',
            customFunctions: functions
        },
        files: '<%= lessTargetFile %>'
    }
};
