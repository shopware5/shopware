module.exports = {
    production: {
        options: {
            compress: true,
            preserveComments: false
        },
        files: '<%= jsTargetFile %>'
    },
    development: {
        options: {
            mangle: false,
            compress: false,
            beautify: true,
            preserveComments: 'all'
        },
        files: '<%= jsTargetFile %>'
    }
};
