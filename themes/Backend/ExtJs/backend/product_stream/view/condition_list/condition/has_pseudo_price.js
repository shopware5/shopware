
Ext.define('Shopware.apps.ProductStream.view.condition_list.condition.HasPseudoPrice', {
    extend: 'ProductStream.filter.AbstractCondition',

    getName: function() {
        return 'Shopware\\Bundle\\SearchBundle\\Condition\\HasPseudoPriceCondition';
    },

    getLabel: function() {
        return 'Has pseudo price condition';
    },

    isSingleton: function() {
        return true;
    },

    create: function(callback) {
        callback(this.createField());
    },

    load: function(key, value) {
        if (key !== this.getName()) {
            return;
        }
        return this.createField();
    },

    createField: function() {
        var me = this;

        return Ext.create('Ext.container.Container', {
            getName: function() {
                return 'condition.' + me.getName();
            },
            items: [{
                xtype: 'displayfield',
                value: 'Es werden nur Produkte angezeigt, welche im Listing einen Pseudopreis angezeigt bekommen',
            }, {
                xtype: 'numberfield',
                name: 'condition.' + this.getName(),
                hidden: true,
                value: 1
            }]
        });
    }
});
