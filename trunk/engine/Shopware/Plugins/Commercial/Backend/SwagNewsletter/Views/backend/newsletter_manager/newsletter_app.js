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
 * @package    NewsletterManager
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

//{block name="backend/newsletter_manager/application" append}
    /**
     * Controllers
     */
    //{include file="backend/newsletter_manager/controller/designer.js"}
    //{include file="backend/newsletter_manager/controller/analytics.js"}
    //{include file="backend/newsletter_manager/controller/overview.js"}
    //{include file="backend/newsletter_manager/controller/main.js"}
    //{include file="backend/newsletter_manager/controller/editor.js"}

    /**
     * Models
     */
    //{include file="backend/newsletter_manager/model/component.js"}
    //{include file="backend/newsletter_manager/model/field.js"}
    //{include file="backend/newsletter_manager/model/newsletter_element.js"}
    //{include file="backend/newsletter_manager/model/link.js"}
    //{include file="backend/newsletter_manager/model/article.js"}
    //{include file="backend/newsletter_manager/model/settings.js"}
    //{include file="backend/newsletter_manager/model/mailing.js"}
    //{include file="backend/newsletter_manager/model/voucher.js"}
    //{include file="backend/newsletter_manager/model/order.js"}


    /**
     * Stores
     */
    //{include file="backend/newsletter_manager/store/library.js"}
    //{include file="backend/newsletter_manager/store/voucher.js"}
    //{include file="backend/newsletter_manager/store/mailing.js"}
    //{include file="backend/newsletter_manager/store/order.js"}

    /**
     * Views
     */
    //{include file="backend/newsletter_manager/view/main/window.js"}
    //{include file="backend/newsletter_manager/view/tabs/overview.js"}
    //{include file="backend/newsletter_manager/view/tabs/statistics.js"}
    //{include file="backend/newsletter_manager/view/tabs/analytics.js"}
    //{include file="backend/newsletter_manager/view/tabs/orders.js"}
    //{include file="backend/newsletter_manager/view/newsletter/editor.js"}
    //{include file="backend/newsletter_manager/view/newsletter/designer.js"}
    //{include file="backend/newsletter_manager/view/newsletter/window.js"}

    /**
     * Components
     */
    //{include file="backend/newsletter_manager/view/components/settings_window.js"}

    //{include file="backend/newsletter_manager/view/components/base.js"}
    //{include file="backend/newsletter_manager/view/components/article.js"}
    //{include file="backend/newsletter_manager/view/components/text.js"}
    //{include file="backend/newsletter_manager/view/components/text_east.js"}
    //{include file="backend/newsletter_manager/view/components/link.js"}

    /**
     * Component fields
     */
    //{include file="backend/newsletter_manager/view/components/fields/article.js"}
    //{include file="backend/newsletter_manager/view/components/fields/article_type.js"}
    //{include file="backend/newsletter_manager/view/components/fields/target_selection.js"}
    //{include file="backend/newsletter_manager/view/components/fields/voucher_selection.js"}
    //{include file="backend/newsletter_manager/view/components/fields/numberfield.js"}

//{/block}

