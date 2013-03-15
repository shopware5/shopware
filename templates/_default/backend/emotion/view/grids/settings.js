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
 * @package    Emotion
 * @subpackage View
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/emotion/view/detail}

/**
 * Shopware UI - Media Manager Main Window
 *
 * This file contains the business logic for the User Manager module. The module
 * handles the whole administration of the backend users.
 */
//{block name="backend/emotion/view/grids/settings"}
Ext.define('Shopware.apps.Emotion.view.grids.Settings', {
    extend: 'Enlight.app.Window',
    alias: 'widget.emotion-view-grids-settings',
    width: 800,
    height: 600,
    autoShow: true,

    /**
     * Snippets which are used by this component.
     * @Object
     */
    snippets: {
        title_new: '{s name=grids/settings/title_new}Create new grid{/s}',
        title_edit: '{s name=grids/settings/title_edit}Edit existing grid{/s}',
        fieldset_title: '{s name=grids/settings/fieldset}Define grid{/s}'
    },

    /**
     * Initializes the component and builds up the main interface
     *
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.title = me.snippets[(me.hasOwnProperty('record') ? 'title_edit' : 'title_new' )];

        me.formPanel = Ext.create('Ext.form.Panel', {
            layout: 'fit',
            bodyPadding: 20,
            border: 0,
            bodyBorder: 0,
            items: [{
                xtype: 'fieldset',
                title: me.snippets.fieldset_title
            }]
        });
        me.items = [ me.formPanel ];

        me.callParent(arguments);
    }
});
//{/block}