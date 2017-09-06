module.exports = {
    src: [
        'Gruntfile.js',
        '../themes/Frontend/**/_public/src/js/*.js',
        '../engine/Shopware/Plugins/**/frontend/**/src/js/**/*.js',
        '../custom/plugins/**/frontend/**/src/js/**/*.js'
    ],
    options: {
        configFile: '.eslintrc.js'
    }
};
