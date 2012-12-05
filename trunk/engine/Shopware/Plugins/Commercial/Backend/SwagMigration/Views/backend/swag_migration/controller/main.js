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
 * @package    Migration
 * @subpackage Controller
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Daniel Nögel
 * @author     $Author$
 */

/**
 *
 */
//{namespace name=backend/swag_migration/main}
//{block name="backend/swag_migration/controller/main"}
Ext.define('Shopware.apps.SwagMigration.controller.Main', {

    /**
     * The parent class that this class extends.
     * @string
     */
    extend: 'Ext.app.Controller',

    /**
     * Class property which holds the main application if it is created
     *
     * @default null
     * @object
     */
    mainWindow: null,

    /**
     * A template method that is called when your application boots.
     * It is called before the Application's launch function is executed
     * so gives a hook point to run any code before your Viewport is created.
     *
     * @return void
     */
    init: function () {
        var me = this;

        me.subApplication.profileStore = me.getStore('Shopware.apps.SwagMigration.store.Profile').load();
        // these stores needs to be loaded dynamically depending on the database credentials
        me.subApplication.databaseStore = me.getStore('Shopware.apps.SwagMigration.store.Database');
        me.subApplication.mappingStoreLeft = me.getStore('Shopware.apps.SwagMigration.store.MappingLeft');
        me.subApplication.mappingStoreRight = me.getStore('Shopware.apps.SwagMigration.store.MappingRight');

        me.mainWindow = me.getView('main.Window').create({
            profileStore: me.subApplication.profileStore,
            databaseStore: me.subApplication.databaseStore,
            mappingStoreLeft: me.subApplication.mappingStoreLeft,
            mappingStoreRight: me.subApplication.mappingStoreRight
        });
        me.subApplication.setAppWindow(me.mainWindow);

        me.callParent(arguments);
    }

});
//{/block}
