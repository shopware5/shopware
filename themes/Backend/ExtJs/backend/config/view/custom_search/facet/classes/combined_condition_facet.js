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

//{namespace name=backend/custom_search/translation}

//{block name="backend/config/view/custom_search/facet/classes/combinedconditionfacet"}

Ext.define('Shopware.apps.Config.view.custom_search.facet.classes.CombinedConditionFacet', {

    getClass: function() {
        return 'Shopware\\Bundle\\SearchBundle\\Facet\\CombinedConditionFacet';
    },

    createItems: function () {
        var me = this;
        var factory = Ext.create('Shopware.attribute.SelectionFactory');

        return [
            {
                xtype: 'textfield',
                name: 'label',
                translatable: true,
                labelWidth: 150,
                allowBlank: false,
                fieldLabel: '{s name="label"}{/s}'
            }, {
                xtype: 'textfield',
                name: 'requestParameter',
                labelWidth: 150,
                allowBlank: false,
                helpText: '{s name="attribute_url"}{/s}',
                fieldLabel: '{s name="request_parameter"}{/s}',
                validator: Ext.bind(me.validateParameter, me)
            }, {
                xtype: 'productstreamselection',
                store: factory.createEntitySearchStore("Shopware\\Models\\ProductStream\\ProductStream"),
                name: 'streamId',
                labelWidth: 150,
                fieldLabel: '{s name="product_stream"}{/s}',
                helpText: '{s name="product_stream_help"}{/s}',
                allowBlank: true,
                listeners: {
                    'change': function() {
                        me.onChangeStream(this.getValue());
                    }
                }
            },
            me._createConditionPanel()
        ];
    },

    onChangeStream: function(streamId) {
        var me = this;

        if (streamId) {
            me.conditionPanel.setDisabled(true);
            me.conditionPanel.conditionPanel.removeAll();
        } else {
            me.conditionPanel.setDisabled(false);
        }

    },

    _createConditionPanel: function() {
        return this.conditionPanel = Ext.create('Shopware.apps.Config.view.custom_search.facet.classes.ConditionSelection', {
            name: 'conditions',
            allowBlank: false,
            flex: 1
        });
    },

    validateParameter: function(value) {
        var me = this;
        var reg = new RegExp(/^[a-z][a-z0-9_]+$/);

        if (!reg.test(value)) {
            return '{s name="request_parameter_validation"}{/s}';
        }
        return true;
    }
});

//{/block}
