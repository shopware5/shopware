
Ext.define('Shopware.apps.ProductStream.view.condition_list.condition.CreateDate', {
    extend: 'ProductStream.filter.AbstractCondition',

    getName: function() {
        return 'Shopware\\Bundle\\SearchBundle\\Condition\\CreateDateCondition';
    },

    getLabel: function() {
        return 'Create date condition';
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
        return Ext.create('Shopware.apps.ProductStream.view.condition_list.field.CreateDate', {
            name: 'condition.' + this.getName()
        });
    }
});