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
 * @package    Article
 * @subpackage Detail
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware UI - Article detail page - Image
 * The drop zone component is used in the option sidebar component and in the image tab.
 * It handles the drop event when the user drops one or more images on the drop zone and uploads
 * the images over the media manager into the article album. After the images uploaded the media controller
 * adds the images to the listing.
 * All events of the component handled in the media controller.
 */
//{namespace name=backend/article/view/main}
//{block name="backend/article/view/image/drop_zone"}
Ext.define('Shopware.apps.Article.view.image.DropZone', {
    /**
     * Define that the category drop zone is an extension of the Ext.panel.Panel
     * @string
     */
    extend:'Ext.container.Container',
    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.article-image-drop-zone',
    /**
     * Set css class for this component
     * @string
     */
    cls: Ext.baseCSSPrefix + 'article-image-drop-zone',

    /**
     * Layout for the component
     */
    layout: 'anchor',

    /**
     * Defaults for the panel items
     * @object
     */
    defaults: {
        anchor: '100%'
    },

    snippets: {
        dropZone: '{s name=image/upload/drop_zone}Upload images via drag&drop{/s}'
    },

    /**
     * Configuration object for the drop zone which will be set during initializing
     * @object
     */
    dropZoneConfig: {},

    /**
     * The initComponent template method is an important initialization step for a Component.
     * It is intended to be implemented by each subclass of Ext.Component to provide any needed constructor logic.
     * The initComponent method of the class being created is called first,
     * with each initComponent method up the hierarchy to Ext.Component being called thereafter.
     * This makes it easy to implement and, if needed, override the constructor logic of the Component at any step in the hierarchy.
     * The initComponent method must contain a call to callParent in order to ensure that the parent class' initComponent method is also called.
     *
     * @return void
     */
    initComponent:function () {
        var me = this;
        me.mediaDropZone = me.createMediaDropZone();
        me.items = [ me.mediaDropZone ];
        me.registerEvents();
        me.callParent(arguments);
    },

    /**
     * Registers additional component events.
     */
    registerEvents: function() {
        this.addEvents(
            /**
             * Event will be fired when the user select or uploads article images.
             *
             * @event
             * @param [Ext.data.Model] media - The uploaded media
             */
            'addMedia'
        );
    },

    /**
     * Creates the drop zone for article images
     * @return Shopware.app.FileUpload
     */
    createMediaDropZone: function() {
        var me = this,
            defaultConfig = {
            requestURL: '{url controller="mediaManager" action="upload"}?albumID=-1',
            showInput: false,
            padding:0,
            checkSize: false,
            checkType: false,
            checkAmount: false,
            enablePreviewImage: false,
            dropZoneText: me.snippets.dropZone
        };

        defaultConfig = Ext.apply(defaultConfig, me.dropZoneConfig);
        return Ext.create('Shopware.app.FileUpload', defaultConfig);
    }
});
//{/block}
