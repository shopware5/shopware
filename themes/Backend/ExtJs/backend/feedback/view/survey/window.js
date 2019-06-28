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
 */

//{namespace name=backend/feedback/view/survey}

/**
 * Shopware UI - Feedback preview Window
 *
 * This file contains the business logic for the User Manager module. The module
 * handles the whole administration of the backend users.
 */
//{block name="backend/feedback/view/survey/window"}
Ext.define('Shopware.apps.Feedback.view.survey.Window', {
    extend: 'Enlight.app.Window',
    title: '{s name=window/title}{/s}',
    alias: 'widget.installation-survey-window',
    border: false,
    layout: 'fit',
    autoShow: true,
    height: 650,
    width: 550,
    maximizable: false,
    resizable: false,
    stateful: true,
    stateId: 'installation-survey-window',

    /**
     * Initializes the component and builds up the main interface
     *
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.items = [{
            xtype : "component",
            autoEl : {
                tag : "iframe",
                src : Ext.String.format('https://api.shopware.com/survey/[0]/firstinstallation', Ext.userLanguage !== 'de' ? 'en' : Ext.userLanguage)
            }
        }];

        me.checkbox = Ext.create('Ext.form.field.Checkbox', {
            padding: '0 0 0 5px',
            itemId: 'disableInstallationSurvey',
            width: 150,
            boxLabel: '{s name=window/do_not_show_again}{/s}'
        });

        me.cancelButton = Ext.create('Ext.button.Button', {
            cls: 'primary',
            text: '{s name=window/close}{/s}',
            handler: function() {
                me.close();
            }
        });

        me.dockedItems = [{
            xtype: 'toolbar',
            dock: 'bottom',
            items:[me.checkbox, '->', me.cancelButton]
        }];

        me.callParent(arguments);
    }
});
//{/block}
