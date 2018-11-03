module.exports = {
    before: (client) => {
        client
            .url(client.launchUrl + '/backend')
            .expect.element('body').to.be.present.before(5000);
    },

    after: (client) => {
        client.end();
    },

    'Login user with default credentials': (client) => {
        client.expect.element('.login-window').to.be.present.before(5000);
        
        client
            .setValue('input[name=username]', 'demo')
            .setValue('input[name=password]', 'demo')
            .sendKeys('input[name=password]', client.Keys.ENTER);

        client.expect.element('body').to.be.present.before(5000);
        client.expect.element('.shopware-menu').to.be.present.before(10000);
    }
};

