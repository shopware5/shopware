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
 * @package    Partner
 * @subpackage Model
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware Model -  partner list backend module.
 *
 * The partner statistic_list model of the partner module holds the partner statistic data for the chart
 */
//{block name="backend/partner/model/statisticChart"}
Ext.define('Shopware.apps.Partner.model.StatisticChart', {
    /**
    * Extends the standard ExtJS 4
    * @string
    */
    extend : 'Ext.data.Model',
    /**
    * The fields used for this model
    * @array
    */
    fields : [
        //{block name="backend/partner/model/statisticChart/fields"}{/block}
        { name:'netTurnOver', type:'float' },
        { name:'date', type:'date', dateFormat:'Y-m-d' }
    ],
    /**
    * If the name of the field is 'id' extjs assumes autmagical that
    * this field is an unique identifier.
    */
    idProperty : 'id',
    /**
    * Configure the data communication
    * @object
    */
    proxy : {
        type : 'ajax',
        api:{
            read:   '{url action=getChartData}'
        },
        reader : {
            type : 'json',
            root : 'data',
            totalProperty: 'totalCount'
        }
    }
});
//{/block}
