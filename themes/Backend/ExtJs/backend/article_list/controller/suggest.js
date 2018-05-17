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

/**
 * Currently there is in Shopware no way to include those resources other than with these smarty includes
 */
// {include file='backend/article_list/resource/lexer.js'}
// {include file='backend/article_list/resource/parser.js'}
// {include file='backend/article_list/resource/tokenizer.js'}

/**
 *
 */
//{block name="backend/article_list/controller/suggest"}
Ext.define('Shopware.apps.ArticleList.controller.Suggest', {

    /**
     * The parent class that this class extends.
     * @string
     */
    extend: 'Ext.app.Controller',

    parser: null,

    refs: [
        { ref:'batchEditButton', selector:'multiedit-main-window button[name=batchEdit]' },
        { ref:'runButton', selector:'query-field button[name=run-button]' },
        { ref:'simpleRunButton', selector:'multi-edit-add-filter-grid button[name=run-button-simple]' },
        { ref:'statusLabel', selector:'query-field label[name=status-label]' },
        { ref:'statusLabelSimple', selector:'multi-edit-add-filter-grid label[name=status-label-simple]' },
        { ref:'filterCombo', selector:'query-field combo[name=filterString]' },
        { ref:'mainWindow', selector:'shopware-multiedit-main-window' }
    ],

    /**
     * A template method that is called when your application boots.
     * It is called before the Application's launch function is executed
     * so gives a hook point to run any code before your Viewport is created.
     *
     * @return void
     */
    init: function () {
        var me = this;

        me.createParser();

        me.control({
            'query-field': {
                suggest: me.onShowSuggestion
            },
            'query-field combo': {
                select: me.onSuggestionSelected
            }
        });

        me.callParent(arguments);
    },

    /**
     * Run through the grammar object and create RegExps when needed
     *
     * @param grammar
     * @returns Object
     */
    prepareGrammar: function(grammar)  {
        var me = this;


        Ext.each(Object.keys(grammar.binaryOperators), function(key) {
            var items = grammar.binaryOperators[key];

            Ext.each(items, function(item, idx) {
                if (item.charAt(item.length-1) == '/' && item.charAt(0) == '/') {

                    grammar.binaryOperators[key][idx] = new RegExp(item.slice(1).slice(0,-1));
                }
            });
        });

        Ext.each(grammar.values, function(item, idx) {
            if (item.charAt(item.length-1) == '/' && item.charAt(0) == '/') {

                grammar.values[idx] = new RegExp(item.slice(1).slice(0,-1));
            }
        });

        return grammar;

    },

    /**
     * Creates the parser. Will request the grammar object from remote.
     *
     */
    createParser: function() {
        var me = this;

        Ext.Ajax.request({
            url: '{url controller="ArticleList" action = "getGrammar"}',
            params : {
                resource: 'product'
            },
            method: 'GET',
            success: function (response, request) {
                var result = Ext.JSON.decode(response.responseText);

                if(!result) {
                    me.showError(response.responseText);
                }else if(result.success) {
                    var grammar = me.prepareGrammar(result.data);

                    me.parser = new Parser(grammar);

                    me.subApplication.fireEvent('grammarProcessed', grammar);
                    return;
                }
                me.showError(result.message);

            },
            failure: function (response, request) {
                if(response.responseText) {
                    me.showError(response.responseText);
                } else {
                    me.showError('{s name=unknownError}An unknown error occurred, please check your server logs{/s}');
                }
            }
        });

    },

    /**
     * Convenience method to show a sticky growl message
     *
     * @param message
     */
    showError: function(message) {
        var me = this;

        Shopware.Notification.createStickyGrowlMessage({
            title: '{s name=error}Error{/s}',
            text: message,
            log: true
        },
        'ArticleList');
    },

    /**
     * Returns instance of the parser
     * @returns null
     */
    getParser: function() {
        var me = this;
        return me.parser;
    },


    /**
     * Actually do show suggestions in the combobox; depending on the type of suggestion, local or remote
     * suggestions are shown
     *
     * @param suggestion
     * @param lastSeenAttribute
     * @param lastToken
     * @param lastValidToken
     */
    showSuggestions: function(suggestion, lastSeenAttribute, lastToken, lastValidToken)  {
        var me = this,
            combo = me.getFilterCombo(),
            parser = me.getParser(),
            i;

        // If we got an array of suggestions, we just set that
        if (suggestion != 'value') {
            combo.showSuggestion(suggestion, lastToken, false);
            return;
        }

        // Check if the lastSeenAttribute is valid - if not, no remote request will be performed
        var attributes = Object.keys(me.getParser().grammar.attributes);
        var attributesLength = attributes.length;

        lastSeenAttribute = lastSeenAttribute.toUpperCase();

        for (i=0;i<attributesLength;i++) {
           if (lastSeenAttribute == attributes[i].toUpperCase()) {
               // Get the suggested values from remote
               var params = {
                   resource: 'product',
                   attribute: lastSeenAttribute
               };

               combo.showRemoteSuggestion(params, lastToken, lastValidToken);
               return;
           }
        }

        // If attribute was not valid, collapse suggestion box
        combo.collapse();
    },

    /**
     * A suggestion was selected and needs to be shown in the combo
     *
     * @param combo
     * @param suggestion
     */
    onSuggestionSelected: function(combo, suggestion) {
        var me = this,
            tokens = me.getParser().lexer.tokens,
            lastToken = tokens ? tokens.slice(-1).pop() : "";

        combo.insertSuggestion(suggestion, lastToken);
    },

    /**
     * Returns a token array from a given string
     *
     * @param string
     * @returns Array
     */
    getTokensFromString: function(string) {
        var me = this;

        me.getParser().parse(string);
        return me.getParser().getAst();
    },

    /**
     * Returns the simple result representation of the given query string
     * Will return false if the result is not simple or invalid
     *
     * @param string
     * @returns Boolean|Array
     */
    getSimpleResult: function(string) {
        var me = this;

        me.getParser().parse(string);
        return me.getParser().getSimpleResult();
    },

    /**
     * Callback function triggered when a suggestion needs to be shown
     *
     * @param string
     * @param cursorPosition
     */
    onShowSuggestion: function(string, cursorPosition) {
        var me = this,
            firstResult, secondResult,
            stringUntilCurrentPosition;

        if (!string) {
            string = "";
        }

        if (!isNaN(cursorPosition)) {
            stringUntilCurrentPosition = string.substring(0, cursorPosition);
        }

        var firstResult = me.getParser().parse(stringUntilCurrentPosition);

        me.showSuggestions(
            me.getParser().getSuggestion(),
            me.getParser().getLastSeenAttribute(),
            me.getParser().lexer.tokens.slice(-1).pop(),
            me.getParser().lexer.lastToken || undefined
        );

        if (string != stringUntilCurrentPosition) {
            var secondResult = me.getParser().parse(string);
            me.setStatus();
        } else {
            me.setStatus();

        }
    },

    /**
     * Check if a given filter is valid
     *
     * @param filterString
     * @returns boolean
     */
    isFilterValid: function(filterString) {
        var me = this;

        if (!filterString || filterString == '') {
            return false;
        }

        return me.getParser().parse(filterString);
    },

    /**
     * Loads a given filterString and show products, which do match that filter
     *
     * @param filterString
     * @param name
     * @returns boolean
     */
    loadFilter: function(filterString, name, extraParams) {
        var me = this,
            batchEditButton = me.getBatchEditButton(),
            mainWindow = me.getController('Main').mainWindow,
            result = me.getParser().parse(filterString);

        var existing = me.subApplication.articleStore.getProxy().extraParams;

        if (!extraParams) {
            extraParams = { };
        }

        extraParams = Ext.Object.merge(existing, extraParams);
        extraParams.resource = 'product';
        extraParams.ast = Ext.JSON.encode(me.getParser().getAst());

        if (result) {
            me.subApplication.currentFilterName = name;
            me.subApplication.currentFilterString = filterString;
            me.subApplication.articleStore.getProxy().extraParams = extraParams;
            me.subApplication.articleStore.loadPage(1, { callback: function () {
                me.getController('BatchProcess').updateBatchProcessWindow();
                mainWindow.setWindowTitle(name);
            }});
            batchEditButton.enable();
            return true;
        } else {
            me.showError(Ext.String.format('{s name=parserError}Could not parse the string [0]: [1]{/s}', filterString, me.getErrorMessage()));
            batchEditButton.disable();
            return false;
        }
    },

    /**
     * Read the last error message from parser
     *
     * @returns string
     */
    getErrorMessage: function() {
        var me = this,
            error = me.getParser().getError(),
            context;

        // Rename the first token
        if (error.lastValidToken == 0) {
            context = error.context ? ' ' + error.context : "{s name=expectedErrorMessage/begin} at Begin{/s}";
        } else {
            context = error.context ? ' ' + error.context : Ext.String.format("{s name=expectedErrorMessage/after} after [0]{/s}", error.lastValidToken);
        }
        return Ext.String.format("{s name=expectedErrorMessage}Expected [0] got [1] instead{/s}", error.expectation.message + context, error.currentToken);
    },

    /**
     * Sets the status indicator in order to give the user feedback whether his query is valid or not
     */
    setStatus: function() {
        var me = this,
            error = me.getParser().getError(),
            combo = me.getFilterCombo(),
            runButton = me.getRunButton(),
            simpleRunButton = me.getSimpleRunButton(),
            simpleLabel = me.getStatusLabelSimple(),
            label = me.getStatusLabel();

        if (!error || error == false) {
            Ext.get(combo.inputEl).setStyle('background', '#C7F5AA');
            label.setText("{s name=addFilter/noErrorDetected}No error detected{/s}");
            simpleLabel.setText("{s name=addFilter/noErrorDetected}No error detected{/s}");
            runButton.enable();
            simpleRunButton.enable();
        } else {
            Ext.get(combo.inputEl).setStyle('background', '#FFB0AD');
            label.setText(me.getErrorMessage());
            simpleLabel.setText(me.getErrorMessage());
            runButton.disable();
            simpleRunButton.disable();
        }

        try {
            combo.alignPicker();
        } catch (e) {
            // The picker might not be available
        }


    }
});
//{/block}
