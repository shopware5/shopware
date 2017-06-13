var path = require('path');

module.exports = (grunt) => {
    var config = require('./grunt-tasks/collect-shop-config')(grunt);

    require('load-grunt-config')(grunt, {
        configPath: path.join(process.cwd(), 'grunt-tasks/config'),
        jitGrunt: {
            customTasksDir: 'grunt-tasks/tasks'
        },
        data: config
    });
};
