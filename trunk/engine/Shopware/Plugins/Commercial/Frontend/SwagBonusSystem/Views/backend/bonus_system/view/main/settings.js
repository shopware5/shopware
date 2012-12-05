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
 * @package    BonusSystem
 * @subpackage Main
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     shopware AG
 */

//{namespace name=backend/bonus_system/view/main}
//{block name="backend/bonus_system/view/settings"}
Ext.define('Shopware.apps.BonusSystem.view.main.Settings', {
    extend: 'Ext.form.Panel',
    alias: 'widget.bonusSystem-main-settings',
    autoScroll: true,
    monitorValid: true,

    /**
     * Contains all snippets for the component
     * @object
     */
    snippets: {
        saveButton: '{s name=settings/save_button}Save{/s}',

        bonusMaintenanceModeFieldLabel: '{s name=settings/bonusMaintenanceModeFieldLabel}Wartungsmodus aktivieren{/s}',
        bonusMaintenanceModeHelpText: '{s name=settings/bonusMaintenanceModeHelpText}Wenn der Wartungsmodus aktiviert ist, werden f&uuml;r Bestellungen keine Bonuspunkte gutgeschrieben. Die Darstellung der Bonuspunkte sowie der Bonusartikel und die Verrechnung sind im Frontend deaktiviert.{/s}',

        bonusArticlesActiveFieldLabel: '{s name=settings/bonusArticlesActiveFieldLabel}Bonusartikel aktivieren{/s}',
        bonusArticlesActiveHelpText: '{s name=settings/bonusArticlesActiveHelpText}Durch die Aktivierung der Bonuspunkte Artikel, ist es Ihren Kunden m&ouml;glich die von Ihnen definierten Bonusartikel mit den gesammelten Bonuspunkten zu bestellen.{/s}',

        bonusPointConversionFactorFieldLabel: '{s name=settings/bonusPointConversionFactorFieldLabel}Umrechnungsfaktor Bonuspunkte{/s}',
        bonusPointConversionFactorHelpText: '{s name=settings/bonusPointConversionFactorHelpText}Hier k&ouml;nnen Sie bestimmen ab wieviel Euro dem Kunden ein Bonuspunkt geschrieben wird. (Beispiel: Ist hier der Wert 100 eingetragen, so wird dem Kunden bei einer Bestellung im Wert von 200-299 &euro; (immer abgerundet) insgesamt 2 Bonuspunkte gut geschrieben){/s}',
        bonusPointConversionFactorSupportText: '{s name=settings/bonusPointConversionFactorSupportText}Eurowert f&uuml;r einen Bonuspunkt{/s}',

        bonusPointUnlockTypeFieldLabel: '{s name=settings/bonusPointUnlockTypeFieldLabel}Freischaltung der Bonuspunkte{/s}',
        bonusPointUnlockTypeHelpText: '{s name=settings/bonusPointUnlockTypeHelpText}Hier k&ouml;nnen Sie bestimmen, wann die Bonuspunkte einer Bestellung dem Kunden gutgeschrieben werden.{/s}',
        bonusPointUnlockTypeHelpTitle: '{s name=settings/bonusPointUnlockTypeHelpTitle}Freischaltung{/s}',

        bonusPointUnlockTypePaidBoxLabel: '{s name=settings/bonusPointUnlockTypePaidBoxLabel}Wenn Bestellung auf bezahlt{/s}',
        bonusPointUnlockTypePaidHelpText: '{s name=settings/bonusPointUnlockTypePaidHelpText}Die Bonuspunkte der Bestellung werden freigegeben sobald diese bezahlt wurde. (Zahlstatus = Komplett abgeschlossen){/s}',
        bonusPointUnlockTypePaidHelpTitle: '{s name=settings/bonusPointUnlockTypePaidHelpTitle}Freischaltung bei Bezahlung{/s}',

        bonusPointUnlockTypeDayBoxLabel: '{s name=settings/bonusPointUnlockTypeDayBoxLabel}Nach [n] Tagen nach Bestellung{/s}',
        bonusPointUnlockTypeDayHelpText: '{s name=settings/bonusPointUnlockTypeDayHelpText}Die Bonuspunkte der Bestellung werden nach [n] Tagen automatisch freigeschaltet, unabh&auml;nging davon ob die Bestellung bezahlt wurde.{/s}',
        bonusPointUnlockTypeDayHelpTitle: '{s name=settings/bonusPointUnlockTypeDayHelpTitle}Freischaltung nach Tagen{/s}',

        bonusPointUnlockTypeDirectBoxLabel: '{s name=settings/bonusPointUnlockTypeDirectBoxLabel}Direkt nach Bestellung{/s}',
        bonusPointUnlockTypeDirectHelpText: '{s name=settings/bonusPointUnlockTypeDirectHelpText}Die Bonuspunkte der Bestellung werden nach Abschluss der Bestellung freigeschaltet.{/s}',
        bonusPointUnlockTypeDirectHelpTitle: '{s name=settings/bonusPointUnlockTypeDirectHelpTitle}Direkte Freischaltung{/s}',

        bonusPointUnlockTypeManuelBoxLabel: '{s name=settings/bonusPointUnlockTypeManuelBoxLabel}Immer manuell{/s}',
        bonusPointUnlockTypeManuelHelpText: '{s name=settings/bonusPointUnlockTypeManuelHelpText}Die Freigabe der Bonuspunkte ist immer manuell &uuml;ber das Backend Module durchzuf&uuml;hren{/s}',
        bonusPointUnlockTypeManuelHelpTitle: '{s name=settings/bonusPointUnlockTypeManuelHelpTitle}Manuelle Freischaltung{/s}',

        bonusPointUnlockDayFieldLabel: '{s name=settings/bonusPointUnlockDayFieldLabel}Tage bis zur Freischaltung{/s}',
        bonusPointUnlockDaySupportText: '{s name=settings/bonusPointUnlockDaySupportText}Wochentage{/s}',
        bonusPointUnlockDayHelpText: '{s name=settings/bonusPointUnlockDayHelpText}Sollten Sie die Freischaltung der Bonuspunkte auf [n] Tag nach Bestellung gesetzt haben k&ouml;nnen Sie hier die Anzahl Tage bestimmen{/s}',

        bonusVoucherActiveFieldLabel: '{s name=settings/bonusVoucherActiveFieldLabel}Verrechnung aktivieren{/s}',
        bonusVoucherActiveHelpText: '{s name=settings/bonusVoucherActiveHelpText}Durch die Aktivierung der Bonuspunkte Verrechnung, ist es Ihren Kunden m&ouml;glich aus den gesammelten Bonuspunkten einen Gutschein generieren zu lassen.{/s}',

        bonusVoucherConversionFactorFieldLabel: '{s name=settings/bonusVoucherConversionFactorFieldLabel}Umrechnungsfaktor{/s}',
        bonusVoucherConversionFactorSupportText: '{s name=settings/bonusVoucherConversionFactorSupportText}Anzahl Bonuspunkte f&uuml;r einen Euro{/s}',
        bonusVoucherConversionFactorHelpText: '{s name=settings/bonusVoucherConversionFactorHelpText}Hier k&ouml;nnen Sie bestimmen f&uuml;r wieviel Bonuspunkte der Kunden einen Euro bei der Verrechnung gut geschrieben bekommt.{/s}',

        bonusVoucherLimitationTypeFieldLabel: '{s name=settings/bonusVoucherLimitationTypeFieldLabel}Art der Verrechnungs Beschr&auml;nkung{/s}',

        bonusVoucherLimitationTypeFixBoxLabel: '{s name=settings/bonusVoucherLimitationTypeFixBoxLabel}Maximal X EUR{/s}',

        bonusVoucherLimitationTypeRelativeBoxLabel: '{s name=settings/bonusVoucherLimitationTypeRelativeBoxLabel}Minus X EUR Bestellwert{/s}',

        bonusVoucherLimitationValueFieldLabel: '{s name=settings/bonusVoucherLimitationValueFieldLabel}Wert der Beschr&auml;nkun{/s}',
        bonusVoucherLimitationValueHelpText: '{s name=settings/asdfasdfbonusVoucherLimitationValueHelpText}Sie können für die Bonuspunkte Verrechnung einen maximalen Gutscheinwert bestimmen. Bei einem Wert von 0 wird der Gutschein auf den Wert der Bestellung beschr&auml;nkt{/s}',
        bonusVoucherLimitationValueSupportText: '{s name=settings/bonusVoucherLimitationValueSupportText}Maximaler Gutscheinwert in Euro{/s}',

        bonusListingTextFieldLabel: '{s name=settings/bonusListingTextFieldLabel}Text f&uuml;r das Bonusartikel Listing{/s}',
        bonusListingTextHelpText: '{s name=settings/bonusListingTextHelpText}Hier k&ouml;nnen Sie einen Text definieren der &uuml;ber dem Bonusartikel Listing angezeigt wird.{/s}',

        displayBannerFieldLabel: '{s name=settings/displayBannerFieldLabel}Banner anzeigen{/s}',
        displayBannerHelpText: '{s name=settings/displayBannerHelpText}Das von Ihnen definierte Banner f&uuml;r das Bonusartikel Listing wird ein- oder ausgeblendet{/s}',

        displayAccordionFieldLabel: '{s name=settings/displayAccordionFieldLabel}Akkordeon anzeigen{/s}',
        displayAccordionHelpText: '{s name=settings/displayAccordionHelpText}Bei Aktivierung des Akkordeons wird auf Ihrer Startseite und in den Kategorie Listings ein Akkordeon mit den ersten f&uuml;nf Bonusartikeln angezeigt{/s}',

        displayArticleSliderFieldLabel: '{s name=settings/displayArticleSliderFieldLabel}Bonusartikel Slider anzeigen{/s}',
        displayArticleSliderHelpText: '{s name=settings/displayArticleSliderHelpText}Bei Aktivierung des Bonusartikel Sliders wird auf Ihrer Startseite und im Warenkorb ein Artikel Slider mit allen Bonusartikeln angezeigt{/s}',

        bonusListingBannerFieldLabel: '{s name=settings/bonusListingBannerFieldLabel}Banner f&uuml;r das Bonusartikel Listing{/s}',
        bonusListingBannerButtonText: '{s name=settings/bonusListingBannerButtonText}Banner ausw&auml;hlen{/s}',

        titleGeneral:  '{s name=settings/titleGeneral}Allgemein{/s}',
        titleVoucher:  '{s name=settings/titleVoucher}Bonuspunkte Verrechnung{/s}',
        titleTemplate:  '{s name=settings/titleTemplate}Template{/s}'
    },

    /**
     * Sets up the ui component
     *
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.registerEvents();

        me.items = me.getItems();
        me.dockedItems = me.getButtons();

        me.callParent(arguments);
    },

    /**
     * Defines additional events which will be
     * fired from the component
     *
     * @return void
     */
    registerEvents: function () {
        this.addEvents(
            /**
             * @event saveSetting
             * @param [Ext.form.Panel] view
             */
            'saveSetting'
        );
    },

    /**
     * Creates buttons shown in form panel
     *
     * @return array
     */
    getButtons: function() {
        var me = this;

        return Ext.create('Ext.toolbar.Toolbar', {
            ui: 'shopware-ui',
            dock: 'bottom',
            cls: 'shopware-toolbar',
            items: [
                '->',
                {
                    text: me.snippets.saveButton,
                    action: 'save',
                    cls: 'primary',
                    formBind: true,
                    handler: function() {
                        me.fireEvent('saveSetting', me);
                    }
                }
            ]
        });
    },

    /**
     * Creates items shown in form panel
     *
     * @return array
     */
    getItems: function() {
        var me = this;

        var items = [
            {
                xtype: 'form',
                title: me.snippets.titleGeneral,
                bodyPadding: 10,
                defaults: {
                    labelWidth: 225
                },
                items: [
                    {
                        xtype: 'checkbox',
                        name: 'bonus_maintenance_mode',
                        uncheckedValue: 0,
                        inputValue: 1,
                        fieldLabel: me.snippets.bonusMaintenanceModeFieldLabel,
                        helpText: me.snippets.bonusMaintenanceModeHelpText
                    },
                    {
                        xtype: 'checkbox',
                        name: 'bonus_articles_active',
                        uncheckedValue: 0,
                        inputValue: 1,
                        fieldLabel: me.snippets.bonusArticlesActiveFieldLabel,
                        helpText: me.snippets.bonusArticlesActiveHelpText
                    },
                    {
                        xtype: 'numberfield',
                        width: 550,
                        minValue: 1,
                        name: 'bonus_point_conversion_factor',
                        fieldLabel: me.snippets.bonusPointConversionFactorFieldLabel,
                        helpText: me.snippets.bonusPointConversionFactorHelpText,
                        supportText: me.snippets.bonusPointConversionFactorSupportText
                    },
                    {
                        xtype: 'radiogroup',
                        allowBlank: false,
                        fieldLabel: me.snippets.bonusPointUnlockTypeFieldLabel,
                        helpText: me.snippets.bonusPointUnlockTypeHelpText,
                        helpTitle: me.snippets.bonusPointUnlockTypeHelpTitle,
                        columns: 1,
                        vertical: true,
                        items: [
                            {
                                inputValue: 'paid',
                                name: 'bonus_point_unlock_type',
                                boxLabel: me.snippets.bonusPointUnlockTypePaidBoxLabel,
                                helpText: me.snippets.bonusPointUnlockTypePaidHelpText,
                                helpTitle: me.snippets.bonusPointUnlockTypePaidHelpTitle
                            },
                            {
                                inputValue: 'day',
                                name: 'bonus_point_unlock_type',
                                boxLabel: me.snippets.bonusPointUnlockTypeDayBoxLabel,
                                helpText: me.snippets.bonusPointUnlockTypeDayHelpText,
                                helpTitle: me.snippets.bonusPointUnlockTypeDayHelpTitle
                            },
                            {
                                inputValue: 'direct',
                                name: 'bonus_point_unlock_type',
                                boxLabel: me.snippets.bonusPointUnlockTypeDirectBoxLabel,
                                helpText: me.snippets.bonusPointUnlockTypeDirectHelpText,
                                helpTitle: me.snippets.bonusPointUnlockTypeDirectHelpTitle
                            },
                            {
                                inputValue: 'manuel',
                                name: 'bonus_point_unlock_type',
                                boxLabel: me.snippets.bonusPointUnlockTypeManuelBoxLabel,
                                helpText: me.snippets.bonusPointUnlockTypeManuelHelpText,
                                helpTitle: me.snippets.bonusPointUnlockTypeManuelHelpTitle
                            }
                        ]
                    },
                    {
                        xtype: 'numberfield',
                        width: 550,
                        minValue: 1,
                        name: 'bonus_point_unlock_day',
                        fieldLabel: me.snippets.bonusPointUnlockDayFieldLabel,
                        supportText: me.snippets.bonusPointUnlockDaySupportText,
                        helpText: me.snippets.bonusPointUnlockDayHelpText,
                        helpWidth: 250
                    }

                ]
            },
            {
                xtype: 'form',
                title: me.snippets.titleVoucher,
                bodyPadding: 10,
                defaults: {
                    labelWidth: 225
                },
                items: [
                    {
                        xtype: 'checkbox',
                        uncheckedValue: 0,
                        inputValue: 1,
                        name: 'bonus_voucher_active',
                        fieldLabel: me.snippets.bonusVoucherActiveFieldLabel,
                        helpText: me.snippets.bonusVoucherActiveHelpText,
                        helpWidth: 250
                    },
                    {
                        xtype: 'numberfield',
                        width: 550,
                        minValue: 1,
                        name: 'bonus_voucher_conversion_factor',
                        placeholder: 100,
                        fieldLabel: me.snippets.bonusVoucherConversionFactorFieldLabel,
                        supportText: me.snippets.bonusVoucherConversionFactorSupportText,
                        helpText: me.snippets.bonusVoucherConversionFactorHelpText
                    },
                    {
                        xtype: 'radiogroup',
                        fieldLabel: me.snippets.bonusVoucherLimitationTypeFieldLabel,
                        columns: 1,
                        vertical: true,
                        items: [
                            {
                                boxLabel: me.snippets.bonusVoucherLimitationTypeFixBoxLabel,
                                name: 'bonus_voucher_limitation_type',
                                inputValue: 'fix'
                            },
                            {
                                boxLabel: me.snippets.bonusVoucherLimitationTypeRelativeBoxLabel,
                                name: 'bonus_voucher_limitation_type',
                                inputValue: 'relative'
                            }
                        ]
                    },
                    {
                        xtype: 'numberfield',
                        width: 550,
                        minValue: 0,
                        name: 'bonus_voucher_limitation_value',
                        placeholder: 100,
                        fieldLabel: me.snippets.bonusVoucherLimitationValueFieldLabel,
                        supportText: me.snippets.bonusVoucherLimitationValueSupportText,
                        helpText: me.snippets.bonusVoucherLimitationValueHelpText,
                        helpWidth: 250
                    }
                ]
            },
            {
                xtype: 'form',
                title: me.snippets.titleTemplate,
                bodyPadding: 10,
                defaults: {
                    labelWidth: 225
                },
                name: 'template-settings-panel',
                items: [
                    {
                        xtype: 'tinymcefield',
                        height: 350,
                        name: 'bonus_listing_text',
                        fieldLabel: me.snippets.bonusListingTextFieldLabel,
                        helpText: me.snippets.bonusListingTextHelpText
                    },
                    {
                        xtype: 'checkbox',
                        name: 'display_banner',
                        uncheckedValue: 0,
                        inputValue: 1,
                        fieldLabel: me.snippets.displayBannerFieldLabel,
                        helpText: me.snippets.displayBannerHelpText
                    },
                    {
                        xtype: 'checkbox',
                        name: 'display_accordion',
                        uncheckedValue: 0,
                        inputValue: 1,
                        fieldLabel: me.snippets.displayAccordionFieldLabel,
                        helpText: me.snippets.displayAccordionHelpText
                    },
                    {
                        xtype: 'checkbox',
                        uncheckedValue: 0,
                        inputValue: 1,
                        name: 'display_article_slider',
                        fieldLabel: me.snippets.displayArticleSliderFieldLabel,
                        helpText: me.snippets.displayArticleSliderHelpText
                    },
                    {
                        xtype:'mediaselectionfield',
                        name: 'bonus_listing_banner',
                        fieldLabel: me.snippets.bonusListingBannerFieldLabel,
                        buttonText: me.snippets.bonusListingBannerButtonText,
                        multiSelect: false,
                        anchor:'60%'
                    }
                ]
            }
        ]

        return items;
    }
});
//{/block}
