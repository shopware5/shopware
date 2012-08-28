/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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
 * @package    Template
 * @subpackage Model
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

//{block name="backend/template/model/template"}
Ext.define('Shopware.apps.Template.model.Template', {

    /**
     * Extends the standard ExtJS 4
     * @string
     */
    extend: 'Ext.data.Model',

    /**
     * The fields used for this model
     *
     * @array
     */
    fields : [
		//{block name="backend/template/model/template/fields"}{/block}
        { name: 'name',                     type: 'string' },
        { name: 'basename',                 type: 'string' },
        { name: 'description',              type: 'string' },
        { name: 'author',                   type: 'string' },
        { name: 'license',                  type: 'string' },
        { name: 'isEnabled',                type: 'boolean' },
        { name: 'isPreviewed',              type: 'boolean' },
        { name: 'isEsiCompatible',          type: 'boolean' },
        { name: 'isStyleAssistCompatible',  type: 'boolean' },
        { name: 'isEmotionsCompatible',     type: 'boolean' }
    ],

    /**
     * Configure the data communication
     * @object
     */
    proxy: {
        type: 'ajax',

        /**
         * Configure the url mapping for the different
         * store operations based on
         * @object
         */
        api: {
            read:   '{url controller="template" action="getTemplates"}',
            update: '{url controller="template" action="updateTemplate"}'
        },

        /**
         * Configure the data reader
         * @object
         */
        reader: {
            type: 'json',
            root: 'data'
        }
    }
});
//{/block}
