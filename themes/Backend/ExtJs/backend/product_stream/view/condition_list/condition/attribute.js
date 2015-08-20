
Ext.define('Shopware.apps.ProductStream.view.condition_list.condition.Attribute', {
    extend: 'ProductStream.filter.AbstractCondition',

    getName: function() {
        return 'Shopware\\Bundle\\SearchBundle\\Condition\\ProductAttributeCondition';
    },

    getLabel: function() {
        return 'Attribute condition';
    },

    isSingleton: function() {
        return true;
    },

    create: function(callback, container, conditions) {
        var me = this;

        me.subApp.getView('condition_list.field.AttributeWindow').create({
            applyCallback: function(attribute) {
                var field = me.createField(attribute);
                callback(field);
                me.updateTitle(container, attribute);
            }
        }).show();
    },

    load: function(key, value, container) {
        if (key.indexOf(this.getName()) < 0) {
            return;
        }

        var field = this.createField(value.field);
        field.setValue(value);

        this.updateTitle(container, value.field);
        container.fixToggleTool();

        return field;
    },

    createField: function(attribute) {
        return Ext.create('Shopware.apps.ProductStream.view.condition_list.field.Attribute', {
            name: 'condition.' + this.getName() + '|' + attribute,
            attributeField: attribute
        });
    },

    updateTitle: function(container, name) {
        container.setTitle(this.getLabel() + ': ' + name);
    }
});
