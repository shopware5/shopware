const path = require('path');

const resolve = (relativePath) => {
    return path.resolve(__dirname, relativePath);
};

// http://nightwatchjs.org/getingstarted#settings-file
module.exports = {
    src_folders: [
        resolve('./specs')
    ],
    output_folder: resolve('./reports'),
    custom_assertions_path: resolve('./custom-assertions'),

    // Selenium config - We're using the node packages `selenium-server` and `chromedriver` to automatically
    // bind the correct binaries
    selenium: {
        start_process: true,
        server_path: require('selenium-server').path,
        host: '127.0.0.1',
        port: 4444,
        cli_args: {
            'webdriver.chrome.driver': require('chromedriver').path
        }
    },

    // Run test specs in parallel
    test_workers: {
        enabled: false,
        workers: 'auto'
    },

    test_settings: {
        // Selenium setup
        default: {
            selenium_port: 4444,
            selenium_host: 'localhost',
            silent: true,
            launch_url: (process.env.URL || 'http://localhost'),
            globals: {
                devServerURL: 'http://shopware-next.local:' + (process.env.PORT || '80')
            }
        },

        // Set up chrome in headless mode
        chromeHeadless: {
            desiredCapabilities: {
                browserName: 'chrome',
                javascriptEnabled: true,
                acceptSslCerts: true,
                chromeOptions: {
                    args: [
                        'headless',
                        'no-sandbox',
                        'disable-gpu'
                    ]
                }
            }
        },

        chrome: {
            desiredCapabilities: {
                browserName: 'chrome',
                javascriptEnabled: true,
                acceptSslCerts: true
            }
        },

        phantomjs: {
            desiredCapabilities : {
                browserName: 'phantomjs',
                javascriptEnabled: true,
                acceptSslCerts: true,
                'phantomjs.binary.path': require('phantomjs-prebuilt').path,
                'phantomjs.cli.args': []
            }
        }
    }
};
