
Ext.define('Shopware.apps.ProductStream.view.condition_list.condition.Sales', {
    extend: 'ProductStream.filter.AbstractCondition',

    getName: function() {
        return 'Shopware\\Bundle\\SearchBundle\\Condition\\SalesCondition';
    },

    getLabel: function() {
        return 'Minimum Sales';
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
        var field = this.createField();
        field.setValue(value);
        return field;
    },

    createField: function() {
        return Ext.create('Shopware.apps.ProductStream.view.condition_list.field.Sales', {
            flex: 1,
            name: 'condition.' + this.getName()
        });
    }
});
