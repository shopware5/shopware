module.exports = (grunt) => {
    grunt.registerTask('default', [ 'fileExists:js', 'less:development', 'uglify:development', 'chokidar' ]);
};
