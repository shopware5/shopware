module.exports = (grunt) => {
    grunt.registerTask('production', ['less:production', 'uglify:production']);
};
