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
 * @package    Emotion
 * @subpackage App
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware UI - Emotion Bootstrapper
 *
 * This file bootstrapps the Emotion module.
 */
//{block name="backend/emotion/app"}
//{block name="backend/Emotion/app"}
Ext.define('Shopware.apps.Emotion', {

    /**
     * Extends from our special controller, which handles the
     * sub-application behavior and the event bus
     * @string
     */
    extend: 'Enlight.app.SubApplication',

    /**
     * Sets the loading path for the sub-application.
     *
     * Note that you'll need a "loadAction" in your
     * controller (server-side)
     * @string
     */
    loadPath:'{url action=load}',

    /**
     * Enables our bulk loading technique.
     * @booelan
     */
    bulkLoad: true,

    /**
     * The name of the module. Used for internal purpose
     * @string
     */
    name: 'Shopware.apps.Emotion',

    /**
     * Required controllers for module (subapplication)
     * @array
     */
    controllers: [ 'Main', 'Detail', 'Templates' ],

    /**
     * Required views for module (subapplication)
     * @array
     */
    views: [ 'main.Window', 'list.Toolbar', 'list.Grid', 'presets.Window', 'presets.List', 'presets.Info', 'presets.Form', 'detail.Window', 'detail.Preview', 'detail.Designer', 'detail.Grid',
        'detail.Settings', 'detail.Layout', 'detail.Widgets', 'detail.elements.Base', 'detail.elements.Banner', 'detail.elements.BannerSlider',
        'detail.elements.Html', 'detail.elements.Article', 'detail.elements.ArticleSlider', 'detail.elements.HtmlCode', 'detail.elements.Blog',
        'detail.elements.CategoryTeaser', 'detail.elements.HtmlVideo', 'detail.elements.Iframe', 'detail.elements.ManufacturerSlider', 'detail.elements.Youtube', 'detail.elements.ContentType',
        'components.SettingsWindow', 'components.Base', 'components.Banner', 'components.BannerMapping', 'components.Iframe',
        'components.Article', 'components.CategoryTeaser', 'components.fields.Article', 'components.fields.ArticleType',
        'components.fields.CategoryImageType', 'components.fields.CategorySelection', 'components.Blog', 'components.BannerSlider',
        'components.fields.SliderSelect', 'components.fields.Variant', 'components.fields.ManufacturerType', 'components.ManufacturerSlider', 'components.fields.LinkTarget',
        'components.fields.ArticleSliderType', 'components.ArticleSlider', 'components.HtmlElement', 'components.HtmlVideo', 'components.HtmlCode', 'components.Youtube', 'components.ContentType',
        'templates.List', 'templates.Toolbar', 'templates.Settings', 'components.fields.VideoMode', 'translation.Window' ],

    /**
     * Required views for module (subapplication)
     * @array
     */
    stores: [ 'CategoryPath', 'List', 'LandingPage', 'Detail', 'Library', 'Templates', 'Presets', 'Visibility', 'ContentTypeMode' ],

    /**
     * Required models for the module (subapplication)
     * @array
     */
    models: [
        'Emotion',
        'EmotionElement',
        'EmotionShop',
        'Viewport',
        'Component',
        'Field',
        'BannerSlider',
        'ManufacturerSlider',
        'ArticleSlider',
        'Template',
        'Preset',
        'Presetdata'
    ],

    /**
     * Returns the main application window for this is expected
     * by the Enlight.app.SubApplication class.
     * The class sets a new event listener on the "destroy" event of
     * the main application window to perform the destroying of the
     * whole sub application when the user closes the main application window.
     *
     * This method will be called when all dependencies are solved and
     * all member controllers, models, views and stores are initialized.
     *
     * @private
     * @return [object] mainWindow - the main application window based on Enlight.app.Window
     */
    launch: function() {
        var me = this,
            mainController = me.getController('Main');

        return mainController.mainWindow;
    }
});
//{/block}
//{/block}
