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
 * @package    Plugins
 * @subpackage Staging
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Stefan Hamann
 * @author     $Author$
 */

/**
 * Shopware UserManager - User listing model
 *
 * todo@all: Documentation
 */
//{block name="backend/staging/model/status"}
Ext.define('Shopware.apps.Staging.model.Status', {
	extend: 'Ext.data.Model',
	fields: [
		//{block name="backend/staging/model/status/fields"}{/block}
		'done', 'totalQueueJobs','doneQueueJobs','jobId','offset'
    ],
	proxy: {
		type: 'ajax',
		api: {
			read: '{url controller="Staging" action="executeJob"}',
            update: '{url controller="Staging" action="executeJob"}',
            create: '{url controller="Staging" action="executeJob"}'
		},
		reader: {
			type: 'json',
			root: 'data'
		}
	}
});
//{/block}