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
 * @package    Shopware.global.ErrorReporter
 * @subpackage Controller
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/base/component/global_error_logger}

//{block name="backend/base/component/global_error_loger"}
Ext.define('Shopware.global.ErrorReporter', {

    /**
     * The parent class that this class extends
     *
     * @type String
     * @default Ext.app.Controller
     */
    extend: 'Ext.app.Controller',

    /**
     * Component main window
     *
     * @type Ext.window.Window
     * @default null
     */
    mainWindow: null,

    /**
     * Initializes the event listener. Please note that
     * this method will be fired in the `launch()`-method
     * of the global `Shopware.app.Application`
     *
     * @param { Shopware.app.Application } cmp - Application which fires the event
     * @returns { Void }
     */
    bindEvents: function(cmp) {
        var me = this;
        cmp.on('Ext.Loader:xhrFailed', me.onXhrErrorOccurs, me);
    },

    /**
     * Event listener method which will be fired when the loader couldn't
     * load a module properly.
     *
     * @this Shopware.global.ErrorReporter
     * @event Ext.Loader:xhrFailed
     * @param { XMLHttpRequest } xhr
     * @param { Object } namespace
     * @param { String } requestType
     * @returns { Void }
     */
    onXhrErrorOccurs: function(xhr, namespace, requestType) {
        var me = this;

        me.mainWindow = Ext.create('Ext.window.Window', {
            width: 800,
            height: 600,
            modal: true,
            title: 'Error Reporter',
            dockedItems: [ me.createActionToolbar(namespace) ] ,
            renderTo: Ext.getBody(),
            bodyPadding: 15,
            items: [
                me.createErrorInformation(xhr, namespace, requestType),
                me.createErrorDescription(xhr),
                me.createErrorFilesList(namespace)
            ]
        }).show();
    },

    /**
     * Creates the basic error information fieldset
     *
     * @param { XMLHttpRequest } xhr
     * @param { Object } namespace
     * @param { String } requestType
     * @returns { Object } fieldset configuration object
     */
    createErrorInformation: function(xhr, namespace, requestType) {

        return {
            xtype: 'fieldset',
            title: 'Fehler-Informationen',
            layout: 'column',
            defaults: { xtype: 'container', columnWidth: 0.5, layout: 'anchor', defaults: {
                anchor: '100%', readOnly: true, xtype: 'displayfield', labelWidth: 155, labelStyle: 'margin-top: 0'
            } },
            items: [{
                items: [{
                    fieldLabel: 'Modul',
                    value: namespace.prefix
                }, {
                    fieldLabel: 'Request-Pfad',
                    value: namespace.path
                }]
            }, {
                margin: '0 0 0 15',
                items: [{
                    fieldLabel: 'HTTP-Fehlermeldung',
                    value: xhr.statusText
                }, {
                    fieldLabel: 'HTTP-Fehlercode',
                    value: Ext.String.format('[0] / [1]', xhr.status, requestType.toUpperCase())
                }]
            }]
        };
    },

    /**
     * Creates a fieldset with the error description
     *
     * @param { XMLHttpRequest } xhr
     * @returns { Object } fieldset configuration object
     */
    createErrorDescription: function(xhr) {
        return {
            xtype: 'fieldset',
            title: 'Fehler-Beschreibung',
            layout: 'anchor',
            height: 175,
            items: [{
                xtype: 'textarea',
                anchor: '100%',
                height: 125,
                value: xhr.responseText
            }]
        }
    },

    /**
     * Creates a grid (with the associated column model and store) which
     * displays the loaded files, grouped by it's type.
     *
     * @param { Object } namespace
     * @returns { Object } Grid panel configuration
     */
    createErrorFilesList: function(namespace) {
        var data = [];

        var getFileType = function(path) {
            var regEx = /^([a-zA-Z]+)\//,
                result = regEx.exec(path);

            if(!result) {
                return 'Unbekannter Typ';
            }

            result = result[1];
            return result.charAt(0).toUpperCase() + result.slice(1);
        };

        Ext.each(namespace.classNames, function(cls, i) {
            data.push({
                id: i + 1,
                name: cls,
                path: namespace.files[i],
                type: getFileType(namespace.files[i])
            });
        });

        var store = Ext.create('Ext.data.Store', {
            fields: [ 'id', 'name', 'path', 'type' ],
            groupField: 'type',
            data: data
        });

        return {
            xtype: 'gridpanel',
            store: store,
            title: 'Modul-Dateien',
            height: 215,
            features: [{
                ftype:'grouping',
                groupHeaderTpl: '{literal}Typ: {name} ({rows.length}){/literal}'
            }],
            columns: [{
                dataIndex: 'id',
                header: '#',
                width: 35
            }, {
                dataIndex: 'name',
                header: 'Klassen-Name',
                flex: 1,
                renderer: function(val) {
                    return '<strong>' + val + '</strong>';
                }
            }, {
                dataIndex: 'path',
                header: 'Pfad',
                flex: 1
            }, {
                dataIndex: 'type',
                header: 'Typ',
                flex: 1
            }]
        };
    },

    /**
     * Creates a toolbar with action buttons which is located
     * at the bottom of the window.
     *
     * @param { Object } namespace
     * @returns { Ext.toolbar.Toolbar } Instance of the Ext.toolbar.Toolbar
     */
    createActionToolbar: function(namespace) {
        var me = this;

        return Ext.create('Ext.toolbar.Toolbar', {
            dock: 'bottom',
            padding: 5,
            items: [ '->', {
                xtype: 'button',
                text: 'Abbrechen',
                cls: 'secondary',
                handler: function() {
                    me.mainWindow.destroy();
                }
            }, {
                xtype: 'button',
                text: 'Modul erneut laden',
                cls: 'primary',
                handler: function() {
                    Ext.require(namespace.classNames);
                    me.mainWindow.destroy();
                }
            }]
        })
    }
});
//{/block}