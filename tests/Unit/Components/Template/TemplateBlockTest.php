<?php

declare(strict_types=1);
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

namespace Shopware\Tests\Unit\Components\Template;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;

class TemplateBlockTest extends TestCase
{
    private const BLOCK_REGEX = '/{block name=["|\'](.*?)["|\']}/';

    public function testForDuplicateBlocksBackend(): void
    {
        $files = (new Finder())
            ->in(__DIR__ . '/../../../../themes/Backend/ExtJs/backend')
            ->name('*.js')
            ->contains(self::BLOCK_REGEX);

        $this->checkFiles($files);
    }

    public function testForDuplicateBlocksFrontend(): void
    {
        $files = (new Finder())
            ->in(__DIR__ . '/../../../../themes/Frontend/Bare')
            ->name('*.tpl')
            ->contains(self::BLOCK_REGEX);

        $multipleOccurrencesAllowed = [
            'document_index_amount' => 3,
            'document_index_head_bottom' => 4,
            'document_index_head_right' => 3,
            'document_index_selectAdress' => 2,
            'document_index_table_each' => 2,
            'document_index_table_head_price' => 2,
            'document_index_table_head_tax' => 2,
            'document_index_table_price' => 3,
            'document_index_table_tax' => 2,

            'frontend_account_error_messages' => 3,
            'frontend_account_logout_info' => 2,
            'frontend_account_logout_info_actions' => 2,
            'frontend_account_logout_info_content' => 2,
            'frontend_account_logout_info_headline' => 2,
            'frontend_account_order_item_date' => 2,
            'frontend_account_order_item_dispatch' => 2,
            'frontend_account_orders_welcome' => 2,
            'frontend_account_orders_welcome_headline' => 2,
            'frontend_account_partner_statistic_item_overview_row' => 2,
            'frontend_account_sidebar' => 2,

            'frontend_address_action_button_send' => 2,
            'frontend_address_action_buttons' => 2,
            'frontend_address_content' => 2,
            'frontend_address_error_messages' => 3,
            'frontend_address_form_content' => 2,
            'frontend_address_form_headline' => 2,
            'frontend_address_form_input_set_default_billing' => 2,
            'frontend_address_form_input_set_default_shipping' => 2,
            'frontend_address_headline' => 2,

            'frontend_atom_title' => 2,

            'frontend_checkout_actions_checkout' => 2,
            'frontend_checkout_actions_confirm' => 2,
            'frontend_checkout_actions_confirm_bottom_checkout' => 2,
            'frontend_checkout_actions_inquiry' => 2,
            'frontend_checkout_actions_link_last' => 2,
            'frontend_checkout_ajax_cart_prices_container_inner' => 2,
            'frontend_checkout_ajax_cart_prices_info' => 2,
            'frontend_checkout_cart_footer_add_product' => 3,
            'frontend_checkout_cart_footer_add_voucher' => 3,
            'frontend_checkout_cart_footer_add_voucher_button' => 2,
            'frontend_checkout_cart_footer_add_voucher_field' => 2,
            'frontend_checkout_cart_footer_add_voucher_label' => 2,
            'frontend_checkout_cart_footer_add_voucher_trigger' => 2,
            'frontend_checkout_cart_footer_field_labels_taxes' => 2,
            'frontend_checkout_cart_footer_field_labels_total' => 2,
            'frontend_checkout_cart_header_actions' => 2,
            'frontend_checkout_cart_header_price' => 3,
            'frontend_checkout_cart_header_tax' => 2,
            'frontend_checkout_cart_item_additional_type' => 3,
            'frontend_checkout_cart_item_delete_article' => 2,
            'frontend_checkout_cart_item_delivery_informations' => 2,
            'frontend_checkout_cart_item_details_essential_features' => 2,
            'frontend_checkout_cart_item_image_container' => 2,
            'frontend_checkout_cart_item_premium_delete_article' => 2,
            'frontend_checkout_cart_item_premium_tax_price' => 2,
            'frontend_checkout_cart_item_price' => 2,
            'frontend_checkout_cart_item_quantity' => 2,
            'frontend_checkout_cart_item_quantity_label' => 2,
            'frontend_checkout_cart_item_rebate_details_inline' => 3,
            'frontend_checkout_cart_item_rebate_tax_price' => 3,
            'frontend_checkout_cart_item_tax_price' => 2,
            'frontend_checkout_cart_item_voucher_delete_article' => 2,
            'frontend_checkout_cart_item_voucher_details_inline' => 2,
            'frontend_checkout_cart_item_voucher_tax_price' => 2,
            'frontend_checkout_cart_tax_symbol' => 8,
            'frontend_checkout_cart_voucher_tax_label' => 4,
            'frontend_checkout_cart_voucher_tax_value' => 3,
            'frontend_checkout_footer' => 2,
            'frontend_checkout_shipping_costs_country_include' => 3,
            'frontend_checkout_shipping_costs_country_trigger' => 2,
            'frontend_checkout_shipping_payment_core_buttons' => 2,

            'frontend_content_type_field_base_content' => 11,
            'frontend_content_type_field_base_label' => 4,

            'frontend_detail_actions_notepad' => 2,
            'frontend_detail_configurator_noscript_action' => 2,
            'frontend_detail_group_description' => 2,
            'frontend_detail_group_name' => 2,
            'frontend_detail_group_selection' => 2,
            'frontend_detail_image_fallback' => 2,
            'frontend_detail_index_similar_slider_content' => 2,
            'frontend_detail_index_tabs_cross_selling' => 2,
            'frontend_detail_tabs_rating_title' => 2,

            'frontend_home_index_promotions' => 2,

            'frontend_index_after_body' => 2,
            'frontend_index_body_classes' => 5,
            'frontend_index_body_inline' => 2,
            'frontend_index_breadcrumb' => 9,
            'frontend_index_breadcrumb_inner' => 2,
            'frontend_index_checkout_actions' => 2,
            'frontend_index_content' => 42,
            'frontend_index_content_left' => 17,
            'frontend_index_content_main_classes' => 3,
            'frontend_index_content_right' => 8,
            'frontend_index_content_top' => 5,
            'frontend_index_content_wrapper' => 2,
            'frontend_index_controller_url' => 2,
            'frontend_index_footer' => 8,
            'frontend_index_header' => 12,
            'frontend_index_header_canonical' => 10,
            'frontend_index_header_feeds' => 5,
            'frontend_index_header_javascript' => 2,
            'frontend_index_header_javascript_jquery_lib' => 2,
            'frontend_index_header_meta_description' => 11,
            'frontend_index_header_meta_description_og' => 5,
            'frontend_index_header_meta_description_twitter' => 5,
            'frontend_index_header_meta_http_tags' => 2,
            'frontend_index_header_meta_keywords' => 9,
            'frontend_index_header_meta_robots' => 4,
            'frontend_index_header_meta_tags_opengraph' => 8,
            'frontend_index_header_title' => 11,
            'frontend_index_left_categories' => 3,
            'frontend_index_left_last_articles' => 3,
            'frontend_index_logo_container' => 2,
            'frontend_index_logo_supportinfo' => 2,
            'frontend_index_logo_trusted_shops' => 5,
            'frontend_index_navigation' => 4,
            'frontend_index_navigation_categories_top' => 7,
            'frontend_index_navigation_inline' => 2,
            'frontend_index_search' => 2,
            'frontend_index_shop_navigation' => 8,
            'frontend_index_sidebar' => 2,
            'frontend_index_start' => 20,
            'frontend_index_top_bar_container' => 8,

            'frontend_listing_actions_count' => 3,
            'frontend_listing_actions_filter_container' => 2,
            'frontend_listing_actions_items_per_page' => 2,
            'frontend_listing_actions_paging_first' => 2,
            'frontend_listing_actions_paging_inner' => 2,
            'frontend_listing_actions_paging_label' => 3,
            'frontend_listing_actions_paging_last' => 2,
            'frontend_listing_actions_paging_next' => 3,
            'frontend_listing_actions_paging_numbers' => 3,
            'frontend_listing_actions_paging_previous' => 3,
            'frontend_listing_actions_top' => 2,
            'frontend_listing_atom_article_name' => 2,
            'frontend_listing_atom_article_title' => 2,
            'frontend_listing_atom_entry' => 2,
            'frontend_listing_atom_link' => 2,
            'frontend_listing_atom_short_description' => 2,
            'frontend_listing_atom_title' => 2,
            'frontend_listing_bottom_paging' => 3,
            'frontend_listing_box_article' => 2,
            'frontend_listing_box_article_actions' => 4,
            'frontend_listing_box_article_badges_container' => 2,
            'frontend_listing_box_article_buy' => 2,
            'frontend_listing_box_article_content' => 2,
            'frontend_listing_box_article_description' => 6,
            'frontend_listing_box_article_image_element' => 3,
            'frontend_listing_box_article_image_media' => 3,
            'frontend_listing_box_article_image_picture_element' => 2,
            'frontend_listing_box_article_info_container' => 2,
            'frontend_listing_box_article_name' => 2,
            'frontend_listing_box_article_picture' => 3,
            'frontend_listing_box_article_price' => 3,
            'frontend_listing_box_article_price_default' => 2,
            'frontend_listing_box_article_price_discount' => 2,
            'frontend_listing_box_article_price_discount_after' => 2,
            'frontend_listing_box_article_price_discount_before' => 2,
            'frontend_listing_box_article_price_info' => 2,
            'frontend_listing_box_article_price_regulation' => 2,
            'frontend_listing_box_article_price_regulation_after' => 2,
            'frontend_listing_box_article_price_regulation_before' => 2,
            'frontend_listing_box_article_rating' => 2,
            'frontend_listing_box_article_unit' => 2,
            'frontend_listing_box_article_unit_label' => 2,
            'frontend_listing_emotions' => 2,
            'frontend_listing_emotions_emotion' => 2,
            'frontend_listing_filter_facet_date_config' => 4,
            'frontend_listing_filter_facet_date_content' => 3,
            'frontend_listing_filter_facet_date_title' => 2,
            'frontend_listing_filter_facet_multi_selection_input' => 2,
            'frontend_listing_filter_facet_range_slider_config' => 2,
            'frontend_listing_index_banner' => 2,
            'frontend_listing_index_listing' => 2,
            'frontend_listing_index_tagcloud' => 2,
            'frontend_listing_index_text' => 2,
            'frontend_listing_index_topseller' => 3,
            'frontend_listing_list_inline' => 3,
            'frontend_listing_list_promotion' => 2,
            'frontend_listing_list_promotion_link_show_listing' => 2,
            'frontend_listing_listing_container' => 2,
            'frontend_listing_listing_content' => 3,
            'frontend_listing_listing_wrapper' => 2,
            'frontend_listing_no_filter_result' => 2,
            'frontend_listing_text' => 2,
            'frontend_listing_top_actions' => 2,

            'frontend_note_item_actions_compare' => 2,

            'frontend_register_personal_fieldset_input_title' => 2,

            'frontend_widgets_captcha' => 3,
            'frontend_widgets_captcha_input_code' => 3,
            'frontend_widgets_captcha_input_label' => 3,
            'frontend_widgets_captcha_input_placeholder' => 2,
        ];

        $this->checkFiles($files, $multipleOccurrencesAllowed);
    }

    /**
     * @param array<string, int> $multipleOccurrencesAllowed Indexed by block name, value is amount of allowed occurrences
     */
    private function checkFiles(Finder $files, array $multipleOccurrencesAllowed = []): void
    {
        $blocks = [];
        foreach ($files as $file) {
            $matchCounter = preg_match_all(self::BLOCK_REGEX, $file->getContents(), $matches);
            if ($matchCounter === false) {
                continue;
            }

            foreach ($matches[1] as $match) {
                if (\array_key_exists($match, $blocks)) {
                    ++$blocks[$match];
                } else {
                    $blocks[$match] = 1;
                }
            }
        }

        foreach ($blocks as $blockName => $count) {
            if (\array_key_exists($blockName, $multipleOccurrencesAllowed)) {
                $expectedCount = $multipleOccurrencesAllowed[$blockName];
            } else {
                $expectedCount = 1;
            }

            static::assertSame(
                $expectedCount,
                $count,
                sprintf('Block "%s" expected to have %s matches. Got %s matches instead', $blockName, $expectedCount, $count)
            );
        }
    }
}
