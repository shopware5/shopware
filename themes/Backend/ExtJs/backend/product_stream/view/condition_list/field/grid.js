
Ext.define('Shopware.apps.ProductStream.view.condition_list.field.Grid', {
    extend: 'Shopware.apps.ProductStream.view.SearchGrid',
    alias: 'widget.product-stream-field-grid',
    mixins: [ 'Ext.form.field.Base' ],
    minHeight: 90,
    maxHeight: 150,
    allowBlank: false,
    validateOnChange: false,
    idsName: 'ids',

    validate: function() {
        if (this.allowBlank) {
            return true;
        }
        var ids = this.getSelectedIds();
        var valid = !Ext.isEmpty(ids);

        if (!valid) {
            Shopware.Notification.createGrowlMessage('Validation', this.getErrorMessage());
        }

        return valid;
    },

    getErrorMessage: function() {
        return 'Grid is empty';
    },

    createPagingBar: function() {
        return null;
    },

    getValue: function() {
        return this.getSelectedIds();
    },

    setValue: function(value) {
        var me = this;

        me.store.load({
            params: { ids: Ext.JSON.encode(value[me.idsName]) }
        })
    },

    getSubmitData: function() {
        var value = {};

        value[this.name] = { };
        value[this.name][this.idsName] = this.getSelectedIds();
        return value;
    },

    getSelectedIds: function() {
        var ids = [], me = this;

        me.store.each(function(item) {
            ids.push(item.get('id'));
        });
        return ids;
    }
});