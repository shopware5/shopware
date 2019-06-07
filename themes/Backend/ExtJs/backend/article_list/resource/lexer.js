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
 * The lexer analyzes a given array of tokens and makes sure, that the order of thoken matches the grammar
 */
//{namespace name=backend/article_list/main}
Ext.define('Lexer', {

    /**
     * Constructor method for the lexer object
     * @param grammar
     */
    constructor: function(grammar) {
        var me = this;

        me.grammar = grammar;
        me.init();
    },

    /**
     * Setup the lexer
     */
    init: function() {
        var me = this;

        // The last token the lexer looked at
        me.lastToken = 0;
        // The type of the last token the lexer looked at
        me.lastTokenType = 0;

        // Keep track of open braces
        me.braceCounter = 0;

        // Are we currently inside a list (e.g. IN (1,2,3))
        me.inList = false;

        // Was the process canceled?
        me.canceled = false;

        // The attribute the lexer looked at last
        me.lastSeenAttribute = null;

        // The item the lexer would suggest next
        me.suggest = null;

        // A flat description of the tokens (its not an ast, actually)
        me.astFlat = [ ];

        // Simple queries can be shown and edited in the "graphical" query designer
        me.isSimple = true;

        // Simple result which can be shown in the "simple editor" grid
        me.simpleResult = [ ];

        // Stores the tokens belonging to the current expression
        me.currentExpression = [ ];

    },

    /**
     * Cancel operation
     */
    cancel: function () {
        var me = this;

        me.canceled = true;
    },

    setSuggestion: function(suggestion) {
        this.suggest = suggestion;
    },

    /**
     * Analyze all the tokens and make sure, that they are valid
     */
    analyze: function (tokens) {
        var me = this,
            expectation,
            valid,
            finalCheck,
            i, token, originalToken, tokenType, tokenLength = tokens.length;

        me.tokens = tokens;
        me.canceled = false;
        me.isSimple = true;
        me.simpleResult = [ ];
        me.currentExpression = [ ];

        if (tokens[0] ==  undefined) {
            expectation = me.getExpectationForToken(0, 0);
            me.lastSeenAttribute = '';
            me.lastToken = '';
            me.lastTokenType = 'boolOperators';
            me.setExpressionNotSimple();
            return {
                success: false,
                expectation: expectation,
                lastValidToken: '',
                currentToken: ''
            };
        }

        // Step through all the tokens
        for (i=0;i<tokenLength;i++) {
            token = tokens[i];

            if (me.canceled) {
                return;
            }
            originalToken = token;
            token = token.toUpperCase();
            tokenType = me.getTokenType(token);

            if (tokenType == 'attribute' && Object.keys(me.grammar.attributes).indexOf(token) > -1) {
                me.lastSeenAttribute = token;
            }

            // subOperators, Lists and OR are not possible in simple queries
            if (token == 'IN' || token == 'NOT IN' || tokenType == 'subOperators' || (tokenType == 'boolOperators' && token == 'OR')) {
                me.setExpressionNotSimple();
            } else if (tokenType == 'boolOperators' && token == 'AND') {
                me.addToSimpleResult();
            }

            // Check for braces
            if (token == '(') {
                me.braceCounter++;
            }

            // Check for list
            if (token == 'IN' || token == 'NOT IN') {
                me.inList = true;
            }

            // Validate the token
            expectation = me.getExpectationForToken(me.lastToken, me.lastTokenType);
            valid = me.checkToken(token, tokenType, expectation.rules);
            if (!valid) {
                me.setExpressionNotSimple();
                return {
                    success: false,
                    expectation: expectation,
                    lastValidToken: me.lastToken,
                    currentToken: token
                };
            }

            if (me.isSimple) {
                me.buildSimpleResult(token, tokenType);
            }


            me.addToAst(token, originalToken, tokenType);

            if (me.inList && token == ')') {
                me.inList = false;
            }
            if (token == ')') {
                me.braceCounter--;
            }
            me.lastToken = token;
            me.lastTokenType = tokenType;
        }
        if (me.canceled) {
            return;
        }

        finalCheck = me.checkFinalState();
        if (finalCheck == true) {
            me.addToSimpleResult();

            // Once we are done, we can start all over, so suggest a boolOperators
            me.setSuggestion(me.grammar.boolOperators);
            return true;
        } else {
            me.setExpressionNotSimple();

            // If we are not done, show a suggestion for the last item
            me.getExpectationForToken(me.tokens.slice(-1).pop().toUpperCase(), me.getTokenType(me.tokens.slice(-1).pop().toUpperCase()));
            return finalCheck;
        }
    },


    /**
     * Mark the query-string as not being simple
     */
    setExpressionNotSimple: function() {
        var me = this;

        me.isSimple = false;
        me.simpleResult = [ ];
        me.currentExpress = [ ];
    },

    /**
     * Push the current expression to the simpleResult array
     */
    addToSimpleResult: function() {
        var me = this;

        if (!me.isSimple) {
            return;
        }

        me.simpleResult.push(me.currentExpression);
        me.currentExpression = [ ];
    },

    /**
     * Push the current token to the currentExpression-Array. One a expression is satisfied (valid on its own)
     * it will be pushed to me.simpleResult by addToSimpleResult
     *
     * @param token
     * @param tokenType
     */
    buildSimpleResult: function(token, tokenType) {
        var me = this;

        if (!(tokenType == 'boolOperators' && token == 'AND')) {
            me.currentExpression.push({ token: token, tokenType: tokenType });
        }
    },

    /**
     * Add a given token to our token list
     *
     * @param token
     * @param tokenType
     */
    addToAst: function(token, originalToken, tokenType) {
        var me = this;

        if (tokenType === 'values') {
            /* 
             * Do not use `token` which was usually generated from `originalToken.toUpperCase()`
             * because the following is not always true and would make filtering certain values impossible:
             * str.toUpperCase().toLowerCase() === str.toLowerCase()
             * e.g. 'ß'.toUpperCase() == 'SS'
             */
            token = originalToken;
        }

        this.astFlat.push({
            type: tokenType,
            token: token
        });
    },

    /**
     * This check is performed at the end. It makes sure, that no braces are open and that all expressions
     * are satisfied
     *
     * @returns boolean|Object
     */
    checkFinalState: function () {
        var me = this,
            expectation,
            token = me.tokens[me.tokens.length - 1] ? me.tokens[me.tokens.length - 1].toUpperCase() : '',
            tokenType = me.getTokenType(token);

        // At the end of all the token we should no be "inList" any more
        if (me.inList) {
            if (token == ',' || token == '(') {
                return {
                    success: false,
                    expectation: { message: "{s name=lexer/value}value{/s}", rule: me.grammar.values },
                    context: '{s name=lexer/inList}in list{/s}',
                    currentToken: '{s name=lexer/end}END{/s}'
                };
            }
            if (tokenType == 'values') {
                return {
                    expectation: { message: "{s name=lexer/commaOrBrace}, OR ){/s}", rule: ',|)' },
                    context: '{s name=lexer/afterValueInList}after value in list{/s}',
                    currentToken: '{s name=lexer/end}END{/s}',
                    success: false
                };
            }
        }

        // Check for unsatisfied expressions (e.g. name =)
        if (tokenType != 'unaryOperators' && tokenType != 'nullaryOperators' && tokenType != 'values' && token != ')') {
            expectation = me.getExpectationForToken(token, tokenType);
            return {
                expectation: expectation,
                lastValidToken: tokenType,
                currentToken: '{s name=lexer/end}END{/s}',
                success: false
            };
        // Check for open braces
        } else if (me.braceCounter != 0) {
            return {
                expectation: { message: '{s name=lexer/closingBrace}closing brace{/s}', rule: ')'},
                lastValidToken: tokenType,
                currentToken: '{s name=lexer/end}END{/s}',
                success: false
            };
        }

        return true;
    },

    /**
     * Check the current token based on a given expected value
     *
     * @param token         The token we've got
     * @param tokenType     The token's type
     * @param expectation   What did the lexer expect?
     * @returns boolean
     */
    checkToken: function (token, tokenType, expectation) {
        var me = this,
            index;

        if (expectation == 'expression') {
            // todo: fix: don't hardcode (
            return tokenType == 'attribute' || tokenType == "nullaryOperators" || token == '(';
        }

        if (typeof expectation === 'string') {
            return tokenType == expectation || token == expectation;
        }

        // If array, check if operator matches
        if (expectation.indexOf) {
            index = Ext.each(expectation, function (rule) {
                // Simple operator comparision
                if (me.getOperators().indexOf(rule) > -1) {
                    if (rule == token) {
                        return false;
                    }
                    return;
                }

                var idx = token[rule.test ? 'search' : 'indexOf'](rule);
                if (idx > -1) {
                    return false;
                }
            });

            if (index === true) {
                return false;
            }
            return true;
        }

    },

    /**
     * Based on a given token and its type: Expect the next token
     *
     * @param token
     * @param tokenType
     * @returns Object
     */
    getExpectationForToken: function (token, tokenType) {
        var me = this;

        // First token
        if (token == 0 && tokenType != 'values') {
            me.setSuggestion(Object.keys(me.grammar.attributes).concat(Object.keys(me.grammar.nullaryOperators)));
            return { rules: 'expression', message: '{s name=lexer/expressionOrAttribute}expression or attribute{/s}'}
        }

        /**
         * Handle list - basically expect "(", ",", "value" or ")
         */
        if (me.inList) {
            if (token == ',' || token == '(') {
                me.setSuggestion('value');
                return { rules: me.grammar.values, message: '{s name=lexer/value}Value{/s}'}
            }
            if (token == 'IN' || token == 'NOT IN') {
                me.setSuggestion(['(']);
                return { rules: '(', message: '{s name=lexer/openingBrace}({/s}'}
            }
            if (tokenType == 'values') {
                me.setSuggestion([',', ')']);
                return { rules: [',', ')'], message: '{s name=lexer/commaOrBrace}, OR ){/s}'}
            }
        }

        /**
         * Last token:  Bool operator like AND or OR
         * Expect:      New expression
         */
        if (tokenType == "boolOperators") {
            me.setSuggestion(Object.keys(me.grammar.attributes).concat(Object.keys(me.grammar.nullaryOperators)));
            return { rules: 'expression', message: '{s name=lexer/expression}Expression{/s}'}
        }

        /**
         * Last token:  Opening brace
         * Expect:      New expression
         *
         * Last token:  Closing brace
         * Expect:      Bool operator
         *
         * Last token: Closing brace - braces not in balance
         * Expect:      Bool operators or closing brace
         */
        if (tokenType == "subOperators" && token != ')') {
            me.setSuggestion(Object.keys(me.grammar.attributes).concat(Object.keys(me.grammar.nullaryOperators)));
            return { rules: 'expression', message: '{s name=lexer/expression}Expression{/s}'}
        } else if (tokenType == "subOperators" && token == ")" && me.braceCounter == 0) {
            me.setSuggestion(me.grammar.boolOperators);
            return { rules: 'boolOperators', message: '{s name=lexer/boolOperators}boolOperators{/s}'}
        } else if (tokenType == "subOperators" && token == ")" && me.braceCounter != 0) {
            me.setSuggestion(me.grammar.boolOperators.concat([')']));
            return { rules: me.grammar.boolOperators.concat([')']), message: '{s name=lexer/boolOperatorsOrBrace}boolOperators or ){/s}'};
        }

        /**
         * Last token:  Value, left side operator, atomic operator or value - Braces in balance
         * Expect:      bool operator
         *
         * Last token:  As above - but braces are not balanced
         * Expect:      bool operator or closing brace
         */
        if ((tokenType == 'unaryOperators' || tokenType == 'nullaryOperators' || tokenType == 'values') && me.braceCounter == 0) {
            me.setSuggestion(me.grammar.boolOperators);
            return { rules: 'boolOperators', message: '{s name=lexer/boolOperators}boolOperators{/s}'}
        } else if (tokenType == 'nullaryOperators' || tokenType == 'unaryOperators' || tokenType == 'values') {
            me.setSuggestion(me.grammar.boolOperators.concat([')']));
            return { rules: me.grammar.boolOperators.concat([')']), message: '{s name=lexer/boolOperatorsOrBrace}boolOperators or ){/s}'};
        }


        /**
         * Last token:  double sided operator
         * Expect:      value
         */
        if (tokenType == 'binaryOperators') {
            me.setSuggestion('value');
            return { rules: me.grammar[tokenType][token], message: '{s name=lexer/value}Value{/s}'}
        }

        /**
         * Last token:  attribute
         * Expect:      an operator compatible to this attribute
         */
        if (tokenType == 'attribute') {
            me.setSuggestion(me.grammar.attributes[token]);
            return { rules: me.grammar.attributes[token], message: '{s name=lexer/onOfThese}one of these: {/s}: ' + me.grammar.attributes[token].join(",")}
        }

        /**
         * Any other case is an unexpected / unknown token and will result in errors
         */
        console.log(token);
        console.log(tokenType);

    },

    /**
     * Determine the type of a given token by searching through the operators, attributes and values
     *
     * @param token
     * @returns string
     */
    getTokenType: function (token) {
        var me = this,
            operators,
            index;

        operators = me.getOperators('binaryOperators');
        index = operators.indexOf(token);
        if (index != -1) {
            return "binaryOperators";
        }

        operators = me.getOperators('unaryOperators');
        index = operators.indexOf(token);
        if (index != -1) {
            return "unaryOperators";
        }

        operators = me.getOperators('nullaryOperators');
        index = operators.indexOf(token);
        if (index != -1) {
            return "nullaryOperators";
        }

        index = me.grammar.boolOperators.indexOf(token);
        if (index != -1) {
            return "boolOperators";
        }

        index = me.grammar.subOperators.indexOf(token);
        if (index != -1) {
            return "subOperators";
        }

        index = Object.keys(me.grammar.attributes).indexOf(token);
        if (index != -1) {
            return "attribute";
        }

        index = Ext.each(me.grammar.values, function (rule) {
            var idx = token[rule.test ? 'search' : 'indexOf'](rule);
            if (idx > -1) {
                return false;
            }
        });
        if (index !== true) {
            return "values";
        }

        /**
         * Unknown tokens will result in an error
         */
        return "unknown";

    },

    /**
     * Get all available operators as string
     *
     * @param operator
     * @returns Array
     */
    getOperators: function (operator) {
        var me = this;

        if (operator) {
            return Object.keys(me.grammar[operator]);
        } else {
            return Object.keys(me.grammar.unaryOperators).concat(Object.keys(me.grammar.binaryOperators), Object.keys(me.grammar.nullaryOperators));
        }
    }
});
