window.i18n = new VueI18n({
    fallbackLocale: 'en',
    locale: 'de',
    messages: {
        de: {
            headlineMain: 'Die Auswertung Deiner Shopdaten ist fertig.',
            textShop: `
<h1>Dein Shop</h1>
<h3>hat im <span class="shop-color">Durchschnitt</span> am Tag <span class="shop-color">{averageOrdersPerDay} Bestellungen</span>
mit einem <span class="shop-color">Bestellwert von {averageAmount}€</span>. Am meisten wurde in
der <span class="shop-color">Zeit von {averageHourRange} Uhr</span> bestellt.</h3><div class="nice-graphic"></div>`
        },
        en: {
            headlineMain: 'Your statistics for your shop are ready.',
            textShop: `
<h1>Dein Shop</h1>
<h3>hat im <span class="shop-color">Durchschnitt</span> am Tag <span class="shop-color">{averageOrdersPerDay} Bestellungen</span>
mit einem <span class="shop-color">Bestellwert von {averageAmount}€</span>. Am meisten wurde in
der <span class="shop-color">Zeit von {averageHourRange} Uhr</span> bestellt.</h3><div class="nice-graphic"></div>`
        }
    }
});
