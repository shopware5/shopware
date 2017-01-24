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
 * @package    Customer
 * @subpackage Detail
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/performance/main}

/**
 * Filter fieldSet
 */
//{block name="backend/performance/view/tabs/settings/fields/filter"}
Ext.define('Shopware.apps.Performance.view.tabs.settings.fields.Filter', {
    /**
     * Define that the base field set is an extension of the "Base" fieldSet
     * @string
     */
    extend: 'Shopware.apps.Performance.view.tabs.settings.fields.Base',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias: 'widget.performance-tabs-settings-filter',

    /**
     * Description of the fieldSet
     */
    caption: '{s name=tabs/settings/filter/title}Filters{/s}',

    /**
     * Component event method which is fired when the component
     * is initials. The component is initials when the user
     * want to create a new customer or edit an existing customer
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.items = me.getItems();
        me.callParent(arguments);

    },

    getItems: function() {
        var me = this;

        me.listingModeField = Ext.create('Shopware.apps.Base.view.element.ListingFilterModeSelect', {
            name: 'filters[listingMode]',
            fieldLabel: '{s name=fieldset/filter/text/listingMode}{/s}',
            labelWidth: 210,
            labelStyle: 'font-weight: 700;',
            anchor: '100%'
        });

        return [
            {
                xtype: 'fieldset',
                defaults: me.defaults,
                title: '{s name=fieldset/information}{/s}',
                items: [
                    me.createDescriptionContainer("{s name=fieldset/filter/info}{/s}")
                ]
            },
            {
                xtype: 'fieldset',
                defaults: me.defaults,
                title: '{s name=fieldset/listings}Listings{/s}',
                items: [ me.listingModeField ]
            }
        ];
    }
});
//{/block}
