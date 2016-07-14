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
 * Shopware Controller - Detail
 * The detail controller handles all events of the detail page main form element and the sidebar.
 */
//{namespace name=backend/article/view/main}
//{block name="backend/article/controller/crossselling"}
Ext.define('Shopware.apps.Article.controller.Crossselling', {

    /**
     * Extend from the standard ExtJS 4 controller
     * @string
     */
    extend: 'Enlight.app.Controller',

    /**
     * System texts for the controller.
     *
     * @object
     */
    snippets: {
        growlMessage: '{s name=growl_message}Article{/s}',
        existTitle: '{s name=sidebar/accessory/already_assigned_title}Already exists{/s}',
        similar: {
            exist: '{s name=sidebar/similar/already_assigned_message}The article [0] has been assigned as similar article!{/s}'
        },
        accessory: {
            exist: '{s name=sidebar/accessory/already_assigned_message}The article [0] has been already assigned as accessory article!{/s}'
        },
        streams: {
            exist: '{s name=cross_selling/streams/already_assigned_message}The stream with the ID [0] has already been assigned to this article.{/s}'
        },
        saved: {
            title: '{s name=article_saved/title}Successful{/s}',
            errorTitle: '{s name=article_saved/error_title}Error{/s}'
        }
    },

    /**
     * A template method that is called when your application boots.
     * It is called before the Application's launch function is executed
     * so gives a hook point to run any code before your Viewport is created.
     *
     * @params  - The main controller can handle a orderId parameter to open the order detail page directly
     * @return void
     */
    init:function () {
        var me = this;

        me.control({
            'article-detail-window article-crossselling-base': {
                addSimilarArticle: me.onAddSimilarArticle,
                removeSimilarArticle: me.onRemoveSimilarArticle,
                addAccessoryArticle: me.onAddAccessoryArticle,
                removeAccessoryArticle: me.onRemoveAccessoryArticle
            },
            'article-detail-window article-crossselling-product-streams': {
                addStream: me.onAddStream,
                removeStream: me.onRemoveStream
            }
        });

        me.callParent(arguments);
    },

    /**
     * Event will be fired when the user want to add a similar article
     *
     * @event
     */
    onAddSimilarArticle: function(form, grid, searchField) {
        var me = this,
            selected = searchField.returnRecord,
            store = grid.getStore(),
            values = form.getValues();

        if (!form.getForm().isValid() || !(selected instanceof Ext.data.Model)) {
            return false;
        }
        var model = Ext.create('Shopware.apps.Article.model.Similar', values);
        model.set('id', selected.get('id'));
        model.set('name', selected.get('name'));
        model.set('number', selected.get('number'));

        //check if the article is already assigned
        var exist = store.getById(model.get('id'));
        if (!(exist instanceof Ext.data.Model)) {
            store.add(model);
            form.getForm().reset();
        } else {
            Shopware.Notification.createGrowlMessage(me.snippets.existTitle,  Ext.String.format(me.snippets.similar.exist, model.get('number')), me.snippets.growlMessage);
        }
    },

    /**
     * Event will be fired when the user want to remove an assigned similar article
     *
     * @event
     */
    onRemoveSimilarArticle: function(grid, record) {
        var me = this,
            store = grid.getStore();

        if (record instanceof Ext.data.Model) {
            store.remove(record);
        }
    },

    /**
     * Event will be fired when the user want to add a similar article
     *
     * @event
     */
    onAddAccessoryArticle: function(form, grid, searchField) {
        var me = this,
            selected = searchField.returnRecord,
            store = grid.getStore(),
            values = form.getValues();

        if (!form.getForm().isValid() || !(selected instanceof Ext.data.Model)) {
            return false;
        }
        var model = Ext.create('Shopware.apps.Article.model.Accessory', values);
        model.set('id', selected.get('id'));
        model.set('name', selected.get('name'));
        model.set('number', selected.get('number'));

        //check if the article is already assigned
        var exist = store.getById(model.get('id'));
        if (!(exist instanceof Ext.data.Model)) {
            store.add(model);
            form.getForm().reset();
        } else {
            Shopware.Notification.createGrowlMessage(me.snippets.existTitle,  Ext.String.format(me.snippets.similar.exist, model.get('number')), me.snippets.growlMessage);
        }

    },

    /**
     * Event will be fired when the user want to remove an assigned similar article
     *
     * @event
     */
    onRemoveAccessoryArticle: function(grid, record) {
        var me = this,
            store = grid.getStore();

        if (record instanceof Ext.data.Model) {
            store.remove(record);
        }
    },

    /**
     * Event will be fired when the user wants to assign a product stream
     *
     * @event
     */
    onAddStream: function(form, grid, streamSelection) {
        var me = this,
            store = grid.getStore(),
            values = form.getValues(),
            streamModel, model, exist;

        if (!form.getForm().isValid()) {
            return;
        }

        streamModel = streamSelection.store.getById(values.id);
        if(streamModel instanceof Ext.data.Model) {
            values.description = streamModel.get('description');
            values.name = streamModel.get('name');
        }

        model = Ext.create('Shopware.apps.Article.model.Stream', values);

        // Check if product stream is already assigned
        exist = store.getById(model.get('id'));
        if (!(exist instanceof Ext.data.Model)) {
            store.add(model);
            form.getForm().reset();
        } else {
            Shopware.Notification.createGrowlMessage(
                me.snippets.existTitle,
                Ext.String.format(me.snippets.streams.exist, model.get('id'))
            );
        }
    },

    /**
     * Event will be fired when the user wants to remove an assigned product stream.
     *
     * @event
     */
    onRemoveStream: function(grid, record) {
        var store = grid.getStore();

        if (record instanceof Ext.data.Model) {
            store.remove(record);
        }
    }
});
//{/block}
