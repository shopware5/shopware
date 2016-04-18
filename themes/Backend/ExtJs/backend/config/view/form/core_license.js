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

//{namespace name=backend/config/view/core_license}

/**
 * Shopware UI - Core-license management
 *
 * The customer is able to insert the shopware license data,
 * e.g. the shopware Core license key.
 *
 * If the license key is valid, the shopware backend icon is
 * switched accordingly and the license information is shown
 * to the customer.
 *
 */
//{block name="backend/config/view/form/core_license"}
Ext.define('Shopware.apps.Config.view.form.CoreLicense', {
    extend: 'Shopware.apps.Config.view.base.Form',
    alias: 'widget.config-form-corelicense',
    layout: 'anchor',
    bodyPadding: 20,

    /**
     * Unfilled license data object, used in case of
     * invalid license key check or license removal.
     */
    savedLicenseData: {
        'success': false,
        'message': '',
        'errorType': '',
        'licenseData': {
            'label': '',
            'module': '',
            'creation': '',
            'edition': '',
            'host': '',
            'type': '',
            'license': ''
        }
    },

    /**
     * The translatable label strings for license information.
     * Kept in seperate object to easier prevent interference with smarty compiler.
     */
    infoLabels: {
        heading: '{s name=info/heading}Add or change and validate your shopware license.{/s}',
        license: '{s name=info/license}License{/s}',
        error: '{s name=errors/common/heading}Error{/s}',
        registeredTo: '{s name=info/registeredTo}Registered to{/s}',
        createdAt: '{s name=info/createdAt}Created at{/s}'
    },

    /**
     * All known shopware edition css classes,
     * used when switching the backend icon.
     */
    editions: [
        'shopware-ce',
        'shopware-pe',
        'shopware-pp',
        'shopware-ee',
        'shopware-eb',
        'shopware-ec'
    ],

    /**
     * Used to keep the date format translatable for
     * different countries.
     */
    dateFormat: '{s name=date_format}m/d/Y{/s}',

    constructor: function(config) {
        var me = this;
        me.emptyLicenseData = me.savedLicenseData;

        Ext.Ajax.request({
            url: '{url controller="CoreLicense" action="loadSavedLicense"}',
            method: 'GET',
            async  : false,
            success: function(result){
                me.savedLicenseData = Ext.decode(result.responseText);
            }
        });

        me.callParent(arguments);
    },

    getItems: function() {
        var me = this;

        me.licenseField = Ext.create('Ext.form.field.TextArea', {
            name: 'license',
            height: 250,
            width: '100%',
            anchor: '100%',
            fieldStyle: 'font: 13px monospace !important;',
            cols: 4,
            fieldLabel: '',
            supportText: ''
        });

        me.blockMessage = Shopware.Notification.createBlockMessage('', 'success');
        me.blockMessage.hide();

        /* {literal} */
        me.licenseStatus = Ext.create('Ext.Component', {
            tpl: new Ext.XTemplate(
                '<div style="text-align:left;width:100%;">' +
                    '<p class="x-form-item-label">' +
                      me.infoLabels.heading +
                    '</p>' +
                    '<br />'+
                    '<div class="x-panel" style="width:100%;display:{display};">' +
                        '<div style="height: 100px;">'+
                            '<table style="width:400px;">' +
                                '<tr>' +
                                    '<td>' + me.infoLabels.license + ':</td>' +
                                    '<td><b>{license}</b></td>' +
                                '</tr>'+
                                '<tr>' +
                                    '<td>' + me.infoLabels.registeredTo + ':</td>' +
                                    '<td>{host}</td>' +
                                '</tr>'+
                                '<tr>' +
                                    '<td>' + me.infoLabels.createdAt + ':</td>' +
                                    '<td>{[this.formatDate(values.licenseDate)]}</td>' +
                                '</tr>'+
                            '</table>'+
                        '</div>' +
                    '</div>' +
                '</div>',
                {
                    formatDate: function(licenseDate) {
                        var dt = Ext.Date.parse(licenseDate, 'Ymd');
                        return Ext.Date.format(dt, me.dateFormat);
                    }
                }
            ),
            data: {
                license: '',
                licenseDate: '',
                host: '',
                display: 'none'
            }
        });
        /* {/literal} */

        me.showLicenseData(me.savedLicenseData);

        return [
            me.blockMessage,
            me.licenseStatus,
            {
                xtype: 'fieldset',
                title: me.infoLabels.license,
                defaults: {
                    anchor: '100%',
                    labelWidth: 250,
                    xtype: 'textfield'
                },
                items: [
                    me.licenseField
                ]
            },
            {
                xtype: 'container',
                layout: {
                    type: 'hbox',
                    pack: 'end'
                },
                items: [
                    {
                        xtype: 'button',
                        cls: 'small',
                        text: '{s name=buttons/uninstallLicenseLabel}Uninstall{/s}',
                        name: 'license-delete-button',
                        handler: Ext.bind(me.handleUninstallButtonClick, me)
                    },
                    {
                        xtype: 'button',
                        cls: 'small primary',
                        text: '{s name=buttons/addLicenseLabel}Save{/s}',
                        name: 'license-set-button',
                        handler: Ext.bind(me.setNewLicense, me)
                    }
                ]
            }
        ];
    },

    /**
     * Displays a confirmation dialog before uninstallation takes place.
     * Triggered when the uninstall button is clicked.
     */
    handleUninstallButtonClick: function(){
        var me = this,
            licenseData = me.savedLicenseData.licenseData;

        // Check if a license is present
        if (!licenseData.host || licenseData.host.length === 0) {
            return;
        }

        Ext.MessageBox.confirm(
            '{s name=mesagebox/uninstall/title}Uninstallation confirmation{/s}',
            '{s name=mesagebox/uninstall/body}Are you sure you want to remove your license information from this shopware installation?{/s}',
            function (response) {
                if (response !== 'yes') {
                    return;
                }

                me.uninstallLicense();
            });
    },

    /**
     * Deletes the license data from database via backend controller,
     * removes the license information content which is visible to the user
     * empty afterwards.
     */
    uninstallLicense: function () {
        var me = this,
            jsonData,
            licensePresent,
            licenseData = me.savedLicenseData.licenseData;

        Ext.Ajax.request({
            url: '{url controller="CoreLicense" action="uninstallLicense"}',
            method: 'GET',
            success: function (result) {
                jsonData = Ext.decode(result.responseText);

                licensePresent = licenseData.host && licenseData.host.length > 0;
                
                if (jsonData.success) {
                    me.savedLicenseData = me.emptyLicenseData;
                    me.switchEditionIcon('ce');
                    me.licenseField.setValue('');
                    if (licensePresent) {
                        me.licenseStatus.update({
                            license: '{s name=info/license/unlicensed}Not licensed{/s}',
                            licenseDate: '',
                            host: '',
                            display: 'initial'
                        });
                    }
                    me.blockMessage.hide();
                } else {
                    Shopware.Notification.createGrowlMessage(
                        '{s name=errors/common/heading}Error{/s}',
                        '{s name=errors/common/common}An unknown error occured.{/s}',
                        '{s name=errors/common/title}Core License{/s}'
                    );
                }
            }
        });
    },

    /**
     * Tries to validate and set new license information provided
     * by the customer.
     */
    setNewLicense: function(){
        var me = this,
            license = me.licenseField.getValue().trim();

        me.licenseField.setDisabled(true);

        if(!license){
            Shopware.Notification.createGrowlMessage(
                '{s name=errors/common/heading}Error{/s}',
                '{s name=errors/license/common}License key could not be validated{/s}',
                '{s name=errors/common/title}Core License{/s}'
            );
            me.licenseField.setDisabled(false);
            return;
        }

        me.checkLicense(license);

        me.licenseField.setDisabled(false);
    },

    /**
     * Performs a license validation request and
     * updates the license information on success,
     * shows error otherwise.
     *
     * @string licenseString
     */
    checkLicense: function(licenseString){
        var me = this,
            jsonData;

        Ext.Ajax.request({
            url: '{url controller="CoreLicense" action="checkLicense"}',
            method: 'POST',
            params: {
                licenseString: licenseString
            },
            success: function (result) {
                jsonData = Ext.decode(result.responseText);
                if (jsonData.success === true) {
                    me.showLicenseData(jsonData);
                } else {
                    Shopware.Notification.createGrowlMessage(
                        '{s name=errors/common/heading}Error{/s}',
                        me.getErrorMessage(jsonData.errorType),
                        '{s name=errors/common/title}Core License{/s}'
                    );
                }
            }
        });
    },

    /**
     * Performs the actual task of license information display by
     * updating the ExtJs xTemplate data using the provided object.
     *
     * @object jsonData
     */
    showLicenseData: function (jsonData) {
        var me = this;

        if (jsonData.errorType && jsonData.errorType == 'LicenseHostException') {
            me.switchEditionIcon('ce');
            me.licenseStatus.update({
                license: jsonData.licenseData.label,
                licenseDate: jsonData.licenseData.creation,
                host: jsonData.licenseData.host,
                display: 'initial'
            });
            me.licenseField.setValue(jsonData.licenseData.license);
            me.setBlockMessage('{s name=errors/license/host}License key is not valid for domain{/s}', 'error');
            me.blockMessage.show();
            return;
        }

        if(jsonData.success === false){
            me.savedLicenseData = me.emptyLicenseData;
            me.licenseStatus.update({
                license: '',
                licenseDate: '',
                host: '',
                display: 'none'
            });
            me.blockMessage.hide();
            return;
        }

        me.savedLicenseData = jsonData;
        me.licenseField.setValue(jsonData.licenseData.license);
        me.licenseStatus.update({
            license: jsonData.licenseData.label,
            licenseDate: jsonData.licenseData.creation,
            host: jsonData.licenseData.host,
            display: 'initial'
        });

        me.setBlockMessage('{s name=message/validLicense}Your license was successfully validated{/s}', 'success');
        me.blockMessage.show();

        me.switchEditionIcon(jsonData.licenseData.edition);
    },

    /**
     * Update the blockmessage content and type.
     * Doesn't change the blockmessage visibility.
     *
     * @param text
     * @param type
     */
    setBlockMessage: function (text, type) {
        var me = this,
            inner = me.blockMessage.items.first();

        inner.update({
            text: text,
            type: type
        });

        Ext.each(['notice', 'error', 'success'], function (element) {
            inner.removeCls(element);
        });
        inner.addCls(type);
    },

    /**
     * Switches the shopware icon in the backend according
     * to the string provided. If empty parameter is provided,
     * shopware community edition is assumed.
     *
     * @string edition
     */
    switchEditionIcon: function (edition) {
        var me = this,
            classes = Ext.getBody().getAttribute('class').split(" ");

        if(!edition){
            edition = 'ce';
        }

        classes.forEach(function (element) {
            if (Ext.Array.indexOf(me.editions, element) > -1) {
                Ext.getBody().removeCls(element);
                Ext.getBody().addCls('shopware-' + edition.toLowerCase());
            }
        });
    },

    /**
     * Translates an error string from the backend controller to a more
     * human readable and translatable error message.
     * 
     * @string errorType
     * @returns string
     */
    getErrorMessage: function (errorType) {
        var me= this,
            message;

        switch (errorType) {
            case 'LicenseProductKeyException':
                message = '{s name=errors/license/product_key}License key does not match a commercial shopware edition{/s}';
                break;
            case 'LicenseHostException':
                message = '{s name=errors/license/host}License key is not valid for domain{/s}';
                break;
            default:
                message = '{s name=errors/license/common}License key could not be validated{/s}';
        }

        return message;
    }
});
//{/block}
