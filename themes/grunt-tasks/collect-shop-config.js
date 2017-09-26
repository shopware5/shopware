var path = require('path');

module.exports = (grunt) => {
    var shopId = grunt.option('shopId') || 1,
        file = '../web/cache/config_' + shopId + '.json',
        config = grunt.file.readJSON(file),
        lessTargetFile = {},
        jsFiles = [],
        jsTargetFile = {},
        content = '',
        inheritancePath = config.inheritancePath,
        themesTasks = {};

    lessTargetFile['../' + config.lessTarget] = '../web/cache/all.less';

    config.js.forEach(function (item) {
        jsFiles.push('../' + item);
    });
    jsTargetFile['../' + config.jsTarget] = jsFiles;

    for (var key in config.config) {
        content += '@' + key + ': ' + config.config[key] + ';';
        content += '\n';
    }

    config.less.forEach(function (item) {
        if (/(\.css)$/.test(item)) {
            // Entry is a css file and needs to be imported inline
            content += `@import (inline) "../${item}";`;
        } else {
            content += `@import "../${item}";`;
        }
    });

    grunt.file.write('../web/cache/all.less', content);

    inheritancePath.forEach(function (item) {
        var folderPath = path.join(__dirname, '../Frontend', item);

        if (!grunt.file.exists(folderPath, 'Gruntfile.js')) {
            return;
        }

        themesTasks[item] = {};
        themesTasks[item][folderPath] = 'default';
    });

    return {
        jsTargetFile,
        lessTargetFile,
        jsFiles,
        themesTasks
    };
};
