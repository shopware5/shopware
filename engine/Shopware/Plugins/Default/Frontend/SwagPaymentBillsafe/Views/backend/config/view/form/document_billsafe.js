//{namespace name="backend/config/view/document"}
//{block name="backend/config/view/form/document" append}
Ext.define('Shopware.apps.Config.view.form.Document-SwagPaymentBillsafe', {

    /**
     * Defines an override applied to a class.
     * @string
     */
    override: 'Shopware.apps.Config.view.form.Document',

    /**
     * List of classes that have to be loaded before instantiating this class.
     * @array
     */
    requires: [ 'Shopware.apps.Config.view.form.Document' ],

    alias: 'widget.config-form-document-billsafe',

    /**
     * Initializes the class override to provide additional functionality
     * like a new full page preview.
     *
     * @public
     * @return void
     */
    initComponent: function() {
        var me = this;
        me.callOverridden(arguments);
    },

    /**
     * Overrides the getFormItems method and appends the billsafe form items
     * @return
     */
    getFormItems: function() {
        var me = this,
            data = me.callOverridden(arguments);

        data[1].items.push({
            xtype: 'tinymce',
            fieldLabel: '{s name=document/detail/content_footer_label}Footer-Content{/s}',
            labelWidth: 100,
            name: 'Billsafe_Footer_Value',
            hidden: true,
            translatable: true
            }, {
            xtype: 'textarea',
            fieldLabel: '{s name=document/detail/style_footer_label}Footer-Style{/s}',
            labelWidth: 100,
            name: 'Billsafe_Footer_Style',
            hidden: true,
            translatable: true
        },{
            xtype: 'tinymce',
            fieldLabel: '{s name=document/detail/content_content_info_label}Content-Info-Content{/s}',
            labelWidth: 100,
            name: 'Billsafe_Content_Info_Value',
            hidden: true,
            translatable: true
            }, {
            xtype: 'textarea',
            fieldLabel: '{s name=document/detail/style_content_info_label}Content-Info-Style{/s}',
            labelWidth: 100,
            name: 'Billsafe_Content_Info_Style',
            hidden: true,
            translatable: true
        });

        return data;

    }


});
//{/block}