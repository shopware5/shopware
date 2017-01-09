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
 * @subpackage Model
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware UI - Vote model
 *
 * This model contains a single vote.
 */
//{block name="backend/vote/model/vote"}
Ext.define('Shopware.apps.Vote.model.Vote', {

    /**
    * Extends the standard ExtJS 4
    * @string
    */
    extend: 'Ext.data.Model',
    /**
    * The fields used for this model
    * @array
    */
    fields: [
        //{block name="backend/vote/model/vote/fields"}{/block}
        'id', 'active',
        { name : 'datum', type : 'date' },
        'name', 'headline', 'points', 'articleName', 'comment', 'answer', 'answer_datum', 'email'],
    /**
    * Configure the data communication
    * @object
    */
    proxy: {
        type: 'ajax',
        /**
        * Configure the url mapping for the different
        * @object
        */
        api: {
            //read out all articles
            read: '{url controller="vote" action="getVotes"}',
            //edit articles
            update: '{url controller="vote" action="editVote"}',
            //funcion to delete articles
            destroy: '{url controller="vote" action="deleteVote"}'
        },
        /**
        * Configure the data reader
        * @object
        */
        reader: {
            type: 'json',
            root: 'data',
            //total values, used for paging
            totalProperty: 'total'
        }
    }
});
//{/block}
