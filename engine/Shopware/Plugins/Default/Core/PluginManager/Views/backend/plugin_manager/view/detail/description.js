/**
 * Shopware 4
 * Copyright © shopware AG
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
 * @package    Order
 * @subpackage View
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Stephan Pohl
 * @author     $Author$
 */

//{namespace name=backend/plugin_manager/main}
//{block name="backend/plugin_manager/view/detail/description"}
Ext.define('Shopware.apps.PluginManager.view.detail.Description', {
    extend: 'Ext.panel.Panel',
    alias: 'widget.plugin-manager-detail-description',
    autoScroll: true,
    border: 0,
    layout: 'border',
    cls: Ext.baseCSSPrefix + 'plugin-manager-detail-description',

	snippets:{
		show_com_store: '{s name=detail/description/show_com_store}Show the community store{/s}',
		get_test_version: '{s name=detail/description/get_test_version}Request a test version{/s}',
		to_forum: '{s name=detail/description/to_forum}To the forum{/s}',
		category: '{s name=detail/description/category}Category{/s}',
		supplier: '{s name=detail/description/supplier}Supplier{/s}',
		released: '{s name=detail/description/released}Released{/s}',
		version: '{s name=detail/description/version}Version{/s}',
		compatible_with: '{s name=detail/description/compatible_with}Compatible with{/s}',
		license: '{s name=detail/description/license}License{/s}',
		meta_data: '{s name=detail/description/meta_data}Meta data{/s}',
		rent_version: '{s name=detail/description/rent_version}Rent version{/s}',
		buy_version: '{s name=detail/description/buy_version}Buy version{/s}',
		free: '{s name=detail/description/free}Free{/s}',
		vat_prices: '{s name=detail/description/vat_prices}* All prices incl. VAT{/s}',
		customer_ratings: '{s name=detail/description/customer_ratings}Customer ratings{/s}',
		from: '{s name=detail/description/from}From{/s}'
	},

    /**
     * Initializes the component.
     *
     * @public
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.leftContainer = me.createLeftContainer();
        me.description = me.createDescription();
        me.votes = me.createVotes();

        var container = Ext.create('Ext.container.Container', {
            padding: 10,
            autoScroll: true,
            region: 'center',
            items: [ me.description, me.votes ]
        });

        me.items = [ me.leftContainer, container ];

        me.callParent(arguments);
    },

    /**
     * Creates the container on the left hand of the detail window.
     * It displays the meta information, the thumbnail and the
     * price.
     *
     * @public
     * @return [object] Ext.container.Container
     */
    createLeftContainer: function() {
        var me = this,
            attributes = me.article.getAttributeStore.first().data,
            categoryNames = me.getCategoryNames(me.article.getCategoryStore),
            thumbPath;

        if(me.article.getMediaStore && me.article.getMediaStore.first()) {
            var media = me.article.getMediaStore.first().data;
            thumbPath = media.thumbnails[5];
        } else {
            thumbPath = '{link file="frontend/_resources/images/no_picture.jpg"}';
        }

        var image = Ext.create('Ext.Img', {
            src: thumbPath,
            autoEl: 'div',
            cls: Ext.baseCSSPrefix + 'outer-thumb'
        });

        var priceContainer = me.createPriceContainer(),
            metaPanel = me.createMetaPanel(),
            linkContainer = me.createLinkContainer();

        return Ext.create('Ext.container.Container', {
            region: 'west',
            width: 260,
            padding: 10,
            items: [ image, priceContainer, metaPanel, linkContainer ]
        });
    },

    createLinkContainer: function() {
        var me = this, links = [], link, attribute = null, container, url, detail = null;

        if (me.article && me.article.getAttribute() instanceof Ext.data.Store && me.article.getAttribute().first() instanceof Ext.data.Model) {
            attribute = me.article.getAttribute().first();
        }
        if (me.article && me.article.getDetail() instanceof Ext.data.Store && me.article.getDetail().first() instanceof Ext.data.Model) {
            detail = me.article.getDetail().first();
        }

        if (attribute !== null && attribute.get('store_url').length > 0) {
            link = '<a target="_blank" href="[0]">'+me.snippets.show_com_store+'</a>';
            link = Ext.String.format(link, attribute.get('store_url'));
            container = Ext.create('Ext.container.Container', {
                cls: Ext.baseCSSPrefix + 'product-link',
                html: link,
                margin: '5 0 0'
            });
            links.push(container);
        }

        if (attribute && attribute.get('test_modus') && detail !== null) {
            link = '<a target="_blank" href="[0]">'+me.snippets.get_test_version+'</a>';
            link = Ext.String.format(link, attribute.get('store_url'));
            container = Ext.create('Ext.container.Container', {
                cls: Ext.baseCSSPrefix + 'product-link',
                html: link,
                margin: '5 0 0'
            });
            links.push(container);
        }

        if (attribute !== null && attribute.get('forum_url').length > 0) {
            link = '<a target="_blank" href="[0]">'+me.snippets.to_forum+'</a>';
            link = Ext.String.format(link, attribute.get('forum_url'));
            container = Ext.create('Ext.container.Container', {
                cls: Ext.baseCSSPrefix + 'product-link',
                html: link,
                margin: '5 0 0'
            });
            links.push(container);
        }

        return Ext.create('Ext.container.Container', {
            items: links,
            margin: '10 0 0'
        });
    },

    /**
     * Builds up the meta information panel.
     *
     * @public
     * @return [object] Ext.container.Container
     */
    createMetaPanel: function() {
        var me = this,
            attributes = me.article.getAttributeStore.first().data,
            categoryNames = me.getCategoryNames(me.article.getCategoryStore),
            date = me.article.data.datum,
            formatted = Ext.util.Format.date(date);

        var metaData = '<p><strong>'+me.snippets.category+':</strong>' + categoryNames +'</p>' +
           '<p><strong>'+me.snippets.supplier+':</strong>' + me.article.get('supplierName') + '</p>' +
           '<p><strong>'+me.snippets.released+':</strong>'+ formatted +'</p>'+
           '<p><strong>'+me.snippets.version+':</strong>'+attributes.version+'</p>'+
           '<p><strong>'+me.snippets.compatible_with+':</strong> ab '+ Ext.String.trim(attributes.shopware_compatible)+'</p>' +
           '<p><strong>'+me.snippets.license+':</strong> '+ me.article.data.licence.name +'</p>';

        return Ext.create('Ext.panel.Panel', {
            bodyPadding: 10,
            title: me.snippets.meta_data,
            cls: Ext.baseCSSPrefix + 'meta-panel',
            html: metaData
        });
    },

    /**
     * Creates the price container, which contains the article price (makes sense...)
     * and if the article has an rent version, it displays a select box which offers
     * the customer the ability to select the version he wants to buy.
     *
     * @public
     * @return [object] Ext.container.Container
     */
    createPriceContainer: function() {
        var me = this, container, price,
            detail = me.article.getDetailStore,
            store, data = [], priceContainer

        detail.each(function(item) {
            data.push({
                display: (item.get('rent_version') ? me.snippets.rent_version : me.snippets.buy_version),
                value: (item.get('rent_version') ? 'rent' : 'buy'),
                ordernumber: item.get('ordernumber'),
                price : item.get('price'),
                articleId: item.get('id')
            });
        });

        store = Ext.create('Ext.data.Store', {
            fields: [ 'display', 'value', 'ordernumber', 'price', 'articleId' ],
            data: data
        });
        container = Ext.create('Ext.container.Container', {
            layout: 'anchor',
            margin: '0 0 10'
        });
        priceContainer = Ext.create('Ext.container.Container', {
            cls: Ext.baseCSSPrefix + 'article-price'
        });

        if(data.length > 1) {
            var select = Ext.create('Ext.form.field.ComboBox', {
                anchor: '100%',
                triggerAction: 'all',
                fieldLabel: 'Typ',
                store: store,
                name: 'versionCombo',
                displayField: 'display',
                valueField: 'value',
                listeners: {
                    scope: me,
                    change: function(field, value) {
                        var activeRecord = store.findRecord('value', value),
                            price = activeRecord.get('price');

                        if(price === 0) {
                            price = me.snippets.free;
                        } else {
                            price += '&nbsp;&euro;&nbsp;*';
                        }

                        if(value === 'rent') {
                            price += '&nbsp;pro Monat';
                        }
                        priceContainer.update(price);
                    }
                }
            });
            select.setValue('buy');
            container.add(select);
        } else {
            priceContainer.update(data[0].price + '&nbsp;&euro;&nbsp;*');
        }

        container.add(priceContainer);
        container.add(Ext.create('Ext.container.Container', {
            html: me.snippets.vat_prices,
            style: 'color: #999; font-size: 11px;'
        }));

        return container;
    },

    /**
     * Creates a data view which contains the description and
     * the changelog of the plugin.
     *
     * @return [object] Ext.view.View
     */
    createDescription: function() {
        var me = this, data = {},
            attributes = me.article.getAttributeStore.first().data;

        Ext.apply(data, me.article.data);
        data.attributes = attributes;
        data.votes = [];

        return Ext.create('Ext.view.View', {
            cls: Ext.baseCSSPrefix + 'main-details',
            renderTpl: me.createDescriptionTemplate(),
            tpl: me.createDescriptionTemplate(),
            renderData: data
        });
    },

    /**
     * Creates a data view which contains the votes of the plugin.
     *
     * @return [object] Ext.view.View
     */
    createVotes: function() {
        var me = this;

        return Ext.create('Ext.view.View', {
            cls: Ext.baseCSSPrefix + 'main-details',
            tpl: me.createVoteTemplate(),
            store: me.voteStore
        });
    },

    createVoteTemplate: function() {
		var me = this;

        return new Ext.XTemplate(
			'{literal}',
				'<div class="changelog-headline">'+me.snippets.customer_ratings+'</div>',
				'<tpl for=".">',
					'<div class="user-comment">',
						'<h3>{headline}<div class="star star{points * 2}">{points * 2}</div></h3>',
						'<div class="meta">'+me.snippets.from+' {author} - {creation_date}</div>',
						'<div class="comment">{comment}</div>',
					'</div>',
				'</tpl>',
			'{/literal}'
        )
    },

    /**
     * Creates the template for the description view.
     *
     * @public
     * @return Ext.XTemplate
     */
    createDescriptionTemplate: function() {
        return new Ext.XTemplate(
            '{literal}',
                '<h2 class="plugin-name">{name}</h2>',
                '<div class="description">',
                    '{description}',
                '</div>',
                '<tpl if="attributes.changelog">',
                    '<div class="changelog">',
                         '<h3 class="changelog-headline">Changelog</h3>',
                        '{attributes.changelog}',
                    '</div>',
                '</tpl>',
            '{/literal}'
        );
    },

    /**
     * Helper method which concatenates the category names
     * to just one single string
     *
     * @public
     * @param [object] categoryStore - Shopware.apps.PluginManager.model.Category
     * @return [string] - concatenated category names
     */
    getCategoryNames: function(categoryStore) {
        var categoryNames = '';

        categoryStore.each(function(category) {
            categoryNames += category.get('description') + ','
        });

        categoryNames = categoryNames.substring(0, categoryNames.length-1);
        return categoryNames;
    }
});
//{/block}
