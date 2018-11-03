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
 * @package    RiskManagement
 * @subpackage Store
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/risk_management/main}

/**
 * Shopware UI - Risks store
 *
 * This store contains all risks.
 */
//{block name="backend/risk_management/store/risks"}
Ext.define('Shopware.apps.RiskManagement.store.Risks', {

    /**
    * Extend for the standard ExtJS 4
    * @string
    */
    extend: 'Ext.data.Store',

    /**
    * The fields used for this store
    * @array
    */
    fields: [
        { name: 'description', type: 'string' },
        { name: 'value', type: 'string' }
    ],

    data: [
        { description: '{s name=risks_store/comboBox/startValue}Please choose{/s}', value: "" },
        //{block name="backend/risk_management/store/risk/data"}{/block}
        { description: '{s name=risks_store/comboBox/orderValueGt}Ordervalue >={/s}', value: 'ORDERVALUEMORE' },
        { description: '{s name=risks_store/comboBox/orderValueLt}Ordervalue <={/s}', value: 'ORDERVALUELESS' },
        { description: '{s name=risks_store/comboBox/customerGroupIs}Customergroup IS{/s}', value: 'CUSTOMERGROUPIS' },
        { description: '{s name=risks_store/comboBox/customerGroupIsNot}Customergroup IS NOT{/s}', value: 'CUSTOMERGROUPISNOT' },
        { description: '{s name=risks_store/comboBox/newCustomer}Customer IS NEW{/s}', value: 'NEWCUSTOMER' },
        { description: '{s name=risks_store/comboBox/zoneIs}Zone IS{/s}', value: 'ZONEIS' },
        { description: '{s name=risks_store/comboBox/zoneIsNot}Zone IS NOT{/s}', value: 'ZONEISNOT' },
        { description: '{s name=risks_store/comboBox/billingZoneIs}Billing Zone IS{/s}', value: 'BILLINGZONEIS' },
        { description: '{s name=risks_store/comboBox/billingZoneIsNot}Billing Zone IS NOT{/s}', value: 'BILLINGZONEISNOT' },
        { description: '{s name=risks_store/comboBox/countryIs}Delivery Country IS{/s}', value: 'LANDIS' },
        { description: '{s name=risks_store/comboBox/countryIsNot}Delivery Country IS NOT{/s}', value: 'LANDISNOT' },
        { description: '{s name=risks_store/comboBox/billingCountryIs}Billing Country IS{/s}', value: 'BILLINGLANDIS' },
        { description: '{s name=risks_store/comboBox/billingCountryIsNot}Billing Country IS NOT{/s}', value: 'BILLINGLANDISNOT' },
        { description: '{s name=risks_store/comboBox/orderPositionsGt}Orderpositions >={/s}', value: 'ORDERPOSITIONSMORE' },
        { description: '{s name=risks_store/comboBox/dunninglevelone}Dunning level 1 IS TRUE{/s}', value: 'DUNNINGLEVELONE' },
        { description: '{s name=risks_store/comboBox/dunningleveltwo}Dunning level 2 IS TRUE{/s}', value: 'DUNNINGLEVELTWO' },
        { description: '{s name=risks_store/comboBox/dunninglevelthree}Dunning level 3 IS TRUE{/s}', value: 'DUNNINGLEVELTHREE' },
        { description: '{s name=risks_store/comboBox/encashment}Encashment IS TRUE{/s}', value: 'INKASSO' },
        { description: '{s name=risks_store/comboBox/lastOrderLess}No order before at least X days{/s}', value: 'LASTORDERLESS' },
        { description: '{s name=risks_store/comboBox/ordersLess}Quantity orders <={/s}', value: 'LASTORDERSLESS' },
        { description: '{s name=risks_store/comboBox/articleFromCategory}Article from category{/s}', value: 'ARTICLESFROM' },
        { description: '{s name=risks_store/comboBox/zipCodeIs}Delivery Zipcode IS{/s}', value: 'ZIPCODE' },
        { description: '{s name=risks_store/comboBox/streetNameContains}Delivery Streetname CONTAINS X{/s}', value: 'PREGSTREET' },
        { description: '{s name=risks_store/comboBox/billingZipCodeIs}Billing Zipcode IS{/s}', value: 'BILLINGZIPCODE' },
        { description: '{s name=risks_store/comboBox/billingStreetNameContains}Billing Streetname CONTAINS X{/s}', value: 'PREGBILLINGSTREET' },
        { description: '{s name=risks_store/comboBox/customerNumberIs}Customernumber IS{/s}', value: 'CUSTOMERNR' },
        { description: '{s name=risks_store/comboBox/lastNameContains}Lastname CONTAINS X{/s}', value: 'LASTNAME' },
        { description: '{s name=risks_store/comboBox/subShopIs}Shop IS{/s}', value: 'SUBSHOP' },
        { description: '{s name=risks_store/comboBox/subShopIsNot}Shop IS NOT{/s}', value: 'SUBSHOPNOT' },
        { description: '{s name=risks_store/comboBox/shippingAddressDifferBillingAddress}Shipping-Address != Billing-Address{/s}', value: 'DIFFER' },
        { description: '{s name=risks_store/comboBox/currencyIsoIs}Currency Iso IS{/s}', value: 'CURRENCIESISOIS' },
        { description: '{s name=risks_store/comboBox/currencyIsoIsNot}Currency Iso IS NOT{/s}', value: 'CURRENCIESISOISNOT' },
        { description: '{s name=risks_store/comboBox/articleAttributeIs}Article attribute IS (1>5){/s}', value: 'ATTRIS' },
        { description: '{s name=risks_store/comboBox/articleAttributeIsNot}Article attribute IS NOT (1>5){/s}', value: 'ATTRISNOT' }
    ]
});
//{/block}
