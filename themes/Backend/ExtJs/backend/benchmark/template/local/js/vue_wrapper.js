(function () {
    'use strict';

    $(function () {
        window.vueInstance = new Vue({
            el: '.wrapper',
            i18n: window.i18n,
            data: function () {
                return {
                    benchmarkData: window.benchmarkData
                }
            },
            computed: {
                local: function () {
                    return this.benchmarkData.local;
                },

                payments: function () {
                    return this.adjustEntrySize(this.benchmarkData.local.payments);
                },

                sortedPayments: function () {
                    return this.sortEntries(this.payments);
                },

                shipments: function () {
                    return this.adjustEntrySize(this.benchmarkData.local.shipments);
                },

                sortedShipments: function () {
                    return this.sortEntries(this.shipments);
                },

                devices: function () {
                    return this.benchmarkData.local.devices;
                },
            },

            mounted: function() {
                document.querySelector('.loading-wrapper').style.display = 'none';
            },

            methods: {
                onChangeLanguage: function () {
                    var lastLocale = this.$i18n.locale;
                    this.$i18n.locale = (lastLocale === 'de' ? 'en' : 'de');
                },

                adjustEntrySize: function (entries) {
                    var amountOfNecessaryKeys = 5,
                        entryKeys = Object.keys(entries),
                        orderedEntryList = { },
                        entryCount = 0;

                    entryKeys.forEach(function (currentEntryKey) {
                        var currentPayment = entries[currentEntryKey];

                        // Prevent more than 5 entries
                        if (entryCount < amountOfNecessaryKeys) {
                            orderedEntryList[currentEntryKey] = currentPayment;
                            entryCount++;
                        }
                    });

                    // Fill to 5 entries
                    if (entryCount < amountOfNecessaryKeys) {
                        var difference = amountOfNecessaryKeys - entryCount;

                        for (var i = 0; i < difference; i++) {
                            orderedEntryList[i] = -1;
                        }
                    }

                    return orderedEntryList;
                },

                /**
                 * @param { Array } entries
                 * @returns { Array }
                 */
                sortEntries: function (entries) {
                    var entryArray = [];

                    Object.keys(entries).forEach(function (currentEntryKey) {
                        var value = entries[currentEntryKey];
                        if (value === -1) {
                            return;
                        }

                        entryArray.push(entries[currentEntryKey]);
                    });

                    entryArray.sort(function (a, b) {
                        return a - b;
                    });

                    entryArray.splice(Math.floor(entryArray.length / 2), 0, entryArray.pop());

                    return entryArray;
                }
            },
            delimiters: ['[[', ']]']
        });
    });
})();
