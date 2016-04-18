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

        me.blockMessage = Shopware.Notification.createBlockMessage('', 'error');
        me.blockMessage.hide();

        /* {literal} */
        me.licenseStatus = Ext.create('Ext.Component', {
            tpl: new Ext.XTemplate(
                '<div style="text-align:left;width:100%;">' +
                    '<p class="x-form-item-label">' +
                      me.infoLabels.heading +
                    '</p>' +
                    '<div class="x-panel" style="width:100%;display:{display};">' +
                        '<br />'+
                        '<div style="float:left;height: 100px;">'+
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
                        '<div style="text-align: right;"><img src="{imageSrc}" /></div>' +
                    '</div>' +
                    '<br style="clear:left;" />' +
                '</div>',
                {
                    formatDate: function(licenseDate) {
                        var dt = Ext.Date.parse(licenseDate, 'Ymd');
                        return Ext.Date.format(dt, me.dateFormat);
                    }
                }
            ),
            data: {
                imageSrc: me.getImage(false),
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
     * Returns a base64 encoded image if true is set as parameter.
     * Empty string otherwise. The image is used as visual success
     * confirmation on successful license validation.
     *
     * @bool success
     * @returns string
     */
    getImage: function (success) {
        var me = this,
            successImage = '{s name=images/success}data:image/png;base64,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{/s}';
        return success ? successImage : '';
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
                            imageSrc: '',
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
                imageSrc: '',
                license: jsonData.licenseData.label,
                licenseDate: jsonData.licenseData.creation,
                host: jsonData.licenseData.host,
                display: 'initial'
            });
            me.licenseField.setValue(jsonData.licenseData.license);
            me.blockMessage.items.items[0].update({
                text: '{s name=errors/license/host}License key is not valid for domain{/s}'
            });
            me.blockMessage.show();
            return;
        }

        me.blockMessage.hide();

        if(jsonData.success === false){
            me.savedLicenseData = me.emptyLicenseData;
            me.licenseStatus.update({
                imageSrc: '',
                license: '',
                licenseDate: '',
                host: '',
                display: 'none'
            });
            return;
        }

        me.savedLicenseData = jsonData;
        me.licenseField.setValue(jsonData.licenseData.license);
        me.licenseStatus.update({
            imageSrc: me.getImage(jsonData.success),
            license: jsonData.licenseData.label,
            licenseDate: jsonData.licenseData.creation,
            host: jsonData.licenseData.host,
            display: 'initial'
        });

        me.switchEditionIcon(jsonData.licenseData.edition);

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
