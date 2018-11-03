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
 * @package    Tax
 * @subpackage Store
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware Backend - Rules store
 *
 * todo@all: Documentation
 */
//{block name="backend/countries/tax/rules"}
Ext.define('Shopware.apps.Tax.store.Rules', {
    extend: 'Ext.data.Store',
    autoLoad: false,
    pageSize: 30,
    model : 'Shopware.apps.Tax.model.Rules',
    listeners: {
        /**
         * Loop through each rule and check if any default rule is defined
         * if not display notice to user
         * @param store
         */
      'datachanged': function (store){
          var foundDefaultRule = false;
          // Store.grid pointer is defined in main/rules.js
          store.grid.toolbar.items.items[1].hide();

          Ext.Array.each(store.data.items, function(item){
              var data = item.data;
               if (data.areaId == 0 && data.countryId == 0 && data.stateId == 0){
                   foundDefaultRule = true;
               }
          });

          if (!foundDefaultRule){
              store.grid.toolbar.items.items[1].show();
          }

      }
    }

});
//{/block}
