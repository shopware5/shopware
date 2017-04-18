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
 */

//{namespace name=backend/article_list/main}
//{block name="backend/article_list/view/add_filter/window"}
Ext.define('Shopware.apps.ArticleList.view.AddFilter.Window', {
    /**
     * Define that the plugin manager main window is an extension of the enlight application window
     * @string
     */
    extend: 'Enlight.app.Window',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias: 'widget.multi-edit-add-filter-window',

    /**
     * Set no border for the window
     * @boolean
     */
    border: false,

    /**
     * True to automatically show the component upon creation.
     * @boolean
     */
    autoShow: true,

    /**
     * Set border layout for the window
     * @string
     */

    /**
     * Define window width
     * @integer
     */
    width: 700,

    /**
     * Define window height
     * @integer
     */
    height: 500,

    /**
     * True to display the 'maximize' tool button and allow the user to maximize the window, false to hide the button and disallow maximizing the window.
     * @boolean
     */
    maximizable: true,

    /**
     * True to display the 'minimize' tool button and allow the user to minimize the window, false to hide the button and disallow minimizing the window.
     * @boolean
     */
    minimizable: true,

    /**
     * A flag which causes the object to attempt to restore the state of internal properties from a saved state on startup.
     */
    stateful: true,

    /**
     * The unique id for this object to use for state management purposes.
     */
    stateId: 'shopware-multi-edit-add-filter-window',

    /**
     * Title of the window.
     * @string
     */
    title: '{s name=addFilter/windowTitle}Add/Edit Filter{/s}',

    resizable: true,

    /**
     * Set the window's layout to "fit"
     */
    layout: 'fit',

    /**
     * Define (default) borders
     */
    bodyPadding: 10,
    defaults: {
        border: false,
        bodyBorder: 0
    },

    /**
     * Fix background color
     */
    bodyStyle: 'background-color: #f0f2f4;',

    /**
     * Initializes the component.
     *
     * @public
     * @return void
     */
    initComponent: function () {
        var me = this;


        me.dockedItems = [{
            xtype: 'toolbar',
            dock: 'bottom',
            ui: 'shopware-ui',
            cls: 'shopware-toolbar',
            items: me.getFormButtons()
        }];

        me.items = me.getItems();

        me.addEvents(
            /**
             * Fires after the user clicked a tab
             */
            'tabChanged'
        );

        me.callParent(arguments);
    },

    getItems: function() {
        var me = this;


        return [{
            xtype: 'form',
            layout: {
                type: 'vbox',
                pack: 'start',
                align: 'stretch'
            },

            items: [
                me.getSaveFieldSet(),
                me.getTabPanel(),
            ]
        }];
    },

    getSaveFieldSet: function() {
        var me = this;

        return {
            xtype: 'fieldset',
            title: '{s name=addFilter/saveTitle}Save{/s}',
            margin: 5,
            items: [
                {
                    xtype: 'label',
                    text: '{s name=addFilter/labelDesc}In order to save the filter above, enter a name and optionally a description{/s}',
                    style: {
                        display: 'block'
                    },
                    margin: '0 0 18px'
                },
                {
                    xtype: 'textfield',
                    name: 'name',
                    fieldLabel: '{s name=addFilter/saveName}Filter name{/s}',
                    anchor: '100%',
                    allowBlank: false
                },
                {
                    xtype: 'textarea',
                    name: 'description',
                    fieldLabel: '{s name=addFilter/saveDescription}Your description{/s}',
                    anchor: '100%'
                }
            ]
        };
    },


    /**
     * Creates the save and cancel button for the form panel.
     *
     * @return [array] - Contains the cancel button and the save button
     */
    getFormButtons: function() {
        var me = this,
            buttons = [ '->' ];


        var cancelButton = Ext.create('Ext.button.Button', {
            text: '{s name=cancel}Cancel{/s}',
            scope: me,
            cls: 'secondary',
            handler:function () {
                me.down('form').getForm().reset();
                me.down('grid').getStore().removeAll();
                // Make sure, that the dropdown is hidden, when the window is closed
                me.down('filterString').collapse();
                me.hide();
            }
        });
        buttons.push(cancelButton);

        var saveButton = Ext.create('Ext.button.Button', {
            action: 'saveAdvanced',
            cls: 'primary',
            text: '{s name=addFilter/saveButton}Save{/s}'
        });

        buttons.push(saveButton);

        return buttons;
    },

    getTabPanel: function()  {
        var me = this;

        return Ext.create('Ext.tab.Panel', {
            name: 'filter-tab-panel',
            flex: 1,
            plain: true,
            items: [{
                xtype: 'query-field',
                internalTitle: 'extended',
                title: '{s name=addFilter/advancedTitle}Advanced{/s}',
                tabConfig: {
                    tooltip: '{s name=addFilter/advancedTooltip}In advanced mode, you can define and combine your conditions very free{/s}'
                }
            },{
                xtype: 'multi-edit-add-filter-grid',
                internalTitle: 'simple',
                filterableColumns: me.filterableColumns,
                columnStore: me.columnStore,
                operatorStore: me.operatorStore,
                title: '{s name=addFilter/simpleTitle}Simple{/s}',
                border: false,
                tabConfig: {
                    tooltip: '{s name=addFilter/simpleTooltip}In simple mode, all conditions are conjuncted with AND{/s}'
                }
            }],
            listeners: {
                tabchange: function(tabPanel, newCard, oldCard, eOpts) {
                    me.fireEvent('tabChanged', tabPanel, newCard, oldCard);
                }
            }
        });
    }
});
//{/block}
