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
 * @package    ProductStream
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name="backend/attributes/main"}

Ext.define('Shopware.apps.Attributes.model.Table', {
    extend: 'Shopware.data.Model',

    snippets: {
        s_articles_attributes: '{s name="table/s_articles_attributes"}{/s}',
        s_articles_downloads_attributes: '{s name="table/s_articles_downloads_attributes"}{/s}',
        s_articles_esd_attributes: '{s name="table/s_articles_esd_attributes"}{/s}',
        s_articles_img_attributes: '{s name="table/s_articles_img_attributes"}{/s}',
        s_articles_information_attributes: '{s name="table/s_articles_information_attributes"}{/s}',
        s_articles_prices_attributes: '{s name="table/s_articles_prices_attributes"}{/s}',
        s_articles_supplier_attributes: '{s name="table/s_articles_supplier_attributes"}{/s}',
        s_emarketing_banners_attributes: '{s name="table/s_emarketing_banners_attributes"}{/s}',
        s_emarketing_partner_attributes: '{s name="table/s_emarketing_partner_attributes"}{/s}',
        s_blog_attributes: '{s name="table/s_blog_attributes"}{/s}',
        s_categories_attributes: '{s name="table/s_categories_attributes"}{/s}',
        s_core_countries_attributes: '{s name="table/s_core_countries_attributes"}{/s}',
        s_core_countries_states_attributes: '{s name="table/s_core_countries_states_attributes"}{/s}',
        s_core_shops_attributes: '{s name="table/s_core_shops_attributes"}{/s}',
        s_user_attributes: '{s name="table/s_user_attributes"}{/s}',
        s_core_customergroups_attributes: '{s name="table/s_core_customergroups_attributes"}{/s}',
        s_premium_dispatch_attributes: '{s name="table/s_premium_dispatch_attributes"}{/s}',
        s_order_documents_attributes: '{s name="table/s_order_documents_attributes"}{/s}',
        s_emotion_attributes: '{s name="table/s_emotion_attributes"}{/s}',
        s_cms_support_attributes: '{s name="table/s_cms_support_attributes"}{/s}',
        s_core_config_mails_attributes: '{s name="table/s_core_config_mails_attributes"}{/s}',
        s_media_attributes: '{s name="table/s_media_attributes"}{/s}',
        s_order_attributes: '{s name="table/s_order_attributes"}{/s}',
        s_order_basket_attributes: '{s name="table/s_order_basket_attributes"}{/s}',
        s_order_billingaddress_attributes: '{s name="table/s_order_billingaddress_attributes"}{/s}',
        s_order_details_attributes: '{s name="table/s_order_details_attributes"}{/s}',
        s_order_shippingaddress_attributes: '{s name="table/s_order_shippingaddress_attributes"}{/s}',
        s_core_paymentmeans_attributes: '{s name="table/s_core_paymentmeans_attributes"}{/s}',
        s_export_attributes: '{s name="table/s_export_attributes"}{/s}',
        s_filter_attributes: '{s name="table/s_filter_attributes"}{/s}',
        s_filter_options_attributes: '{s name="table/s_filter_options_attributes"}{/s}',
        s_filter_values_attributes: '{s name="table/s_filter_values_attributes"}{/s}',
        s_product_streams_attributes: '{s name="table/s_product_streams_attributes"}{/s}',
        s_cms_static_attributes: '{s name="table/s_cms_static_attributes"}{/s}',
        s_article_configurator_templates_attributes: '{s name="table/s_article_configurator_templates_attributes"}{/s}',
        s_article_configurator_template_prices_attributes: '{s name="table/s_article_configurator_template_prices_attributes"}{/s}',
        s_article_configurator_options_attributes: '{s name="table/s_article_configurator_options_attributes"}{/s}',
        s_article_configurator_groups_attributes: '{s name="table/s_article_configurator_groups_attributes"}{/s}',
        s_core_auth_attributes: '{s name="table/s_core_auth_attributes"}{/s}',
        s_emarketing_vouchers_attributes: '{s name="table/s_emarketing_vouchers_attributes"}{/s}',
        s_user_addresses_attributes: '{s name="table/s_user_addresses_attributes"}{/s}',
        s_customer_streams_attributes: '{s name="table/s_customer_streams_attributes"}{/s}',
        s_articles_notification_attributes: '{s name="table/s_articles_notification_attributes"}{/s}',
    },

    fields: [
        { name: 'name', type: 'string' },
        {
            name: 'label',
            type: 'string',
            convert: function(value, record) {
                return record.getLabel(value, record);
            }
        },
        { name: 'model', type: 'string' },
        { name: 'identifiers', type: 'array' },
        { name: 'foreignKey', type: 'string' },
        { name: 'coreAttributes', type: 'array' },
        { name: 'dependingTables', type: 'array' }
    ],

    getLabel: function(value, record) {
        var name = record.get('name');

        if (record.snippets.hasOwnProperty(name)) {
            return record.snippets[name];
        } else {
            return '';
        }
    }
});
