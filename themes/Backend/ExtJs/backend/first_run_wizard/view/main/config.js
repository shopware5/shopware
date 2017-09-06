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

/**
 * Shopware First Run Wizard - Configuration tab
 *
 * @category  Shopware
 * @package   Shopware
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */

//{namespace name=backend/first_run_wizard/main}
//{block name="backend/first_run_wizard/view/main/config"}

Ext.define('Shopware.apps.FirstRunWizard.view.main.Config', {
    extend: 'Ext.container.Container',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.first-run-wizard-config',

    cls: 'first-run-wizard-config',

    /**
     * Name attribute used to generate event names
     */
    name:'config',

    snippets: {
        buttons: {
           save: '{s name=config/buttons/save}Save{/s}',
            skip: '{s name=config/buttons/skip}Skip{/s}'
        },
        content: {
            title: '{s name=config/content/title}Configuration{/s}',
            message: '{s name=config/content/message}Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.{/s}'
        },
        shopSettings: {
            title: '{s name=config/shopSettings/title}Shop settings{/s}',
            shopName: {
                label: '{s name=config/shopSettings/shopName/label}Shop name{/s}'
            },
            mail: {
                label: '{s name=config/shopSettings/mail/label}Your email address{/s}'
            },
            address: {
                label: '{s name=config/shopSettings/address/label}Address{/s}'
            },
            bankAccount: {
                label: '{s name=config/shopSettings/bankAccount/label}Bank account{/s}'
            },
            company: {
                label: '{s name=config/shopSettings/company/label}Company{/s}'
            }
        },
        themeSettings: {
            title: '{s name=config/themeSettings/title}Theme settings{/s}',
            brandPrimary: {
                label: '{s name=config/themeSettings/brandPrimaryColor/label}Primary color{/s}'
            },
            brandSecondary: {
                label: '{s name=config/themeSettings/brandSecondaryColor/label}Secondary color{/s}'
            },
            logo: {
                label: '{s name=config/themeSettings/logo/label}Shop\'s logo{/s}'
            }
        }
    },

    initComponent: function() {
        var me = this;

        me.items = [
            {
                xtype: 'container',
                border: false,
                bodyPadding: 20,
                style: 'font-weight: 700; line-height: 20px;',
                html: '<h1>' + me.snippets.content.title + '</h1>'
            },
            {
                xtype: 'container',
                border: false,
                bodyPadding: 20,
                style: 'margin-bottom: 10px;',
                html: '<p>' + me.snippets.content.message + '</p>'
            },
            me.createShopConfigForm(),
            me.createThemeConfigForm()
        ];

        me.callParent(arguments);
    },

    createThemeConfigForm: function() {
        var me = this;

        me.themeBrandPrimaryColor = Ext.create('Shopware.form.field.ColorField', {
            name: 'brand-primary',
            fieldLabel: me.snippets.themeSettings.brandPrimary.label,
            supportText: me.snippets.themeSettings.brandPrimary.support,
        });

        me.themeBrandSecondaryColor = Ext.create('Shopware.form.field.ColorField', {
            name: 'brand-secondary',
            fieldLabel: me.snippets.themeSettings.brandSecondary.label,
            supportText: me.snippets.themeSettings.brandSecondary.support,
        });

        me.themeDesktopLogo = Ext.create('Shopware.form.field.Media', {
            name: 'desktopLogo',
            fieldLabel: me.snippets.themeSettings.logo.label,
            valueField: 'virtualPath',
            minimizable: false
        });

        me.themeConfigFieldSet = Ext.create('Ext.form.FieldSet', {
            cls: Ext.baseCSSPrefix + 'base-field-set',
            title: me.snippets.themeSettings.title,
            defaults: {
                anchor:'95%',
                labelWidth:150,
                minWidth:250,
                xtype:'textfield'
            },
            items: [
                me.themeBrandPrimaryColor,
                me.themeBrandSecondaryColor,
                me.themeDesktopLogo
            ]
        });

        return me.themeConfigFieldSet;
    },

    createShopConfigForm: function() {
        var me = this;

        me.shopNameField = Ext.create('Ext.form.field.Text', {
            name: 'shopName',
            fieldLabel: me.snippets.shopSettings.shopName.label,
            supportText: me.snippets.shopSettings.shopName.support
        });

        me.mailField = Ext.create('Ext.form.field.Text', {
            name: 'mail',
            fieldLabel: me.snippets.shopSettings.mail.label,
            supportText: me.snippets.shopSettings.mail.support
        });

        me.addressField = Ext.create('Ext.form.field.TextArea', {
            name: 'address',
            fieldLabel: me.snippets.shopSettings.address.label,
            supportText: me.snippets.shopSettings.address.support
        });

        me.bankAccountField = Ext.create('Ext.form.field.TextArea', {
            name: 'bankAccount',
            fieldLabel: me.snippets.shopSettings.bankAccount.label,
            supportText: me.snippets.shopSettings.bankAccount.support
        });

        me.companyField = Ext.create('Ext.form.field.Text', {
            name: 'company',
            fieldLabel: me.snippets.shopSettings.company.label,
            supportText: me.snippets.shopSettings.company.support
        });

        me.shopConfigFieldSet = Ext.create('Ext.form.FieldSet', {
            cls: Ext.baseCSSPrefix + 'base-field-set',
            title: me.snippets.shopSettings.title,
            defaults: {
                anchor:'95%',
                labelWidth:150,
                minWidth:250,
                xtype:'textfield'
            },
            items: [
                me.shopNameField,
                me.mailField,
                me.addressField,
                me.bankAccountField,
                me.companyField
            ]
        });

        return me.shopConfigFieldSet;
    },

    getButtons: function() {
        var me = this;

        return {
            next: { text: me.snippets.buttons.save },
            extraButtonSettings: {
                text: me.snippets.buttons.skip,
                cls: 'primary',
                name: 'skip-button',
                width: 180,
                handler: function() {
                    me.fireEvent('navigate-next');
                }
            }
        };
    }
});

//{/block}
