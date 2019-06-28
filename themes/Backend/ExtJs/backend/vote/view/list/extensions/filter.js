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
 * @package    Vote
 * @subpackage App
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/vote/main}

//{block name="backend/vote/view/list/extension/filter"}
Ext.define('Shopware.apps.Vote.view.list.extensions.Filter', {
    extend: 'Shopware.listing.FilterPanel',
    alias:  'widget.vote-listing-filter-panel',
    width: 390,

    configure: function() {
        return {
            controller: 'Vote',
            model: 'Shopware.apps.Vote.model.Vote',
            fields: {
                active: {
                    xtype: 'combobox',
                    fieldLabel: '{s name="active"}{/s}',
                    valueField: 'value',
                    displayField: 'label',
                    store: new Ext.data.Store({
                        fields: ['value', 'label'],
                        data: [
                            { value: 0, label: '{s name="not_accepted"}{/s}' },
                            { value: 1, label: '{s name="active"}{/s}' }
                        ]
                    })
                },
                shopId: {
                    fieldLabel: '{s name="shop"}{/s}'
                },
                articleId: {
                    fieldLabel: '{s name="article"}{/s}',
                    pageSize: 20
                },
                datum: {
                    fieldLabel: '{s name="date"}{/s}',
                    expression: '>='
                },
                points: {
                    fieldLabel: '{s name="points"}{/s}',
                    expression: '>=',
                    minValue: 0,
                    step: 0.5,
                    maxValue: 5
                }
            }
        };
    }
});
//{/block}
