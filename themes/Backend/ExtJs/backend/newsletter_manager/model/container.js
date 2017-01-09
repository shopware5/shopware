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
 * @package    NewsletterManager
 * @subpackage Model
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware Model - Container model
 * This model holds a container. A container is owned by a newsletter and owns a text model
 */
//{block name="backend/newsletter_manager/model/container"}
Ext.define('Shopware.apps.NewsletterManager.model.Container', {
    /**
     * Extends the standard Ext Model
     * @string
     */
    extend: 'Ext.data.Model',

    /**
     * The fields used for this model
     * @array
     */
    fields: [
        //{block name="backend/newsletter_manager/model/sender/fields"}{/block}
        { name: 'id', type: 'int', useNull: true },
        { name: 'newsletterId', type: 'int' },
        { name: 'value', type: 'string' },
        { name: 'type', type: 'string' },
        { name: 'description', type: 'string' },
        { name: 'position', type: 'int' }
    ],

    /**
     * Define the associations of the mailing model.
     * @array
     */
    associations:[
        // Right now only text containers are supported, therefore only a text-association is implemented
        { type:'hasMany', model:'Shopware.apps.NewsletterManager.model.ContainerTypeText', name:'getText', associationKey:'text' }
    ]

});
//{/block}
