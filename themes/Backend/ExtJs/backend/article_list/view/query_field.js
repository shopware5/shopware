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

//{namespace name="backend/article_list/main"}

/**
 * Shopware UI - Main Panel
 */
//{block name="backend/article_list/view/query-field"}
Ext.define('Shopware.apps.ArticleList.view.QueryField', {
    extend: 'Ext.form.Panel',
    alias: 'widget.query-field',

    bodyBorder: 0,

    border: false,


    padding: '10 10 0 10',


    /**
     * Initializes the component, sets up toolbar and pagingbar and and registers some events
     *
     * @return void
     */
    initComponent: function () {
        var me = this;

        // Create the items of the container
        me.items = me.createItems();

        me.addEvents(
            'suggest',

            'filter'
        );

        me.callParent(arguments);
    },

    /**
     * Creates the items for the card layout
     * @return Array
     */
    createItems: function () {
        var me = this;

        return [{
            xtype: 'fieldset',
            title: '{s name=addFilter/queryTitle}Your query{/s}',
            defaults : {
                margin: '0 0 10 0'
            },
            items: [
                me.getFilterInputField(),
            {
                xtype: 'label',
                name: 'status-label',
                text: '{s name=queryField/enterQuery}Enter your Query here:{/s}'
            }]
        }];

    },

    /**
     * Returns the whole input field including indicator, combo and execute-button
     *
     * @returns Object
     */
    getFilterInputField: function () {
        var me = this;
        return {
            xtype: 'container',
            layout: {
                type: 'hbox',
                pack: 'start',
                align: 'stretchmax'
            },
            items: [
                me.getCombo(),
                {
                    xtype: 'button',
                    text: '{s name=addFilter/run}Execute{/s}',
                    name: 'run-button',
                    iconCls: 'sprite-magnifier--arrow',
                    tooltip: '{s name=runFilter}Immediatly show matching articles{/s}',
                    disabled: true,
                    handler: function () {
                        me.fireEvent('filter');
                    }
                }
            ]
        };
    },

    /**
     * Returns the combo box the filter input field bases on
     *
     * @returns Object
     */
    getCombo: function() {
        var me = this;
        me.combo = Ext.create('Shopware.form.field.FilterCombo', {
            name: 'filterString',
            flex: '1',

            // Debug
            'queryMode': 'local',
            queryDelay: 500,

            displayField: 'title',
            autoSelect: true,
            typeAhead: false,
            hideLabel: true,
            hideTrigger: true,

            listConfig: {
                resizable: false,
                maxWidth: 300,
                height: 3333,
                maxHeight: '100%',
                loadingText: 'Searching...',
                emptyText: 'No matching posts found.',

            pageSize: 10
        },

            // override default onSelect to do redirect
            listeners: {
                // Disable spellcheck
                'afterrender': { fn: function () {
                    me.combo.inputEl.dom.spellcheck = false;
                }, scope: this },
                'focus': { fn: me.comboChangeCallback, scope: this},
                'change': { fn: me.comboChangeCallback, scope: this }
            }
        });

        return me.combo;
    },

    comboChangeCallback: function(combo) {
        var me = this,
            position,
            text = combo.getValue();

        if (text == null) {
            return;
        }

        position = combo.inputEl.dom.selectionStart;

        me.fireEvent('suggest', text, position);
    }


});
//{/block}
