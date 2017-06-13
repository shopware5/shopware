module.exports = (grunt) => {
    grunt.registerTask('production', ['eslint', 'less:production', 'uglify:production']);
};
