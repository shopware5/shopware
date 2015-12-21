//{block name="swag_html_code_widget/backend/emotion_component"}
Ext.define('Shopware.apps.Emotion.view.components.HtmlCodeComponent', {
    extend: 'Shopware.apps.Emotion.view.components.Base',
    alias: 'widget.emotion-html-code',

    initComponent: function() {
        var me = this;

        me.callParent(arguments);

        me.elementFieldset.items.each(function(item) {
            item.config.mode = item.name;
            item.height = 350;
        });
    }
});
//{/block}