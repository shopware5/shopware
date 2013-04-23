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
 * @package    Article
 * @subpackage Main
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware Controller - Article backend module
 */
//{namespace name=backend/article/view/main}
//{block name="backend/article/controller/main"}
Ext.define('Shopware.apps.Article.controller.Main', {

    /**
     * The parent class that this class extends.
     * @string
     */
    extend:'Ext.app.Controller',

    /**
     * Class property which holds the main application if it is created
     *
     * @default null
     * @object
     */
    mainWindow: null,

    snippets: {
        additional: {
            title:'{s name=detail/additional_fields/title}Additional fields{/s}'
        },
		generateNeeded: {
			title: '{s name=detail/needGenerate/title}Duplicate article{/s}',
			message: '{s name=detail/needGenerate/message}You have just duplicated a configurator article. The corresponding configurator variants were not duplicated for performance and stability reasons. ' +
					 'Please go to the tab \'Variants\' and then click \'Generate variants\' to duplicate the configurator variants, too.{/s}'
		}
    },

    /**
     * A template method that is called when your application boots.
     * It is called before the Application's launch function is executed
     * so gives a hook point to run any code before your Viewport is created.
     *
     * @params orderId - The main controller can handle a orderId parameter to open the order detail page directly
     * @return void
     */
    init:function () {
        var me = this;

        //article id passed? Then open the detail page with the passed article id
        if (me.subApplication.params && me.subApplication.params.articleId > 0) {
			if(me.subApplication.params.needGenerate){
				Ext.Msg.alert(me.snippets.generateNeeded.title, me.snippets.generateNeeded.message);
			}
            me.openExistingArticle(me.subApplication.params.articleId);
        //no parameter passed? open article detail page with new record
        } else {
            me.openNewArticle();
        }
        me.callParent(arguments);
    },

    /**
     * Opens the article detail page.
     * @return Ext.window.Window
     */
    openMainWindow: function() {
        var me = this;

        me.mainWindow = me.getView('detail.Window').create();
        me.subApplication.setAppWindow(me.mainWindow);
        me.mainWindow.setLoading(true);
        return me.mainWindow;
    },

    /**
     * Internal helper function which open the detail window with a new article record.
     */
    openNewArticle: function() {
        var me = this;

        // Create the main window
        me.openMainWindow();

        //the batch store is responsible to load all required stores for the detail page in one request
        me.batchStore = me.getStore('Batch');
        me.batchStore.getProxy().extraParams.articleId = null;
        me.batchStore.load({
            callback: function(records, operation) {
                var storeData = records[0];

                //when store has been loaded use the first record as data array to create the required stores
                if (operation.success === true) {
                    //prepare the associated stores to use them in the detail page
                    var stores = me.prepareAssociationStores(storeData);
                    var article = me.prepareArticleDefaults(storeData, stores);
                    me.subApplication.article = article;

                    //create the detail window and pass the prepared stores
                    Ext.apply(me.mainWindow, {
                        _batchStore: me.batchStore,
                        article: article,
                        customerGroupStore: stores['customerGroups'],
                        shopStore: stores['shops'],
                        taxStore: stores['taxes'],
                        attributeFieldSet: me.createAdditionalFieldSet(stores['attributeFields']),
                        attributeFields: stores['attributeFields'],
                        supplierStore: stores['suppliers'],
                        templateStore: stores['templates'],
                        dependencyStore: stores['dependencyStore'],
                        priceSurchargeStore: stores['priceSurchargeStore'],
                        unitStore: stores['unit'],
                        propertyStore: stores['properties'],
                        priceGroupStore: stores['priceGroups'],
                        articleConfiguratorSet: stores['articleConfiguratorSet'],
                        categoryTreeStore: stores['categories'],
                        configuratorGroupStore: stores['configuratorGroups']
                    });

                    var tabPanel = me.mainWindow.createMainTabPanel();
                    me.mainWindow.insert(0, tabPanel);
                    me.mainWindow.setLoading(false);
                }
            }
        });
    },

    /**
     * Internal helper function which prepares the data for a new article
     * @param storeData
     * @param stores
     * @return
     */
    prepareArticleDefaults: function(storeData, stores) {
        var firstTax = stores['taxes'].getAt(0),
            articleData = storeData.raw.article;

        var article = Ext.create('Shopware.apps.Article.model.Article', articleData),
            attribute = Ext.create('Shopware.apps.Article.model.Attribute', articleData.attribute),
            detail = Ext.create('Shopware.apps.Article.model.Detail', articleData );


        article.set('taxId', firstTax.get('id'));
        detail.set('kind', 1);

        article.getMainDetail().add(detail);
        article.getAttribute().add(attribute);
        return article;
    },

    /**
     * Helper function which opens the detail window with the passed article record.
     */
    openExistingArticle: function(id) {
        var me = this;

        // Create main window
        me.openMainWindow();

        //the batch store is responsible to load all required stores for the detail page in one request
        me.batchStore = me.getStore('Batch');
        me.batchStore.getProxy().extraParams.articleId = id;
        me.batchStore.load({
            callback: function(records, operation) {
                var storeData = records[0];

                //when store has been loaded use the first record as data array to create the required stores
                if (operation.success === true) {

                    //prepare the associated stores to use them in the detail page
                    var stores = me.prepareAssociationStores(storeData);
                    var article = storeData.getArticle().first();
                    me.subApplication.article = article;

                    //create the detail window and pass the prepared stores
                    Ext.apply(me.mainWindow, {
                        _batchStore: me.batchStore,
                        article: article,
                        customerGroupStore: stores['customerGroups'],
                        shopStore: stores['shops'],
                        taxStore: stores['taxes'],
                        attributeFieldSet: me.createAdditionalFieldSet(stores['attributeFields']),
                        attributeFields: stores['attributeFields'],
                        supplierStore: stores['suppliers'],
                        templateStore: stores['templates'],
                        unitStore: stores['unit'],
                        propertyStore: stores['properties'],
                        dependencyStore: stores['dependencyStore'],
                        priceSurchargeStore: stores['priceSurchargeStore'],
                        priceGroupStore: stores['priceGroups'],
                        categoryTreeStore: stores['categories'],
                        articleConfiguratorSet: stores['articleConfiguratorSet'],
                        configuratorGroupStore: stores['configuratorGroups']
                    });
                    var tabPanel = me.mainWindow.createMainTabPanel();
                    me.mainWindow.insert(0, tabPanel);

                    me.getController('Detail').loadPropertyStore(article);

                    me.mainWindow.changeTitle();
                    me.mainWindow.setLoading(false);
                }
            }
        });
    },




    /**
     * The passed data object is the batch model which contains associations for each store
     * which is used for the detail page data selection, like the price group combo box or the supplier combo box.
     * This function creates an array which the different stores and sets the proxies for the this stores
     * to refresh the data later.
     *
     * @param Shopware.apps.Article.model.Batch data
     */
    prepareAssociationStores: function(data) {
        var me = this, globalGroup, articleConfiguratorSet = null,
            dependencyStore = null, priceSurchargeStore = null,
            article = data.getArticle().first(), articleOptions,
            globalOptions, globalOption,
            stores = [];

        var supplierStore = Ext.create('Shopware.store.Supplier', {
            remoteFilter: false
        });
        var suppliers = data.getSuppliers();
        supplierStore.add(suppliers.data.items);

        stores['customerGroups'] = data.getCustomerGroups();
        stores['shops'] = data.getShops();
        stores['taxes'] = data.getTaxes();
        stores['suppliers'] = supplierStore;
        stores['templates'] = data.getTemplates();
        stores['unit'] = data.getUnits();
        stores['properties'] = data.getProperties();
        stores['priceGroups'] = data.getPriceGroups();
        stores['attributeFields'] = data.getAttributeFields();

        me.subApplication.firstCustomerGroup = data.getCustomerGroups().first();

        //get the store for the global configurator groups.
        var configuratorGroupStore = data.getConfiguratorGroups();
        configuratorGroupStore.each(function(item) {
             item.set('active', false);
        });

        if (article) {
            //get the configurator set of the current article
            articleConfiguratorSet = article.getConfiguratorSet().first();
            dependencyStore = article.getDependencies();
            priceSurchargeStore = article.getPriceSurcharges();
        } else {
            dependencyStore = Ext.create('Ext.data.Store', { model: 'Shopware.apps.Article.model.Dependency' });
            priceSurchargeStore = Ext.create('Ext.data.Store', { model: 'Shopware.apps.Article.model.PriceSurcharge' });
        }

        if (articleConfiguratorSet) {
            //get the defined configurator groups which used for the current article configurator set.
            var groups = articleConfiguratorSet.getConfiguratorGroups();
            articleOptions = articleConfiguratorSet.getConfiguratorOptions();

            //iterate all configurator groups of the article
            groups.each(function(group) {
                //get the global configurator group by the id of the used configurator set group.
                globalGroup = configuratorGroupStore.getById(group.get('id'));

                if (!globalGroup) {
                    return true;
                }

                //set the global group to active, that means the article has already configurations for the group
                globalGroup.set('active', true);
                globalOptions = globalGroup.getConfiguratorOptions();

                //if no article options exists, continue
                if (!articleOptions) {
                    return true;
                }

                //iterate the configured article options to set the active flag in the global options
                articleOptions.each(function(articleOption) {
                    //find the global options with the same id.
                    globalOption = globalOptions.getById(articleOption.get('id'));

                    //set active flag to true
                    if (globalOption) {
                        globalOption.set('active', true);
                    }
                });
            });
        }

        configuratorGroupStore.each(function(globalGroup) {
            globalOptions = globalGroup.getConfiguratorOptions();
            globalOptions.groupField = 'active';
            globalOptions.group('active', 'DESC');
            globalOptions.sort([
                { property: 'active', 'direction': 'DESC' },
                { property: 'position', 'direction': 'ASC' }
            ]);
        });

        //set store grouping field
        configuratorGroupStore.groupField = 'active';

        //group the current records
        configuratorGroupStore.group('active', 'DESC');
        configuratorGroupStore.sort([
            { property: 'active', 'direction': 'DESC' },
            { property: 'position', 'direction': 'ASC' }
        ]);
        me.subApplication.configuratorGroupStore = configuratorGroupStore;

        stores['configuratorGroups'] = configuratorGroupStore;
        stores['dependencyStore'] = dependencyStore;
        stores['priceSurchargeStore'] = priceSurchargeStore;
        stores['articleConfiguratorSet'] = articleConfiguratorSet;

        var treeStore = Ext.create('Shopware.apps.Article.store.CategoryTree').load();
        stores['categories'] = treeStore;

        return stores;
    },

    /**
     * Creates the field set with the additional article fields.
     * @return Ext.form.FieldSet
     */
    createAdditionalFieldSet: function(attributeFields) {
        var me = this, fields = [];

        if (attributeFields && attributeFields.getCount() > 0) {
            attributeFields.each(function(item) {
                 fields.push(me.createAttributeField(item));
            });
        }

        return Ext.create('Ext.form.FieldSet', {
            layout: 'anchor',
            cls: Ext.baseCSSPrefix + 'article-additional-fields-field-set',
            defaults: {
                labelWidth: 155,
                anchor: '100%',
                xtype: 'textfield'
            },
            title: me.snippets.additional.title,
            items: fields
        });
    },

    createAttributeField: function(fieldModel) {
        var me = this, field = Ext.create('Ext.form.field.Text');

        switch(fieldModel.get('type')) {
            case 'text':
                field = Ext.create('Ext.form.field.Text', {
                    fieldLabel: fieldModel.get('label'),
                    name: 'attribute[' + fieldModel.get('name') + ']',
                    translationName: fieldModel.get('name'),
                    translatable: fieldModel.get('translatable')
                });
                break;
            case 'boolean':
                field = Ext.create('Ext.form.field.Checkbox', {
                    inputValue: true,
                    uncheckedValue: false
                });
                break;
            case 'select':
                if (fieldModel.get('store') == "ArrayStore"){
                    field = Ext.create('Ext.form.field.ComboBox', {
                       store: Ext.create('Ext.data.ArrayStore',{
                           fields: [
                            'id','name'
                           ],
                           data: Ext.JSON.decode(fieldModel.get('default'))
                       }),
                       valueField: 'id',
                       forceSelection: true,
                       displayField: 'name'
                   });

                }else {
                    field = Ext.create('Ext.form.field.ComboBox', {
                        store: fieldModel.get('store'),
                        valueField: 'id',
                        forceSelection: true,
                        displayField: 'name'
                    });

                }
                break;
            case 'date':
                field = Ext.create('Ext.form.field.Date');
                break;
            case 'number':
                field = Ext.create('Ext.form.field.Number');
                break;
            case 'textarea':
                field = Ext.create('Ext.form.field.TextArea', {
                    fieldLabel: fieldModel.get('label'),
                    name: 'attribute[' + fieldModel.get('name') + ']',
                    translationName: fieldModel.get('name'),
                    translatable: fieldModel.get('translatable')
                });
                break;
            case 'time':
                field = Ext.create('Ext.form.field.Time', {
                    increment: 10
                });
                break;
            case 'html':
                field = Ext.create('Ext.form.field.TinyMCE', {
                    fieldLabel: fieldModel.get('label'),
                    name: 'attribute[' + fieldModel.get('name') + ']',
                    translationName: fieldModel.get('name'),
                    translatable: fieldModel.get('translatable')
                });
                break;
            case 'article':
                field = Ext.create('Shopware.form.ArticleSearch', {
                    store: fieldModel.get('store'),
                    fieldLabel: fieldModel.get('label'),
                    valueField: 'id',
                    anchor: '100%',
                    forceSelection: true,
                    displayField: 'name'
                });
                break;
            default:
        }

        Ext.apply(field, {
            fieldLabel: fieldModel.get('label'),
            allowBlank: (!fieldModel.get('required')),
            value: fieldModel.get('default'),
            anchor: '100%',
            helpText: fieldModel.get('help'),
            editable: true,
            name: 'attribute[' + fieldModel.get('name') + ']'
        });
        return field;
    }

});
//{/block}
