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
 * Categories fieldSet
 */
//{block name="backend/performance/view/tabs/settings/fields/various"}
Ext.define('Shopware.apps.Performance.view.tabs.settings.fields.Various', {
    /**
     * Define that the base field set is an extension of the "Base" fieldSet
     * @string
     */
    extend: 'Shopware.apps.Performance.view.tabs.settings.fields.Base',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias: 'widget.performance-tabs-settings-various',

    /**
     * Description of the fieldSet
     */
    caption: '{s name=tabs/settings/various/title}Various{/s}',

    /**
     * Component event method which is fired when the component
     * is initials. The component is initials when the user
     * want to create a new customer or edit an existing customer
     * @return void
     */
    initComponent:function () {
        var me = this;

        me.items = me.getItems();
        me.callParent(arguments);

    },

    getItems: function() {
        var me = this;

        return [
            {
                xtype: 'fieldset',
                title: '{s name=fieldset/information}Information{/s}',
                defaults: me.defaults,
                items: [
                    me.createDescriptionContainer("{s name=fieldset/categories/info}Here you can adjust various settings which impact the performance of item listings.{/s}")]
            },
            {
                xtype: 'fieldset',
                title: '{s name=fieldset/configuration}Configuration{/s}',
                defaults: me.defaults,
                items: [
                    {
                        fieldLabel: '{s name=fieldset/various/disableStats}{/s}',
                        helpText: '{s name=fieldset/various/disableStats/help}{/s}',
                        name: 'various[disableShopwareStatistics]',
                        xtype: 'checkbox',
                        uncheckedValue: false,
                        inputValue: true
                    },
                    {
                        fieldLabel: '{s name=fieldset/various/lastArticles}{/s}',
                        name: 'various[LastArticles:lastarticles_show]',
                        xtype: 'checkbox',
                        uncheckedValue: false,
                        inputValue: true
                    },
                    {
                        fieldLabel: '{s name=fieldset/various/numLastArticles}{/s}',
                        name: 'various[LastArticles:lastarticlestoshow]',
                        xtype: 'numberfield',
                        minValue: 1
                    },
                    {
                        fieldLabel: '{s name=fieldset/various/disableArticleNavigation}{/s}',
                        helpText: '{s name=fieldset/various/disableArticleNavigation/help}{/s}',
                        name: 'various[disableArticleNavigation]',
                        xtype: 'checkbox',
                        uncheckedValue: false,
                        inputValue: true
                    },
                    {
                        fieldLabel: '{s name=fieldset/various/http2Push}{/s}',
                        helpText: '{s name=fieldset/various/http2Push/help}{/s}',
                        name: 'various[http2Push]',
                        xtype: 'checkbox',
                        uncheckedValue: false,
                        inputValue: true
                    },
                    {
                        fieldLabel: '{s name=fieldset/various/minifyHtml}{/s}',
                        helpText: '{s name=fieldset/various/minifyHtml/help}{/s}',
                        name: 'various[minifyHtml]',
                        xtype: 'checkbox',
                        uncheckedValue: false,
                        inputValue: true
                    }
                ]}
        ];
    }


});
//{/block}
