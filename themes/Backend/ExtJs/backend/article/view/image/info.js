/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 *
 * @category   Shopware
 * @package    Article
 * @subpackage Detail
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware UI - Article detail page - Image
 * The image info component displayed on the right side in the image tab.
 * It contains a image preview to display the original image of the selected list item.
 * Additional it contains a tree panel for the assigned article components like variants or similar.
 * All events of the component handled in the media controller.
 */
//{namespace name=backend/article/view/main}
//{block name="backend/article/view/image/info"}
Ext.define('Shopware.apps.Article.view.image.Info', {
    /**
     * Define that the category drop zone is an extension of the Ext.panel.Panel
     * @string
     */
    extend:'Ext.form.Panel',
    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.article-image-info',
    /**
     * Set css class for this component
     * @string
     */
    cls: Ext.baseCSSPrefix + 'article-image-info',

    /**
     * Layout for the component
     */
    layout: {
        type: 'vbox',
        align: 'stretch'
    },

    autoScroll:true,
    /**
     * Defaults for the panel items
     * @object
     */
    defaults: {
        anchor: '100%'
    },

    /**
     * Contains all snippets for the view component
     * @object
     */
    snippets:{
        title: '{s name=image/info/title}Image options{/s}',
        download: '{s name=image/info/download}Download{/s}',
        imageTitle: '{s name=image/info/image_title}Title{/s}',
        settings: '{s name=image/info/settings}Settings{/s}',
        treeTitle: '{s name=image/info/tree}Assignments{/s}',
        translateTitle: '{s name=image/info/translate_title}Translate title{/s}',

        attribute1: '{s name=image/info/attribute1}attribute1{/s}',
        attribute2: '{s name=image/info/attribute2}attribute2{/s}',
        attribute3: '{s name=image/info/attribute3}attribute3{/s}'
    },

    /**
     * Sets the body padding value
     * @integer
     */
    bodyPadding: 10,

    /**
	 * The initComponent template method is an important initialization step for a Component.
     * It is intended to be implemented by each subclass of Ext.Component to provide any needed constructor logic.
     * The initComponent method of the class being created is called first,
     * with each initComponent method up the hierarchy to Ext.Component being called thereafter.
     * This makes it easy to implement and, if needed, override the constructor logic of the Component at any step in the hierarchy.
     * The initComponent method must contain a call to callParent in order to ensure that the parent class' initComponent method is also called.
	 *
	 * @return void
	 */
    initComponent:function () {
        var me = this;
        me.title = me.snippets.title;
        me.thumbnail = me.createThumbnail();
        me.settings = me.createSettings();
        me.items = [
            me.thumbnail,
            me.settings
        ];
        me.registerEvents();
        me.callParent(arguments);
    },

    /**
     * Registers additional component events.
     */
    registerEvents: function() {
    	this.addEvents(
    		/**
    		 * Event will be fired when the user want to download the original image
    		 *
    		 * @event
    		 * @param [Ext.data.Model] The media record
    		 */
    		'download',

            /**
             * Event will be fired when the user wants to translate the settings.
             *
             * @event
             * @param [object] form - Ext.form.Panel - The settings Panel
             * @param [object] record - Shopware.apps.Article.model.Media
             */
            'translateSettings',

            /**
             * Event will be fired when the user wants to save the image settings.
             * @event
             * @param [object] form - Ext.form.Panel - The settings Panel
             * @param [object] record - Shopware.apps.Article.model.Media
             */
            'saveImageSettings'
    	);
    },

    /**
     * Creates the thumbnail container for the info panel.
     * @return Ext.view.View
     */
    createThumbnail: function() {
        var me = this;

        return Ext.create('Ext.view.View', {
            cls: 'outer-article-info-thumbnail',
            emptyText: 'No additional information found',
            tpl: me.createInfoPanelTemplate(),
            margin: '0 0 10',
            itemSelector: '.copy-image-path',
            autoScroll:true,
            flex: 1,
            renderData: []
        });
    },



    /**
     * Creates the XTemplate for the information panel
     *
     * Note that the template has different member methods
     * which are only callable in the actual template.
     *
     * @return [object] generated Ext.XTemplate
     */
    createInfoPanelTemplate: function() {
        return new Ext.XTemplate(
            '<tpl for=".">',
                '<div class="article-thumb-wrap">',
                    // If the type is image, then show the image
                    '<div class="thumb">',
                        '<div class="inner-thumb"><img src="',
                            '<tpl if="original">',
                                '{literal}{original}{/literal}',
                            '</tpl>',
                        '" /></div>',
                    '</div>',
                '</div>',
            '</tpl>',
            '<div class="x-clear"></div>'
        );
    },

    /**
     * Creates the field set for the image settings.
     * Contains the title text field and the download button.
     *
     * @return Ext.form.FieldSet
     */
    createSettings: function() {
        var me = this;

        var changeHandler = function() {
            if (me.record) {
                me.fireEvent('saveImageSettings', me.settingsForm, me.record);
            }
        }

        me.titleField = Ext.create('Ext.form.field.Text', {
            name: 'description',
            anchor: '100%',
            fieldLabel: me.snippets.imageTitle,
            translatable: true,
            listeners: {
                blur: {
                    fn: changeHandler
                }
            }
        });

        me.attr1Field = Ext.create('Ext.form.field.Text', {
            name: 'attribute[attribute1]',
            anchor: '100%',
            fieldLabel: me.snippets.attribute1,
            listeners: {
                blur: {
                    fn: changeHandler
                }
            }
        });

        me.attr2Field = Ext.create('Ext.form.field.Text', {
            name: 'attribute[attribute2]',
            anchor: '100%',
            fieldLabel: me.snippets.attribute2,
            listeners: {
                blur: {
                    fn: changeHandler
                }
            }
        });

        me.attr3Field = Ext.create('Ext.form.field.Text', {
            name: 'attribute[attribute3]',
            anchor: '100%',
            fieldLabel: me.snippets.attribute3,
            listeners: {
                blur: {
                    fn: changeHandler
                }
            }
        });

        me.settingToolbar = Ext.create('Ext.toolbar.Toolbar', {
            dock: 'bottom',
            margin: '8 0 0',
            items: [
                '->', {
                    xtype: 'button',
                    text: me.snippets.translateTitle,
                    cls: 'small secondary',
                    handler: function() {
                        if (me.record) {
                            me.fireEvent('translateSettings', me.settingsForm, me.record);
                        }
                    }
                }, {
                    xtype: 'button',
                    text: me.snippets.download,
                    anchor: '50%',
                    cls: 'small secondary',
                    handler: function() {
                        me.fireEvent('download', me.record);
                    }
                }
            ]
        });

        me.settingsForm = Ext.create('Ext.form.Panel', {
            title: me.snippets.settings,
            layout: 'anchor',
            flex:1,
            autoScroll:true,
            plugins: [{
                ptype: 'translation',
                pluginId: 'translation',
                translationType: 'articleimage',
                translationMerge: false,
                translationKey: null,
                translationName: 'attributes'
            }],
            defaults: {
                labelWidth: 90,
                anchor: '100%'
            },
            bodyPadding: 10,
            cls: Ext.baseCSSPrefix + 'media-info-form-panel',
            dockedItems: [ me.settingToolbar ],
            items: [
                me.titleField,
                me.attr1Field,
                me.attr2Field,
                me.attr3Field
            ]
        });
        return me.settingsForm;
    }
});
//{/block}




