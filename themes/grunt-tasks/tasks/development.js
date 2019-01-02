module.exports = (grunt) => {
    grunt.registerTask('development', ['fileExists:js', 'less:development', 'uglify:development']);
};
