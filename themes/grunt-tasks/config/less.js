const md5File = require('md5-file');
const crypto = require('crypto');

const functions = {
    swhash: function (_, filename) {
        const path = filename._fileInfo.currentDirectory + filename.value;
        const pathHash = md5File.sync(path);
        const shopwareRevision = this.context
            .frames
            .filter(o => o._variables)
            .map(o => o._variables['@shopware-revision'])
            .filter(o => o)
            .map(o => o.value.value)
            .pop()
        ;
        filename.value = crypto
            .createHash('md5')
            .update(shopwareRevision + pathHash)
            .digest('hex')
        ;
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
