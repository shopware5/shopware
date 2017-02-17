<?php
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

namespace Shopware\Themes\JsBase;

use Doctrine\Common\Collections\ArrayCollection;
use Shopware\Components\Form as Form;
use Shopware\Components\Theme\ConfigSet;

class Theme extends \Shopware\Components\Theme
{

    /**
     * Defines the extended Theme
     * @var string
     */
    protected $extend = 'Bare';

    /**
     * Defines the human readable theme name
     * which displayed in the backend
     * @var string
     */
    protected $name = '__theme_name__';

    /**
     * Allows to define a description text
     * for the theme
     * @var string
     */
    protected $description = '__theme_description__';

    /**
     * Name of the theme author.
     * @var string
     */
    protected $author = '__author__';

    /**
     * License of the theme source code.
     *
     * @var string
     */
    protected $license = '__license__';

    /**
     * @var bool
     */
    protected $injectBeforePlugins = true;

    /**
     * Javascript files which will be used in the theme
     *
     * @var array
     */
    protected $javascript = [

        // Third party plugins / libraries
        'vendors/js/jquery/jquery.min.js',
        'vendors/js/picturefill/picturefill.min.js',
        'vendors/js/jquery.transit/jquery.transit.js',
        'vendors/js/jquery.event.move/jquery.event.move.js',
        'vendors/js/jquery.event.swipe/jquery.event.swipe.js',

        // Shopware specific plugins
        'src/js/jquery.ie-fixes.js',
        'src/js/jquery.plugin-base.js',
        'src/js/jquery.state-manager.js',
        'src/js/jquery.storage-manager.js',
        'src/js/jquery.off-canvas-menu.js',
        'src/js/jquery.search.js',
        'src/js/jquery.tab-menu.js',
        'src/js/jquery.image-slider.js',
        'src/js/jquery.image-zoom.js',
        'src/js/jquery.collapse-panel.js',
        'src/js/jquery.auto-submit.js',
        'src/js/jquery.scroll.js',
        'src/js/jquery.product-slider.js',
        'src/js/jquery.register.js',
        'src/js/jquery.modal.js',
        'src/js/jquery.selectbox-replacement.js',
        'src/js/jquery.captcha.js',
        'src/js/jquery.drop-down-menu.js',
        'src/js/jquery.loading-indicator.js',
        'src/js/jquery.overlay.js',
        'src/js/jquery.form-polyfill.js',
        'src/js/jquery.pseudo-text.js',
        'src/js/jquery.last-seen-products.js',
        'src/js/jquery.lightbox.js',
        'src/js/jquery.ajax-product-navigation.js',
        'src/js/jquery.newsletter.js',
        'src/js/jquery.menu-scroller.js',
        'src/js/jquery.shipping-payment.js',
        'src/js/jquery.add-article.js',
        'src/js/jquery.range-slider.js',
        'src/js/jquery.filter-component.js',
        'src/js/jquery.listing-actions.js',
        'src/js/jquery.collapse-cart.js',
        'src/js/jquery.emotion.js',
        'src/js/jquery.product-compare-add.js',
        'src/js/jquery.product-compare-menu.js',
        'src/js/jquery.infinite-scrolling.js',
        'src/js/jquery.off-canvas-button.js',
        'src/js/jquery.subcategory-nav.js',
        'src/js/jquery.ajax-wishlist.js',
        'src/js/jquery.preloader-button.js',
        'src/js/jquery.image-gallery.js',
        'src/js/jquery.offcanvas-html-panel.js',
        'src/js/jquery.jump-to-tab.js',
        'src/js/jquery.ajax-variant.js',
        'src/js/jquery.csrf-protection.js',
        'src/js/jquery.panel-auto-resizer.js',
        'src/js/jquery.address-selection.js',
        'src/js/jquery.address-editor.js',
        'src/js/jquery.shopware-responsive.js'
    ];
}
