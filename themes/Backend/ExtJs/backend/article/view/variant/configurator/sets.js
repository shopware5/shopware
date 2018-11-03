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
//{block name="backend/article/view/variant/configurator/set"}
Ext.define('Shopware.apps.Article.view.variant.configurator.Sets', {
    /**
     * Define that the order main window is an extension of the enlight application window
     * @string
     */
    extend:'Enlight.app.Window',
    /**
     * Set base css class prefix and module individual css class for css styling
     * @string
     */
    cls:Ext.baseCSSPrefix + 'article-sets-window',
    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.article-sets-window',
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
    /**
     * Set border layout for the window
     * @string
     */
    layout:'fit',
    /**
     * Define window width
     * @integer
     */
    width:300,
    /**
     * Define window height
     * @integer
     */
    height:200,


    /**
     * A flag which causes the object to attempt to restore the state of internal properties from a saved state on startup.
     */
    stateful:true,

    /**
     * The unique id for this object to use for state management purposes.
     */
    stateId:'shopware-article-sets-window',
    footerButton: false,
    minimizable: false,
    maximizable: false,
    modal: true,
    /**
     * Contains all snippets for the component
     * @object
     */
    snippets: {
        saveTitle: '{s name=variant/configurator/sets/save_title}Save configurator set{/s}',
        loadTitle: '{s name=variant/configurator/sets/load_title}Load configurator set{/s}',
        save: '{s name=variant/configurator/sets/save}Save{/s}',
        load: '{s name=variant/configurator/sets/load}Load{/s}',
        sets: '{s name=variant/configurator/sets/set_combo}Configurator set{/s}',
        empty:'{s name=empty}Please select...{/s}',
        cancel: '{s name=variant/configurator/sets/cancel}Cancel{/s}',
        publicField: '{s name=variant/configurator/sets/public_field}Mark the set as public{/s}',
        setName: '{s name=variant/configurator/set/set_name}Name of the configurator set{/s}'
    },

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
        if (me.mode === 'save') {
            me.items = me.createSaveItems();
            me.title = me.snippets.saveTitle;
        } else {
            me.items = me.createLoadItems();
            me.title = me.snippets.loadTitle;
        }
        me.dockedItems = [ me.createToolbar() ];
        me.callParent(arguments);

        if (me.mode === 'save') {
            me.formPanel.loadRecord(me.configuratorSet);
        }
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
             * @param [Ext.data.Model] The article record.
             * @param [object] The set window
             */
            'saveSet',
            /**
             * Event will be fired when the user clicks the save button.
             *
             * @event
             * @param [Ext.data.Model] The article record.
             * @param [object] The set window
             */
            'loadSet',
            /**
             * Event will be fired when the user clicks the cancel button.
             *
             * @event
             * @param [object] The detail window
             */
            'cancel'
        );
    },

    /**
     * Creates the form panel for the save window.
     * @return
     */
    createSaveItems: function() {
        var me = this;

        var publicCheckBox = Ext.create('Ext.form.field.Checkbox', {
            name: 'public',
            inputValue: true,
            uncheckedValue:false,
            fieldLabel: me.snippets.publicField
        });
        var nameField = Ext.create('Ext.form.field.Text', {
            name: 'name',
            allowBlank: false,
            fieldLabel: me.snippets.setName
        });

        me.formPanel = Ext.create('Ext.form.Panel', {
            layout: 'anchor',
            bodyPadding: 10,
            defaults: {
                anchor: '100%'
            },
            items: [ nameField, publicCheckBox ]
        });

        return [ me.formPanel ];
    },

    /**
     * Creates the form panel with the set combo box for the load window.
     */
    createLoadItems: function() {
        var me = this;

        me.setComboStore = Ext.create('Shopware.apps.Article.store.ConfiguratorSet');
        me.setComboStore.getProxy().extraParams.setId = me.configuratorSet.get('id');

        var setComboBox = Ext.create('Ext.form.field.ComboBox', {
            name: 'configuratorSet',
            queryMode: 'local',
            valueField: 'id',
            editable: false,
            displayField: 'name',
            store: me.setComboStore.load(),
            emptyText: me.snippets.empty,
            fieldLabel: me.snippets.sets,
            listeners: {
                change: function(field, newValue, oldValue) {
                    var changedSet = me.setComboStore.getById(newValue);
                    if (changedSet instanceof Ext.data.Model) {
                        me.configuratorSet = changedSet;
                    } else {
                        field.setValue(oldValue);
                    }
                }
            }
        });

        me.formPanel = Ext.create('Ext.form.Panel', {
            layout: 'anchor',
            bodyPadding: 10,
            defaults: {
                anchor: '100%'
            },
            items: [ setComboBox ]
        });
        return [me.formPanel];
    },

    /**
     * Creates the toolbar for the save mode.
     * @return
     */
    createToolbar: function() {
        var me = this,
            text = me.snippets.save;

        if (me.mode === 'load') {
            text = me.snippets.load;
        }

        return Ext.create('Ext.toolbar.Toolbar', {
            dock: 'bottom',
            items: [
                { xtype: 'tbfill' },
                {
                    xtype: 'button',
                    cls:'primary',
                    text: text,
                    handler: function() {
                        if (me.mode === 'save') {
                            me.formPanel.getForm().updateRecord(me.configuratorSet);
                            me.fireEvent('saveSet', me.configuratorSet, me);
                        } else {
                            me.fireEvent('loadSet', me.configuratorSet, me);
                        }
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
