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
//{block name="backend/article_list/view/main"}
Ext.define('Shopware.apps.ArticleList.view.FilterCombo', {

    /**
     * Extend from ExtJS default ComboBox.
     */
    extend: 'Ext.form.field.ComboBox',
    alternateClassName: [ 'Shopware.form.field.FilterCombo', 'Shopware.FilterCombo' ],

    /**
     * Alias
     */
    alias: 'widget.filterString',

    /**
     * The last token we have seen before a suggestion was loaded.
     * Needed in order to know, where to insert the suggestion
     */
    lastFilteredToken: 0,

    /**
     * Ignore the next token. Used after a token was inserted - we do not need to look it up again
     */
    ignoreToken: false,

    /**
     * The initial value of the filter is an empty string (instead of undefined)
     */
    value: '',

    /**
     * Constructor - setup the FilterCombo
     */
    constructor: function() {
        var me = this;

        Ext.define("Post", {
            extend: 'Ext.data.Model',
            fields: [
                { name: 'id' },
                { name: 'title'},
                { name: 'addQuotes', defaultValue: true}
            ]
        });

        me.store = Ext.create('Ext.data.Store', {
            pageSize: 10,
            model: 'Post',
            proxy: {
                type: 'ajax',
                url: '{url controller="ArticleList" action="getValues"}',
                reader: {
                    type: 'json',
                    root:'data',
                    totalProperty: 'total'
                }
            }
        });

        me.callParent(arguments);
    },

    /**
     * Insert a suggestion selected by the user
     *
     * @param suggestion
     * @param token
     */
    insertSuggestion: function(suggestion, token) {
        var me = this,
            value = me.getValue() || '',
            rest,
            tokenLength = me.lastFilteredToken ? me.lastFilteredToken.length : 0,
            position = me.inputEl.dom.selectionStart;

        rest = value.substring(position);
        value = value.substring(0, position-tokenLength);

        if (suggestion.get('addQuotes')) {
            suggestion = '"' + suggestion.get('title') + '"';
        } else {
            suggestion = suggestion.get('title');
        }

        me.ignoreToken = true;
        me.setValue (value + suggestion + " " + rest);

    },

    /**
     * Show suggestions from remote
     *
     * @param params
     * @param token
     * @param lastValidToken
     */
    showRemoteSuggestion: function(params, token, lastValidToken) {
        var me = this,
            position = me.inputEl.dom.selectionStart;

        if (lastValidToken == token) {
            token = '';
        }

        // Enable remote store
        me.queryMode = "remote";
        me.store.proxy.extraParams = params;
        me.store.remoteFilter = true;
        me.queryCaching = true;


        // Setting the filter this way ensures the filter to be replaced
        me.store.clearFilter(true);
        me.store.filter({ id: 'filter', property: 'filter', value: token ? token.replace('"', "") : token });
        me.lastFilteredToken = token;

    },

    /**
     * Show local suggestions (from the lexer)
     *
     * @param suggestions
     * @param token
     * @param addQuotes
     */
    showSuggestion: function(suggestions, token, addQuotes) {
        var me = this,
            position = me.inputEl.dom.selectionStart;

        // Enable local store
        me.queryMode = "local";
        me.store.remoteFilter = false;
        me.queryCaching = false;

        me.store.clearFilter(true);


        if (!me.getValue() || me.getValue().substring(position-1, position).trim() == "") {
            token = undefined;
        }

        if (me.ignoreToken) {
            token = undefined;
            me.ignoreToken = false;
        }

        me.store.removeAll();
        me.store.loadData(suggestions.map(function (suggestion) {
            return { title: suggestion, addQuotes: addQuotes };
        }));

        me.lastFilteredToken = token;

        me.expand();
        me.store.clearFilter(true);
        me.store.filter({ id: 'filter', anyMatch:true, property: 'title', value: token ? token.replace('"', "") : '' });
    },


    /**
     * Override the key up callback: We trigger the queries on our own!
     *
     * @param e
     * @param t
     */
    onKeyUp: function(e, t) {
        var me = this,
            key = e.getKey();

        // Only store the lastkey - used for typeAhead only
        if (!me.readOnly && !me.disabled && me.editable) {
            me.lastKey = key;
        }
    },

    /**
     * Override listSelectionChange
     *
     * @param list
     * @param selectedRecords
     */
    onListSelectionChange: function(list, selectedRecords) {
        var me = this,
            isMulti = me.multiSelect,
            hasRecords = selectedRecords.length > 0;

        // Only react to selection if it is not called from setValue, and if our list is
        // expanded (ignores changes to the selection model triggered elsewhere)
        if (!me.ignoreSelection && me.isExpanded) {
            if (!isMulti) {
                /**
                 * Override
                 */
                // Ext.defer(me.collapse, 1, me);
            }
            /*
             * Only set the value here if we're in multi selection mode or we have
             * a selection. Otherwise setValue will be called with an empty value
             * which will cause the change event to fire twice.
             */
            if (isMulti || hasRecords) {
                /**
                 * Override
                 */
                // me.setValue(selectedRecords, false);
            }
            if (hasRecords) {
                me.fireEvent('select', me, selectedRecords[0]);
            }
            me.inputEl.focus();
        }
    },

    /**
     * Override onItemClick in order to set the item when clicked
     *
     * @param picker
     * @param record
     */
    onItemClick: function(picker, record){
        /*
         * If we're doing single selection, the selection change events won't fire when
         * clicking on the selected element. Detect it here.
         */
        var me = this,
            selection = me.picker.getSelectionModel().getSelection();

        if (!me.multiSelect && selection.length) {
            if (record.get(me.valueField) === selection[0]) {
                // Make sure we also update the display value if it's only partial
                me.displayTplData = [record.data];
                me.fireEvent('select', me, selection[0]);
                /**
                 * Override
                 */
                // me.setRawValue(me.getDisplayValue());
                me.collapse();
            }
        }
    },

    /**
     * Override onLoad in order to not clear the input field if nothing was found
     */
    onLoad: function () {
        var me = this,
            value = me.value;

        if (me.ignoreSelection > 0) {
            --me.ignoreSelection;
        }

        // If performing a remote query upon the raw value...
        if (me.rawQuery) {
            me.rawQuery = false;
            me.syncSelection();
            if (me.picker && !me.picker.getSelectionModel().hasSelection()) {
                me.doAutoSelect();
            }
        }
        // If store initial load or triggerAction: 'all' trigger click.
        else {
            // Set the value on load
            if (me.value || me.value === 0) {
                me.setValue(me.value);
            } else {
                // There's no value.
                // Highlight the first item in the list if autoSelect: true
                if (me.store.getCount()) {
                    me.doAutoSelect();
                } else {
                    // assign whatever empty value we have to prevent change from firing
                    /**
                     * Override
                     */
                   // me.setValue(me.value);
                }
            }
        }
    }

});
//{/block}
