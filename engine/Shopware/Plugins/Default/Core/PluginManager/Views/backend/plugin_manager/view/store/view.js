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
//{block name="backend/plugin_manager/view/store/view"}
Ext.define('Shopware.apps.PluginManager.view.store.View', {
    extend: 'Ext.panel.Panel',
    unstyled: true,
    alias: 'widget.plugin-manager-store-view',
    autoScroll: true,
    border: 0,
    cls: Ext.baseCSSPrefix + 'plugin-manager-store-view',

	snippets: {
		vat_price: '{s name=store/view/vat_price}* All prices incl. VAT{/s}',
		show_all: '{s name=store/view/show_all}Show all{/s}',
		hint: '{s name=store/view/hint}Hint{/s}',
		of: '{s name=store/view/of} of {/s}',
		free: '{s name=store/view/free}Free{/s}'
	},

    /**
     * Initializes the component
     *
     * @public
     * @constructor
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.addEvents('changeCategory', 'openArticle');
        me.topSellerView = me.createHighlightsView();
        me.categoryView = me.createCategoryView();

        me.items = [ me.topSellerView, me.categoryView ];
        me.dockedItems = [ me.createTaxNotice() ];

        me.callParent(arguments);
    },

    createTaxNotice: function() {
		var me = this;
        return Ext.create('Ext.container.Container', {
            dock: 'bottom',
            cls: Ext.baseCSSPrefix + 'tax-notice',
            html: me.snippets.vat_price
        });
    },

    /**
     * Creates the highlight (= topseller) view.
     *
     * @public
     * @return [object] Ext.view.View
     */
    createHighlightsView: function() {
        var me = this, store = me.topSellerStore;

        return Ext.create('Ext.view.View', {
            itemSelector: '.clickable',
            store: store,
            tpl: me.createStoreViewTemplate('highlights', 2),
            cls: Ext.baseCSSPrefix + 'community-highlights',
            listeners: {
                scope: me,
                afterrender: function(view) {
                    var el = view.getEl();
                    el.on('click', function(e, t) {
                        var targetEl = Ext.get(t),
                            href = targetEl.getAttribute('href'),
                            articleId, categoryId

                        if(href == '#open-details') {
                            articleId = targetEl.getAttribute('data-articleId');
                            categoryId = targetEl.getAttribute('data-categoryId');
                            categoryId = ~~(1 * categoryId);

                            var record = me.communityStore.getById(categoryId);
                            me.fireEvent('openArticle', articleId, record, 'community');
                            window.location.hash = '';
                        } else {
                            return false;
                        }
                    }, me, { delegate: '.should-clickable' });
                }
            }
        });
    },

    createCategoryView: function() {
        var me = this, store = me.communityStore;
        return Ext.create('Ext.view.View', {
            itemSelector: '.clickable',
            store: store,
            tpl: me.createStoreViewTemplate('listing', 3),
            cls: Ext.baseCSSPrefix + 'community-categories',
            listeners: {
                scope: me,
                afterrender: function(view) {
                    var el = view.getEl();
                    el.on('click', function(e, t) {
                        var targetEl = Ext.get(t),
                            href = targetEl.getAttribute('href'),
                            categoryId, articleId, record;

                        if(href == '#show-all') {
                            categoryId = targetEl.getAttribute('data-action');
                            categoryId = ~~(1 * categoryId);

                            record = me.categoryStore.getById(categoryId);
                            me.fireEvent('changeCategory', me.categoryView, record, t);
                            window.location.hash = '';
                        } else if(href == '#open-details') {
                            articleId = targetEl.getAttribute('data-articleId');
                            categoryId = targetEl.getAttribute('data-categoryId');
                            categoryId = ~~(1 * categoryId);

                            record = me.communityStore.getById(categoryId);
                            me.fireEvent('openArticle', articleId, record, 'community');
                            window.location.hash = '';
                        } else {
                            return false;
                        }
                    }, me, { delegate: '.should-clickable' });
                }
            }
        });
    },

    createStoreViewTemplate: function(cls, perPage) {
        var me = this;

        return new Ext.XTemplate(
            '{literal}<div class="' + cls + '">',
                    '<tpl for=".">',
                        '<tpl if="this.checkCategoryFilled(values) === true">',
                        '<section>',
                            '<div class="section-header">',
                                '<h2>{description}</h2>',
                                '<a class="should-clickable" data-action="{id}" href="#show-all">'+me.snippets.show_all+' &raquo;</a>',
                            '</div>',
                            '{[this.createRows(values, '+ perPage +')]}',
                        '</section>',
                        '</tpl>',
                    '</tpl>',
            '</div>{/literal}',
            {
                checkCategoryFilled: function(values) {
                    return !!values.products.length;
                },
                createRows: function(values, perRow) {
                    var maxCount = values.products.length,
                        rowCount = Math.ceil(maxCount / perRow),
                        rows = '', index = 0;

                    for(var j = 0; j < rowCount; j++) {
                        var row = '<div class="row ' + (j % 2 ? 'row-alt' : 'row') + ' clearfix">';
                        for(var i = 0; i < perRow; i++) {
                            if(values.products[index]) {
                                row += this.createColumn(values.products[index], perRow, values.id);
                            }
                            index++;
                        }
                        row += '</div>';
                        rows += row;
                    }
                    return rows;
                },

                createColumn: function(values, perRow, categoryId) {
                    var column = '<div class="column">';

                    if(values.addons && values.addons.highlight) {
                        column += '<div class="highlight"><strong>'+me.snippets.hint+'</strong></div>';
                    }

                    column += '<div class="thumb">' + this.createThumbnail(values, perRow) + '</div>';
                    column += this.createDetails(values, categoryId);
                    column += '</div>';
                    return column;
                },

                createDetails: function(values, categoryId) {
                    var details = '<div class="detail">';
                    if(values.name) {
                        details += '<h3>' + Ext.String.ellipsis(values.name, 40) + '</h3>';
                    }

                    if(values.attributes && values.supplierName) {
                        details += '<span class="version">v' + values.attributes.version + me.snippets.of + Ext.String.ellipsis(values.supplierName, 18) + '</span>';
                    }

                    if(values.description) {
                        details += '<p class="description">' + Ext.util.Format.stripTags(values.description) + '</p>';
                    }

                    var price = '';
                    Ext.each(values.details, function(item) {
                        if(item.rent_version === true) {
                            price = item.price;
                        }
                    });

                    if(!price) {
                        price = values.details[0].price;
                    } else {
                        price = 'ab ' + price;
                    }

                    if(price == 0) {
                        price = me.snippets.free;
                    } else {
                        price += '&euro;&nbsp;*';
                    }
                    if (values.vote_average) {
                        var starCount = ~~(1 * values.vote_average) * 2;
                        details += '<div class="stars star' + starCount + '"></div>'
                    }

                    details += '<div class="action">';
                        details += '<div class="price">' + price + '</div>';
                        details += '<a class="should-clickable buy" data-categoryId="'+categoryId+'" data-articleId="' + values.id + '" href="#open-details">Details &raquo;</a>';
                    details += '</div>';

                    details += '</div>';
                    return details;
                },

                createThumbnail: function(values, perRow) {
                    var images = values.images, image, ret = '';

                    if(values.attributes.certificate == '1') {
                        ret += '<div class="certificated"></div>';
                    }
                    // No picture image
                    if(!images.length) {
                        ret += '<div class="inner-thumb" style="background-image: url({link file="frontend/_resources/images/no_picture.jpg"})">'+values.name+'</div>';
                        return ret;
                    }

                    // Get the thumbnail
                    if(images[0].thumbnails && images[0].thumbnails[3]) {

                        if(perRow == 2) {
                            image = images[0].thumbnails[3];
                        } else {
                            image = images[0].thumbnails[2];
                        }
                    }

                    ret += '<div class="inner-thumb" style="background-image: url(' + image + ')">'+values.name+'</div>';
                    return ret;
                }
            }
        );
    }
});
//{/block}
