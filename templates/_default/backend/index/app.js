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
 * @package    Index
 * @subpackage App
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware UI - Main Backend Application Bootstrap
 *
 * This file bootstrapps the complete backend structure.
 */
Ext.define('Shopware.apps.Index', {
    /**
     * Extends from our special controller, which handles the
     * sub-application behavior and the event bus
     * @string
     */
    extend:'Enlight.app.SubApplication',

    /**
     * Enables our bulk loading technique.
     * @booelan
     */
    bulkLoad: true,

    /**
     * Sets the loading path for the sub-application.
     *
     * Note that you'll need a "loadAction" in your
     * controller (server-side)
     * @string
     */
    loadPath:"{url action=load}",

    /**
     * Required controllers for module (subapplication)
     * @array
     */
    controllers:[ 'Main', 'Widgets', 'ErrorReporter' ],

    /**
     * Requires class for the module (subapplication)
     */
    requires: ['Shopware.container.Viewport'],

    /**
     * Required views for module (subapplication)
     * @array
     */
    views: [ 'Main', 'Menu', 'Footer', 'Search', 'widgets.Window', 'widgets.Desktop', 'widgets.Sales','widgets.Upload', 'widgets.Visitors',
        'widgets.Orders', 'widgets.Notice', 'widgets.Merchant', 'widgets.Base', 'merchant.Window' ],

    /**
     * Required models for the module
     * @array
     */
    models: [ 'Widget', 'Turnover', 'Batch', 'Customers', 'Visitors', 'Orders', 'Merchant', 'MerchantMail' ],

    /**
     * Required models for the module
     * @array
     */
    store: [ 'Widgets' ]
});