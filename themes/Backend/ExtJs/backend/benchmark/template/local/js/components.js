(function () {
    $(function () {
        Vue.component('payment-graph-list-wrapper', {
            computed: {
                paymentsList: function () {
                    return this.$root.payments;
                }
            },
            template: document.getElementById('paymentGraphList').innerHTML
        });

        Vue.component('payment-graph-bubble-wrapper', {
            computed: {
                paymentsList: function () {
                    return this.$root.sortedPayments;
                }
            },
            methods: {
                compStyle: function (payment) {
                    var graphSize = `${Math.round((180 / 100) * payment)}`;
                    var opacity = payment / 100;

                    if (opacity < 0.2) {
                        opacity = 0.2;
                    }

                    if (graphSize < 40) {
                        graphSize = 40;
                    }

                    return {
                        height: graphSize + 'px',
                        width: graphSize + 'px',
                        background: 'rgba(52, 220, 221, ' + opacity + ')',
                        'line-height': graphSize / 2 + 'px',
                        'font-size': graphSize + 'px'
                    };
                }
            },

            template: document.getElementById('paymentGraphBubble').innerHTML
        });

        Vue.component('shipment-graph-list-wrapper', {
            computed: {
                shipmentsList: function () {
                    return this.$root.shipments;
                }
            },
            template: document.getElementById('shipmentGraphList').innerHTML
        });

        Vue.component('devices-graph-list-wrapper', {
            computed: {
                devicesList: function () {
                    return this.$root.devices;
                }
            },
            template: document.getElementById('devicesGraphList').innerHTML
        });

        Vue.component('shipment-graph-square-wrapper', {
            computed: {
                shipmentsList: function () {
                    return this.$root.sortedShipments;
                }
            },
            methods: {
                compStyle: function (shipment) {
                    var graphSize = `${Math.round((180 / 100) * shipment)}`,
                        opacity = shipment / 100;

                    if (opacity < 0.2) {
                        opacity = 0.2;
                    }

                    if (graphSize < 40) {
                        graphSize = 40;
                    }

                    return {
                        height: graphSize + 'px',
                        width: graphSize + 'px',
                        background: 'rgba(52, 220, 221, ' + opacity + ')',
                        'line-height': graphSize / 2 + 'px',
                        'font-size': graphSize + 'px'
                    };
                }
            },

            template: document.getElementById('shipmentGraphSquares').innerHTML
        });

        Vue.component('target-group-bar', {
            props: {
                name: {
                    type: String,
                    required: true
                }
            },

            computed: {
                amount: function () {
                    return this.$root.benchmarkData.local.customers.ages[this.name];
                }
            },

            template: '<div class="graph-gradient" :style="{ width: `${amount}%` }"><span class="data--txt high-contrast">{{ amount }}%</span></div>'
        });
    });
})();