module.exports = (grunt) => {
    var shopId = grunt.option('shopId') || 1,
        file = '../web/cache/config_' + shopId + '.json',
        config = grunt.file.readJSON(file),
        lessTargetFile = {},
        jsFiles = [],
        jsTargetFile = {},
        content = '';

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

    return {
        jsTargetFile,
        lessTargetFile,
        jsFiles
    };
};
