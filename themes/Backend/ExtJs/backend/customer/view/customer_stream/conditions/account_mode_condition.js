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
 * @package    Customer
 * @subpackage CustomerStream
 * @version    $Id$
 * @author shopware AG
 */

// {namespace name=backend/customer/view/main}
// {block name="backend/customer/view/customer_stream/conditions/account_mode_condition"}
Ext.define('Shopware.apps.Customer.view.customer_stream.conditions.AccountModeCondition', {

    getLabel: function() {
        return '{s name="column/accountMode"}{/s}';
    },

    supports: function(conditionClass) {
        return (conditionClass.indexOf('Shopware\\Bundle\\CustomerSearchBundle\\Condition\\AccountModeCondition') >= 0);
    },

    create: function(callback) {
        callback(this._create());
    },

    load: function(conditionClass, items, callback) {
        callback(this._create());
    },

    _create: function() {
        var me = this;

        return {
            title: this.getLabel(),
            conditionClass: 'Shopware\\Bundle\\CustomerSearchBundle\\Condition\\AccountModeCondition',
            items: [ me.createAccountModeField() ]
        };
    },

    createAccountModeField: function () {
        var me = this;
        me.accountModeSelection = Ext.create('Ext.form.field.ComboBox', {
            fieldLabel: '{s name=column/accountMode}{/s}',
            store: me.createAccountModeStore(),
            displayField: 'name',
            valueField: 'value',
            allowBlank: false,
            name: 'accountMode'
        });
        return me.accountModeSelection;
    },

    createAccountModeStore: function () {
        return Ext.create('Ext.data.Store', {
            fields: [ 'name', 'value' ],
            data: [
                { name: '{s name=accountModeNormal}{/s}', value: '0' },
                { name: '{s name=accountModeGuest}{/s}', value: '1' }
            ]
        });
    }
});
// {/block}
