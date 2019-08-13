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
//{block name="backend/article/view/variant/configurator/mapping"}
Ext.define('Shopware.apps.Article.view.variant.configurator.Mapping', {
    /**
     * Define that the order main window is an extension of the enlight application window
     * @string
     */
    extend: 'Enlight.app.Window',
    /**
     * Set base css class prefix and module individual css class for css styling
     * @string
     */
    cls: Ext.baseCSSPrefix + 'article-mapping-window',
    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias: 'widget.article-mapping-window',
    /**
     * Set no border for the window
     * @boolean
     */
    border: false,
    /**
     * True to automatically show the component upon creation.
     * @boolean
     */
    autoShow: false,
    /**
     * Set fit layout for the window
     * @string
     */
    layout: 'fit',
    /**
     * Define window width
     * @integer
     */
    width: 400,
    /**
     * A flag which causes the object to attempt to restore the state of internal properties from a saved state on startup.
     */
    stateful: true,

    /**
     * The unique id for this object to use for state management purposes.
     */
    stateId: 'shopware-article-mapping-window',
    footerButton: false,
    minimizable: false,
    maximizable: false,
    modal: true,
    /**
     * Contains all snippets for the component
     * @object
     */
    snippets: {
        title: '{s name=variant/configurator/mapping/title}Take over master data{/s}',
        notice: '{s name=variant/configurator/mapping/notice}In this area you have the option to transfer selectable article information to the selected variant articles. If there is no selected variant article, the selected article information will be applied to all variant articles.{/s}',
        attribute: '{s name=variant/configurator/mapping/attribute}Apply attribute configuration{/s}',
        prices: '{s name=variant/configurator/mapping/prices}Apply price configuration{/s}',
        basePrice: '{s name=variant/configurator/mapping/basePrice}Apply base price configuration{/s}',
        purchasePrice: '{s name=variant/configurator/mapping/purchasePrice}Apply pruchase price configuration{/s}',
        settings: '{s name=variant/configurator/mapping/settings}Apply settings configuration{/s}',
        stock: '{s name=variant/configurator/mapping/stock}Apply stock{/s}',
        translations: '{s name=variant/configurator/mapping/translations}Apply translations{/s}',
        save: '{s name=variant/configurator/mapping/save}Save{/s}',
        cancel: '{s name=variant/configurator/mapping/cancel}Cancel{/s}'
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
        me.title = me.snippets.title;
        me.items = me.createFormPanel();
        me.dockedItems = [ me.createToolbar() ];
        me.callParent(arguments);
        if (me.record) {
            me.formPanel.loadRecord(me.record);
        }

        // Set height when showing the window, since all other height changes are ignored
        me.on('show', function() {
            me.setHeight(375);
        });
    },

    /**
     * Registers additional component events
     */
    registerEvents: function() {
        this.addEvents(
            'acceptBaseData',
            'cancel'
        );
    },

    createFormPanel: function() {
        var me = this;

        me.formPanel = Ext.create('Ext.form.Panel', {
            layout: 'anchor',
            bodyPadding: 10,
            defaults: {
                anchor: '100%',
                labelWidth: 250,
                xtype: 'checkbox',
                checked: true,
                inputValue: true,
                uncheckedValue: false
            },
            items: me.createItems()
        });
        return [ me.formPanel ];
    },

    createItems: function() {
        var me = this;
        var notice = Ext.create('Ext.container.Container', {
            html: me.snippets.notice,
            margin: '0 0 10',
            cls: Ext.baseCSSPrefix + 'global-notice-text'
        });

        return [notice,
        {
            name: 'prices',
            fieldLabel: me.snippets.prices
        } , {
            name: 'basePrice',
            fieldLabel: me.snippets.basePrice
        } , {
            name: 'purchasePrice',
            fieldLabel: me.snippets.purchasePrice
        } , {
            name: 'settings',
            fieldLabel: me.snippets.settings
        } , {
            name: 'stock',
            fieldLabel: me.snippets.stock
        } , {
            name: 'attributes',
            fieldLabel: me.snippets.attribute
        } , {
            name: 'translations',
            fieldLabel: me.snippets.translations
        }];
    },

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
                        me.fireEvent('acceptBaseData', me);
                        //mapping window opened over the detail window?
                        if (me.detailWindow) {
                            me.detailWindow.destroy();
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
