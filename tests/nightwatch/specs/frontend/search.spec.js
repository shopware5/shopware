module.exports = {
    before: (client) => {
        client
            .url(client.launchUrl)
            .expect.element('body').to.be.present.before(1000);
    },

    after: (client) => {
        client.end();
    },

    'Search front page with one hit': (client) => {
        // Fill out the search form and sent it with the ENTER key
        client
            .setValue('input.main-search--field', 'Ibiza')
            .sendKeys('input.main-search--field', client.Keys.ENTER);

        client.expect.element('body').to.be.present.before(1000);
        client.expect.element('h1.search--headline').text.to.equal('Zu "Ibiza" wurden 1 Artikel gefunden!');
        client.expect.element('.product--title').text.to.equal('Strandtuch "Ibiza"');
    },
    'Search with with few hits': (client) => {
        client
            .setValue('input.main-search--field', 'Korn')
            .sendKeys('input.main-search--field', client.Keys.ENTER)

        client.expect.element('h1.search--headline').text.to.equal('Zu "Korn" wurden 3 Artikel gefunden!');
        client.assert.elementCount('.product--box', 3);

        client.expect.element('body').to.be.present.before(1000);
        client.expect.element('.product--box:nth-child(1) .product--title').text.to.equal('Sasse Korn 32%');
        client.expect.element('.product--box:nth-child(2) .product--title').text.to.equal('Münsterländer Lagerkorn 32%');
        client.expect.element('.product--box:nth-child(3) .product--title').text.to.equal('Special Finish Lagerkorn X.O. 32%');
    },

    'Search with many hits': (client) => {
        // Fill out the search form and sent it with the ENTER key
        client
            .setValue('input.main-search--field', 'str')
            .sendKeys('input.main-search--field', client.Keys.ENTER);

        client.expect.element('body').to.be.present.before(1000);
        client.expect.element('h1.search--headline').text.to.equal('Zu "str" wurden 13 Artikel gefunden!');
        client.assert.elementCount('.product--box', 12);
    }
};