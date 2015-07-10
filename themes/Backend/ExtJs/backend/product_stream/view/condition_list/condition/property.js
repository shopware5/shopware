
Ext.define('Shopware.apps.ProductStream.view.condition_list.condition.Property', {
    extend: 'ProductStream.filter.AbstractCondition',

    getName: function() {
        return 'Shopware\\Bundle\\SearchBundle\\Condition\\PropertyCondition';
    },

    getLabel: function() {
        return 'Property condition';
    },

    isSingleton: function() {
        return true;
    },

    create: function(callback, container, conditions) {
        var me = this;

        me.subApp.getView('condition_list.field.PropertyWindow').create({
            applyCallback: function(group) {
                var field = me.createField(group.get('id'), group.get('name'));
                callback(field);
                me.updateTitle(container, group.get('name'));
            }
        }).show();
    },

    load: function(key, value, container) {
        var me = this;

        if (key.indexOf(this.buildName(value.groupId)) < 0) {
            return;
        }

        var field = this.createField(value.groupId, value.groupName);
        me.updateTitle(container, value.groupName);
        container.fixToggleTool();
        field.setValue(value);
        return field;
    },

    createField: function(groupId, groupName) {
        return Ext.create('Shopware.apps.ProductStream.view.condition_list.field.Property', {
            name: 'condition.' + this.buildName(groupId),
            searchStore: this.createStore(groupId),
            store: this.createStore(groupId),
            groupId: groupId,
            groupName: groupName,
            flex: 1
        });
    },

    buildName: function(groupId) {
        return this.getName() + '|' + groupId;
    },

    createStore: function(groupId) {
        var store = Ext.create('Shopware.store.Search', {
            fields: [
                { name: 'id', type: 'int' },
                { name: 'name', type: 'string', mapping: 'value'}
            ],
            configure: function() {
                return { entity: "Shopware\\Models\\Property\\Value" }
            }
        });
        store.getProxy().extraParams.groupId = groupId;
        return store;
    },


    updateTitle: function(container, name) {
        container.setTitle(this.getLabel() + ': ' + name);
    }
});