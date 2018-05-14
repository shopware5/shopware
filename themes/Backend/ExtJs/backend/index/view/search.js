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

/**
 * Shopware Global Search
 *
 * This component creates the global search for the Shopware
 * Backend.
 */
//{namespace name=backend/index/view/main}
Ext.define('Shopware.apps.Index.view.Search', {
    extend: 'Ext.container.Container',
    alias: 'widget.searchfield',
    alternateClassName: 'Shopware.Search',
    cls: 'searchfield-container',

    /*{if $esEnabled}*/
        minSearchLength: 2,
    /*{else}*/
        minSearchLength: 4,
    /*{/if}*/

    /**
     * URL which handles the search requests
     *
     * @string
     */
    requestUrl: '{url controller=search}',

    /**
     * Class name which will be set on focus
     *
     * @string
     */
    focusCls: 'searchcontainer-focus',

    /**
     * Initialize the search and creates the search field and
     * the drop down menu
     */
    initComponent: function () {
        var me = this;

        me.callParent(arguments);

        me.searchField = Ext.create('Ext.form.field.Text', {
            emptyText: '{s name=view/search}Search...{/s}',
            cls: 'searchfield',
            margin: '5 0',
            allowBlank: true,
            enableKeyEvents: true,
            /*{if $esEnabled}*/
            checkChangeBuffer: 50,
            /*{else}*/
            checkChangeBuffer: 400,
            /*{/if}*/
            listeners: {
                scope: me,
                change: me.sendSearchRequest,
                focus: function (field) {
                    me.addCls(me.focusCls);
                    me.sendSearchRequest(field);
                },
                blur: function () {

                    // Hide search drop down
                    Ext.defer(function () {
                        Shopware.searchField.searchDropDown.hide();
                        me.removeCls(me.focusCls);
                    }, 1000);
                }
            }

        });

        me.searchDropDown = Ext.create('Ext.container.Container', {
            cls: Ext.baseCSSPrefix + 'search-dropdown',
            renderTo: Ext.getBody(),
            style: 'position: fixed; z-index: 20030',
            hidden: true
        });

        me.add(me.searchField);
        Shopware.searchField = me;
    },

    /**
     * This function sends the AJAX request depending by the field parameter and replaces
     * the content of the drop down menu
     *
     * @param (object) field
     */
    sendSearchRequest: function (field) {
        var value = field.getValue(),
            me = this;

        // Check the length of the search query
        if (value.length < this.minSearchLength) {
            me.searchDropDown.update('');
            me.searchDropDown.hide();
            return false;
        }

        // Request the search result
        Ext.Ajax.request({
            url: me.requestUrl,
            params: { search: value },
            method: 'POST',
            success: function (response) {
                var html = response.responseText,
                    parent = me.searchField.getEl().parent('.searchfield-container'),
                    left = parent.dom.offsetLeft,
                    top = parent.dom.offsetTop;


                me.searchDropDown.update(html);
                me.searchDropDown.getEl().applyStyles({
                    top: top + 'px',
                    left: left + 'px'
                });
                me.searchDropDown.show();
            }
        });

    }
});
