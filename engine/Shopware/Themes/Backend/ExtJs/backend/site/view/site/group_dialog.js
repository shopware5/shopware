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
 * @package    Site
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware UI - Site group dialog View
 */

//{namespace name=backend/site/site}

//{block name="backend/site/view/site/group_dialog"}
Ext.define('Shopware.apps.Site.view.site.GroupDialog', {
    extend: 'Enlight.app.Window',
    alias: 'widget.site-group-dialog',
    title: '{s name=createGroupWindowTitle}New group{/s}',
    width: 400,
    height: 200,
    modal: true,
    resizable: false,
    bodyPadding: 10,
    dockedItems: [{
        xtype: 'toolbar',
        ui: 'shopware-ui',
        cls: 'shopware-toolbar',
        dock: 'bottom',
        items: ['->', {
            xtype: 'button',
            cls: 'secondary',
            text: '{s name=createGroupWindowCancelButton}Cancel{/s}',
            action: 'onCreateGroupWindowClose'
        }, {
            xtype: 'button',
            cls: 'primary',
            text: '{s name=createGroupWindowSubmitButton}Create{/s}',
            action: 'onCreateGroupSubmit'
        }]
    }],
    items: [{
        xtype: 'fieldset',
        layout: 'anchor',
        defaults: {
            labelWidth: 155,
            anchor: '100%',
            xtype: 'textfield'
        },
        items: [{
            fieldLabel: '{s name=createGroupWindowDescription}Description{/s}',
            name:  'description'
        }, {
            fieldLabel: '{s name=createGroupWindowTemplateVar}Template variable{/s}',
            name: 'templateVar'
        }]
    }]
});
//{/block}
