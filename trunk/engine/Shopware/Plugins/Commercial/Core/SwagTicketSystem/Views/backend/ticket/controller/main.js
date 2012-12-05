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
 * @package    Ticket
 * @subpackage Controller
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Stephan Pohl
 * @author     $Author$
 */
//{namespace name=backend/ticket/main}
//{block name="backend/ticket/controller/main"}
Ext.define('Shopware.apps.Ticket.controller.Main', {

    /**
     * The parent class that this class extends.
     * @string
     */
    extend:'Ext.app.Controller',

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
    init:function () {
        var me = this;

        me.subApplication.overviewStore = me.subApplication.getStore('List').load();
        me.subApplication.statusStore = me.subApplication.getStore('Status').load();
        me.subApplication.submissionStore = me.subApplication.getStore('Submission').load();
        me.subApplication.submissionDetailStore = me.subApplication.getStore('SubmissionDetail');
        me.subApplication.typesStore = me.subApplication.getStore('Types').load();
        me.subApplication.statusComboStore = me.subApplication.getStore('StatusCombo');
        me.subApplication.formsStore = me.subApplication.getStore('Forms').load();
        me.subApplication.localeStore = me.subApplication.getStore('Locale').load();

        me.subApplication.employeeStore = me.subApplication.getStore('Employee').load({
            callback: function() {
                me.mainWindow = me.getView('main.Window').create({
                    overviewStore: me.subApplication.overviewStore,
                    employeeStore: me.subApplication.employeeStore,
                    statusStore: me.subApplication.statusStore,
                    submissionStore: me.subApplication.submissionStore,
                    submissionDetailStore: me.subApplication.submissionDetailStore,
                    typesStore: me.subApplication.typesStore,
                    statusComboStore: me.subApplication.statusComboStore,
                    formsStore: me.subApplication.formsStore,
                    localeStore: me.subApplication.localeStore
                });
            }
        });
    }

});
//{/block}
