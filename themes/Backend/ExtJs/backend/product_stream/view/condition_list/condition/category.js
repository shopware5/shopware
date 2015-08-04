
Ext.define('Shopware.apps.ProductStream.view.condition_list.condition.Category', {
    extend: 'ProductStream.filter.AbstractCondition',

    getName: function() {
        return 'Shopware\\Bundle\\SearchBundle\\Condition\\CategoryCondition';
    },

    getLabel: function() {
        return 'Category condition';
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
        return Ext.create('Shopware.apps.ProductStream.view.condition_list.field.Grid', {
            name: 'condition.' + this.getName(),
            searchStore: this.createStore(),
            store: this.createStore(),
            idsName: 'categoryIds',
            flex: 1,
            getErrorMessage: function() {
                return 'No category selected';
            }
        });
    },

    createStore: function() {
        return Ext.create('Shopware.store.Search', {
            fields: ['id', 'name'],
            configure: function() {
                return { entity: "Shopware\\Models\\Category\\Category" }
            }
        });
    }

});