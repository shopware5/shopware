const path = require('path');

module.exports = {
    clearMocks: true,

    rootDir: path.resolve(__dirname),

    testMatch: [
        '**/tests/**spec.js'
    ],

    setupFilesAfterEnv: ['./jest.setup.js'],
    testEnvironment: 'jsdom'
};
