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
 * @package    Base
 * @subpackage Component
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/base/paging_combo_box}

/**
 * Shopware UI - Paging Combo Box
 *
 * todo@all: Documentation
 */
//{block name="backend/base/Shopware.form.field.PagingComboBox"}
Ext.define('Shopware.form.field.PagingComboBox',
{
    /**
     * The shopware PagingComboBox is an extension of the extJs 4 combo box
     * @string
     */
    extend: 'Ext.form.field.ComboBox',

    /**
     * The shopware PagingComboBox can be created over the xtypes pagingcombobox and pagingcombo
     * @array
     */
    alias: ['widget.pagingcombobox', 'widget.pagingcombo'],

    /**
     * Contains the configuration for the combo box paging bar.
     * To hide a paging bar button set the corresponding flag to false.
     * Default:
     * <code>
     *  {
     *       first: true,
     *       prev: true,
     *       jumpTo: false,
     *       next: true,
     *       last: true,
     *       refresh: true
     *   }
     * Example: To hide the refresh set "refresh: false"
     * </code>
     * @object
     */
    pagingBarConfig: {
        first: true,
        prev: true,
        jumpTo: false,
        next: true,
        last: true,
        refresh: true
    },

    /**
     * Default page size for the component. If the component has an store and
     * the store has a page size, this will be used instead.
     *
     * @default 15
     * @integer
     */
    defaultPageSize: 15,

    /**
     * Always use the [defaultPageSize] if truthy. The associated store page size
     * will be ignored.
     *
     * @default false
     * @boolean
     */
    forceDefaultPageSize: false,

    /**
     * The createPicker function creates a boundlist which contains the paging toolbar.
     * To modify the toolbar, this function has to be overridden.
     * @return Ext.view.BoundList
     */
    createPicker: function() {
        var pagingComboBox = this,
            me = pagingComboBox;

        if(me.store.pageSize && !me.forceDefaultPageSize) {
            me.pageSize = me.store.pageSize;
        } else {
            me.pageSize = me.defaultPageSize;
        }

        var picker,
            menuCls = Ext.baseCSSPrefix + 'menu',
            pickerCfg = Ext.apply({
                xtype: 'boundlist',
                pickerField: me,
                selModel: {
                    mode: me.multiSelect ? 'SIMPLE' : 'SINGLE'
                },
                floating: true,
                hidden: true,

                // The picker (the dropdown) must have its zIndex managed by the same ZIndexManager which is
                // providing the zIndex of our Container.
                ownerCt: me.up('[floating]'),
                cls: me.el && me.el.up('.' + menuCls) ? menuCls : '',
                store: me.store,
                displayField: me.displayField,
                focusOnToFront: false,
                pageSize: me.pageSize,
                tpl: me.tpl,

                /**
                 * Override the createPagingToolbar function to set the custom paging bar.
                 */
                createPagingToolbar: function() {
                    return Ext.widget('pagingtoolbar', {
                        id: this.id + '-paging-toolbar',
                        pageSize: this.pageSize,
                        store: this.store,
                        border: false,

                        /**
                         * Override the getPagingItems function to hide the not required elements.
                         * @private
                         */
                        getPagingItems: function() {
                            var me = this, pageText;

                            var pagingBarItems = [{
                                itemId: 'first',
                                tooltip: me.firstText,
                                hidden: !pagingComboBox.pagingBarConfig.first,
                                overflowText: me.firstText,
                                iconCls: Ext.baseCSSPrefix + 'tbar-page-first',
                                disabled: true,
                                handler: me.moveFirst,
                                scope: me
                            },{
                                itemId: 'prev',
                                hidden: !pagingComboBox.pagingBarConfig.prev,
                                tooltip: me.prevText,
                                overflowText: me.prevText,
                                iconCls: Ext.baseCSSPrefix + 'tbar-page-prev',
                                disabled: true,
                                handler: me.movePrevious,
                                scope: me
                            },
                            {
                                xtype: 'numberfield',
                                itemId: 'inputItem',
                                name: 'inputItem',
                                hidden: !pagingComboBox.pagingBarConfig.jumpTo,
                                cls: Ext.baseCSSPrefix + 'tbar-page-number',
                                allowDecimals: false,
                                minValue: 1,
                                hideTrigger: true,
                                enableKeyEvents: true,
                                keyNavEnabled: false,
                                selectOnFocus: true,
                                submitValue: false,
                                // mark it as not a field so the form will not catch it when getting fields
                                isFormField: false,
                                width: me.inputItemWidth,
                                margins: '-1 2 3 2',
                                listeners: {
                                    scope: me,
                                    keydown: me.onPagingKeyDown,
                                    blur: me.onPagingBlur
                                }
                            },{
                                xtype: 'tbtext',
                                itemId: 'afterTextItem',
                                hidden: !pagingComboBox.pagingBarConfig.jumpTo,
                                text: Ext.String.format(me.afterPageText, 1)
                            },
                            '-',
                            {
                                itemId: 'next',
                                tooltip: me.nextText,
                                overflowText: me.nextText,
                                iconCls: Ext.baseCSSPrefix + 'tbar-page-next',
                                disabled: true,
                                hidden: !pagingComboBox.pagingBarConfig.next,
                                handler: me.moveNext,
                                scope: me
                            },{
                                itemId: 'last',
                                tooltip: me.lastText,
                                overflowText: me.lastText,
                                iconCls: Ext.baseCSSPrefix + 'tbar-page-last',
                                disabled: true,
                                handler: me.moveLast,
                                hidden: !pagingComboBox.pagingBarConfig.last,
                                scope: me
                            },
                            '-',
                            {
                                itemId: 'refresh',
                                tooltip: me.refreshText,
                                hidden: !pagingComboBox.pagingBarConfig.refresh,
                                overflowText: me.refreshText,
                                iconCls: Ext.baseCSSPrefix + 'tbar-loading',
                                handler: me.doRefresh,
                                scope: me
                            }];

                            //text field displayed? insert the page text
                            if (pagingComboBox.pagingBarConfig.jumpTo) {
                                Ext.Array.insert(pagingBarItems, 2, ['-', me.beforePageText]);
                            }

                            return pagingBarItems;
                        }
                    });
                }
            }, me.listConfig, me.defaultListConfig);

        picker = me.picker = Ext.widget(pickerCfg);
        if (me.pageSize) {
            picker.pagingToolbar.on('beforechange', me.onPageChange, me);
        }

        me.mon(picker, {
            itemclick: me.onItemClick,
            refresh: me.onListRefresh,
            scope: me
        });

        me.mon(picker.getSelectionModel(), {
            beforeselect: me.onBeforeSelect,
            beforedeselect: me.onBeforeDeselect,
            selectionchange: me.onListSelectionChange,
            scope: me
        });

        return picker;
    }

});
//{/block}
