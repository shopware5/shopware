
//{namespace name="backend/benchmark/main"}
//{block name="backend/benchmark/view/settings/industry_field"}
Ext.define('Shopware.apps.Benchmark.view.settings.IndustryField', {
    extend: 'Ext.form.FieldContainer',
    alias: 'widget.industryfield',

    mixins: {
        field: 'Ext.form.field.Field'
    },

    /**
     * @type Ext.data.Store
     */
    store: null,

    initComponent: function () {
        var me = this;

        if (!me.store || !me.store instanceof Ext.data.Store) {
            throw new Error('The industry field requires a store');
        }

        me.tpl = me.createFieldTemplate();
        me.data = {};
        /*{if {acl_is_allowed privilege=manage}}*/
        me.listeners = {
            click: function() {
                me.fireEvent('changeIndustry');
            },
            element: 'el',
            delegate: 'span#other-action'
        };
        /*{/if}*/

        me.callParent(arguments);
    },

    /**
     * @param { integer } value
     */
    setValue: function (value) {
        var foundIndex = this.store.findExact('id', value);
        if (foundIndex === -1) {
            this.update({ value: value });
            return;
        }

        this.update({ value: this.store.getAt(foundIndex).get('name') });
    },

    /**
     * @returns { Ext.XTemplate }
     */
    createFieldTemplate: function () {
        return new Ext.XTemplate(
            '<div>',
                '<div class="value"><b>{ value }</b></div>',
                '<div>',
                    /*{if {acl_is_allowed privilege=manage}}*/
                    '<span id="other-action" style="text-decoration: underline; font-style: italic; cursor: pointer; display: block; margin-top: 8px; font-size: 10px;">',
                        '{s name="settings/industry_window/wrong"}Wrong industry?{/s}',
                    '</span>',
                    /*{/if}*/
                '</div>',
            '</div>'
        );
    }
});
//{/block}
