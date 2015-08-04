
Ext.define('Shopware.apps.ProductStream.view.condition_list.condition.VoteAverage', {
    extend: 'ProductStream.filter.AbstractCondition',

    getName: function() {
        return 'Shopware\\Bundle\\SearchBundle\\Condition\\VoteAverageCondition';
    },

    getLabel: function() {
        return 'Vote average condition';
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
        return Ext.create('Shopware.apps.ProductStream.view.condition_list.field.VoteAverage', {
            flex: 1,
            name: 'condition.' + this.getName()
        });
    }
});
