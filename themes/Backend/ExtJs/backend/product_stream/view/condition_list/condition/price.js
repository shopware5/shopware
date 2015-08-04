
Ext.define('Shopware.apps.ProductStream.view.condition_list.condition.Price', {
    extend: 'ProductStream.filter.AbstractCondition',

    getName: function() {
        return 'Shopware\\Bundle\\SearchBundle\\Condition\\PriceCondition';
    },

    getLabel: function() {
        return 'Price condition';
    },

    isSingleton: function() {
        return true;
    },

    create: function(callback) {
        callback(this.createField());
    },

    load: function(key, value) {
        if (key !== this.getName()) {
            return null;
        }

        var field = this.createField();
        field.setValue(value);
        return field;
    },

    createField: function() {
        return Ext.create('Shopware.apps.ProductStream.view.condition_list.field.Price', {
            name: 'condition.'+ this.getName(),
            flex: 1
        });
    }
});