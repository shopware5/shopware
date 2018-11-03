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
 * Shopware UI - Article detail window.
 *
 * @link http://www.shopware.de/
 * @license http://www.shopware.de/license
 * @package Article
 * @subpackage Detail
 */
//{namespace name=backend/article/view/main}
//{block name="backend/article/view/variant/configurator/option_edit"}
Ext.define('Shopware.apps.Article.view.variant.configurator.OptionEdit', {
    /**
     * Define that the order main window is an extension of the enlight application window
     * @string
     */
    extend:'Enlight.app.Window',
    /**
     * Set base css class prefix and module individual css class for css styling
     * @string
     */
    cls:Ext.baseCSSPrefix + 'article-option-window',
    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.article-option-window',
    /**
     * Set no border for the window
     * @boolean
     */
    border:false,
    /**
     * True to automatically show the component upon creation.
     * @boolean
     */
    autoShow:false,

    width: 940,
    modal: true,
    stateful: true,
    autoScroll: true,
    layout: 'fit',
    footerButton: false,
    stateId:'shopware-article-option-window',

    /**
     * Contains all snippets for the component
     * @object
     */
    snippets: {
        title: '{s name=variant/configurator/option_edit/save_title}Edit option:{/s}',
        save: '{s name=variant/configurator/sets/save}Save{/s}',
        cancel: '{s name=variant/configurator/sets/cancel}Cancel{/s}',
        nameField: '{s name=variant/configurator/option_edit/name_field}Option name{/s}'
    },

    attributeTable: 's_article_configurator_options_attributes',

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
        me.registerEvents();
        me.items = me.createItems();
        me.title = me.snippets.title;
        me.dockedItems = [ me.createToolbar() ];
        me.callParent(arguments);
        me.formPanel.loadRecord(me.record);
    },

    /**
     * Registers additional component events.
     */
    registerEvents: function() {
        this.addEvents(
            /**
             * Event will be fired when the user clicks the save button.
             *
             * @event
             * @param [Ext.data.Model] The option record.
             * @param [object] This component
             */
            'saveOption',
            /**
             * Event will be fired when the user clicks the cancel button.
             *
             * @event
             * @param [object] This component
             */
            'cancel'
        );
    },

    /**
     * Creates the form panel for the edit window.
     * @return
     */
    createItems: function() {
        var me = this;

        var nameField = Ext.create('Ext.form.field.Text', {
            name: 'name',
            allowBlank: false,
            translatable: true,
            anchor: '100%',
            fieldLabel: me.snippets.nameField
        });

        me.mediaField = Ext.create('Shopware.form.field.Media', {
            name: 'mediaId',
            fieldLabel: 'Filter image'
        });

        me.formPanel = Ext.create('Ext.form.Panel', {
            layout: 'anchor',
            bodyPadding: 20,
            autoScroll: true,
            items: [ nameField, me.mediaField ],
            plugins: [{
                ptype: 'translation',
                translationType: 'configuratoroption'
            }]
        });

        me.attributeForm = Ext.create('Shopware.attribute.Form', {
            table: me.attributeTable,
            allowTranslation: false,
            translationForm: me.formPanel,
            margin: '20 0 0'
        });

        if (me.record) {
            me.attributeForm.loadAttribute(me.record.get('id'), function () {
                me.attributeForm.setHeight(me.attributeForm.fieldSet.getHeight());
            });
        }

        me.formPanel.add(me.attributeForm);

        return [ me.formPanel ];
    },

    /**
     * Creates the toolbar for the window.
     * @return
     */
    createToolbar: function() {
        var me = this;

        return Ext.create('Ext.toolbar.Toolbar', {
            dock: 'bottom',
            items: [
                { xtype: 'tbfill' },
                {
                    xtype: 'button',
                    cls:'primary',
                    text: me.snippets.save,
                    handler: function() {
                        me.fireEvent('saveOption', me.record, me.formPanel, me);
                    }
                },
                {
                    xtype: 'button',
                    text: me.snippets.cancel,
                    cls: 'secondary',
                    handler: function() {
                        me.fireEvent('cancel', me);
                    }
                }
            ]
        });
    }

});
//{/block}
