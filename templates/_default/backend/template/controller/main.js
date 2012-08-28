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
 * @subpackage Controller
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/template/controller/main}
//{block name="backend/template/controller/main"}
Ext.define('Shopware.apps.Template.controller.Main', {

    /**
     * Extend from the standard ExtJS 4
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
     * Define references for the different parts of our application. The
     * references are parsed by ExtJS and Getter methods are automatically created.
     *
     * @array
     */
    refs: [
        { ref: 'mediaPanel', selector: 'template-main-media' }
    ],

    /**
     * Creates the necessary event listener for this
     * specific controller and opens a new Ext.window.Window
     *
     * @return void
     */
    init: function() {
        var me = this;

        me.control({
            'template-main-media': {
                enableTemplate:  me.onEnableTemplate,
                previewTemplate: me.onPreviewTemplate,
                resetPreview:    me.onResetPreview
            }
        });

        me.mainWindow = me.getView('main.Window').create({
            templateStore: me.getStore('Template')
        });

        me.mainWindow.show();

        me.callParent(arguments);
    },

    onResetPreview: function() {
        var me    =   this,
            store = me.getStore('Template');

        me.getMediaPanel().previewMessage.hide();

        store.each(function(record) {
            record.set('isPreviewed', false);
        });

        var record = store.findRecord('isEnabled', true);
        record.set('isPreviewed', true);

        me.getMediaPanel().setupButtonState();

        Ext.Ajax.request({
            url: '{url controller="template" action="previewTemplate"}',
            params: {
                template: record.get('basename')
            }
        });

        if(me.win) {
            me.win.close();
            me.win = null;
        }
    },

    /**
     * @event enableTemplate
     * @param [object] record
     */
    onEnableTemplate: function(record) {
        var me = this;

        me.getStore('Template').each(function(record) {
            record.set('isEnabled', false);
        });

        me.getMediaPanel().previewMessage.hide();
        record.set('isEnabled', true);

        record.save({
            callback: function() {
                me.getStore('Template').each(function(record) {
                    record.set('isEnabled', false);
                });
                record.set('isEnabled', true);

                me.getStore('Template').each(function(record) {
                    record.set('isPreviewed', false);
                });

                record.set('isPreviewed', true);
                me.getMediaPanel().setupButtonState();
            }
        });

    },

    /**
     * @event onPreviewTemplate
     * @param [object] record
     */
    onPreviewTemplate: function(record) {
        var me = this;

        if (record.get('isEnabled') || record.get('isPreviewed')) {
            me.onResetPreview();
            return;
        }

        me.getStore('Template').each(function(record) {
            record.set('isPreviewed', false);
        });
        record.set('isPreviewed', true);

        me.getMediaPanel().setupButtonState();
        me.getMediaPanel().previewMessage.show();

        var url = '{url action=previewTemplate}'
            //    + '&shopId=' + shopId
            + '?template=' + record.get('basename');

        if(!me.win) {
            me.win = window.open(url);
        } else {
            me.win.location.href = url;
        }
        /*
        Ext.Ajax.request({
            url: '{url controller="template" action="previewTemplate"}',
            params: {
                basename: record.get('basename')
            }
        });
        */
    }
});
//{/block}
