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
 * @package    ModelManager
 * @subpackage View
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware UI - ModelManager
 *
 * Defines a closable tab with a basic form/textarea.
 * This will contain all generated code in a basic, stretched textarea.
 */
Ext.define('Shopware.apps.ModelManager.view.main.Tab', {
    extend: 'Ext.container.Container',
    closable: true,

    /**
     * called on initialisation
     */
    initComponent: function() {
        var me = this;

        me.items = me.createItems();

        me.title = me.record.data.tableName;

        me.formPanel.loadRecord(me.record);

        me.callParent(arguments);
    },
    /**
     * get a form and a textarea which will hold the code
     */
    createItems: function () {
        var me = this;

        me.formPanel = Ext.create('Ext.form.Panel', {
            region: 'center',
            height: '100%',
            anchor: '100%',
            items: [
                {
                    xtype: 'textarea',
                    name: 'content',
                    region: 'center',
                    autoScroll: 'auto',
                    anchor: '100%',
                    height: '100%'
                }
            ]
        });

        return [ me.formPanel ];
    }
});