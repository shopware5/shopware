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
 * @subpackage Esd
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware UI - Article crosselling page
 */
//{namespace name=backend/article/view/main}
//{block name="backend/article/view/crossseling/base"}
Ext.define('Shopware.apps.Article.view.crossselling.Base', {

    /**
     * Extend from the standard ExtJS 4
     * @string
     */
    extend: 'Ext.form.FieldSet',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias: 'widget.article-crossselling-base',

    /**
     * Set css class
     * @string
     */
    cls: Ext.baseCSSPrefix + 'article-crossselling-base',

    /**
     * Padding of the body element of the component
     * @number
     */
    bodyPadding: 10,

    /**
     * Layout type of the component.
     * @string
     */
    layout: 'column',

    /**
     * Default snippets for the component. The snippets will be merged with the
     * provided snippets at initialization of the component.
     * @object
     */
    systemTexts: {
        'productSearch':'{s name=detail/sidebar/similar/article_search}Article{/s}',
        'crossField': '{s name=detail/sidebar/similar/assignment_field}Assignment{/s}',
        'crossBox': '{s name=detail/sidebar/similar/assignment_box}Assign each other{/s}',
        'delete': '{s name=detail/sidebar/similar/delete}Remove entry{/s}',
        'name': '{s name=detail/sidebar/similar/name}Article name{/s}'
    },

    /**
     * Additional events which needs to be registered.
     * @object
     */
    customEvents: {
        addEvent: '',
        removeEvent: ''
    },

    /**
     * The initComponent template method is an important initialization step for a Component.
     * It is intended to be implemented by each subclass of Ext.Component to provide any needed constructor logic.
     * The initComponent method of the class being created is called first,
     * with each initComponent method up the hierarchy to Ext.Component being called thereafter.
     * This makes it easy to implement and, if needed, override the constructor logic of the Component at any step in the hierarchy.
     * The initComponent method must contain a call to callParent in order to ensure that the parent class' initComponent method is also called.
     *
     * @returns void
     */
    initComponent: function() {
        var me = this;

        me.systemTexts = Ext.apply({ }, me.systemTexts, me.snippets);
        me.title = me.systemTexts.title;

        me.registerAdditionalEvents();

        me.items = [ me.createFormElements(), me.createGridPanel() ];

        me.callParent(arguments);
    },

    /**
     * Registers additional events for the component based on { @link this.customEvents }.
     *
     * @returns void
     */
    registerAdditionalEvents: function() {
        var me = this,
            events = {};

        Ext.iterate(me.customEvents, function(key, value) {
            events[value] = true;
        });

        me.addEvents(events);
    },

    /**
     * Creates the form panel and the necessary fields for the component
     * @returns { Ext.form.Panel }
     */
    createFormElements: function() {
        var me = this;

        return me.form = Ext.create('Ext.form.Panel', {
            margin: '0 20 0 0',
            layout: 'anchor',
            border: false,
            columnWidth: 0.35,
            defaults: {
                labelWidth: 155,
                anchor: '100%'
            },
            items: [
                me.createNoticeContainer(),
                me.createProductSearch(),
                me.createCheckbox(),
                me.createAddButton()
            ]
        });
    },

    /**
     * Creates a special product search field for the form of the component.
     *
     * @returns { Shopware.form.field.ArticleSearch }
     */
    createProductSearch: function() {
        var me = this;

        return me.productSearch = Ext.create('Shopware.form.field.ArticleSearch', {
            name: 'number',
            fieldLabel: me.systemTexts.productSearch,
            returnValue: 'name',
            hiddenReturnValue: 'number',
            articleStore: Ext.create('Shopware.store.Article'),
            width: '100%',
            anchor: '100%',
            formFieldConfig: {
                labelWidth: 155
            },
            allowBlank: false,
            getValue: function() {
                return this.getSearchField().getValue();
            },
            setValue: function(value) {
                this.getSearchField().setValue(value);
            }
        });
    },

    /**
     * Creates the cross assignment checkbox for the form of the component.
     *
     * @returns { Ext.form.field.Checkbox }
     */
    createCheckbox: function() {
        var me = this;

        return Ext.create('Ext.form.field.Checkbox', {
            name: 'cross',
            labelWidth: 155,
            fieldLabel: me.systemTexts.crossField,
            boxLabel: me.systemTexts.crossBox,
            inputValue: true,
            uncheckedValue: false
        });
    },

    /**
     * Creates the add button to the form of the component. The button adds the provided
     * data as a new model to the grid panel.
     *
     * @returns { Ext.button.Button }
     */
    createAddButton: function() {
        var me = this;

        return Ext.create('Ext.button.Button', {
            cls: 'small primary',
            text: me.systemTexts.add,
            anchor: 'auto',
            margin: '0 0 0 160',
            handler: function() {
                me.fireEvent(me.customEvents.addEvent, me.form, me.productGrid, me.productSearch);
            }
        });
    },

    /**
     * Creates the grid panel which displays the provided data of the component.
     *
     * @returns { Ext.grid.Panel }
     */
    createGridPanel: function() {
        var me = this;

        return me.productGrid = Ext.create('Ext.grid.Panel', {
            title: me.systemTexts.gridTitle,
            cls: Ext.baseCSSPrefix + 'free-standing-grid',
            store: me.gridStore,
            name: me.listingName,
            height: 180,
            columnWidth: 0.65,
            columns: [
                {
                    header: me.systemTexts.productSearch,
                    dataIndex: 'number',
                    width: 120
                }, {
                    header: me.systemTexts.name,
                    dataIndex: 'name',
                    flex: 1
                }, {
                    xtype: 'actioncolumn',
                    width: 30,
                    items: [
                        {
                            iconCls: 'sprite-minus-circle-frame',
                            tooltip: me.systemTexts.delete,
                            handler: function (view, rowIndex, colIndex, item, opts, record) {
                                me.fireEvent(me.customEvents.removeEvent, view, record);
                            }
                        }
                    ]
                }
            ]
        });
    },

    /**
     * Creates a new container which acts as a notice for the user.
     *
     * @returns { Ext.container.Container }
     */
    createNoticeContainer: function() {
        var me = this;

        return Ext.create('Ext.container.Container', {
            cls: Ext.baseCSSPrefix + 'global-notice-text',
            html: me.systemTexts.notice
        });
    }
});
//{/block}
