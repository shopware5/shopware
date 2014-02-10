
Ext.define('Shopware.apps.Theme', {
    extend: 'Enlight.app.SubApplication',

    name:'Shopware.apps.Theme',

    loadPath: '{url action=load}',
    bulkLoad: true,

    controllers: [ 'List', 'Detail' ],

    views: [
        'list.Window',
        'list.Theme',
        'list.extensions.Info',

        'detail.Theme',
        'detail.Window',

        'detail.elements.ArticleSelection',
        'detail.elements.CategorySelection',
        'detail.elements.CheckboxField',
        'detail.elements.ColorPicker',
        'detail.elements.DateField',
        'detail.elements.EmField',
        'detail.elements.MediaSelection',
        'detail.elements.PercentField',
        'detail.elements.SnippetSelection',
        'detail.elements.TextAreaField',
        'detail.elements.TextField'
    ],

    models: [ 'Theme', 'Element', 'ConfigValue' ],
    stores: [ 'Theme' ],

    launch: function() {
        return this.getController('List').mainWindow;
    }
});