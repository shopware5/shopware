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
 * Shopware Controller - Variant
 * The variant controller handles all events of the views in the variant namespace.
 */
//{namespace name=backend/article/view/main}
//{block name="backend/article/controller/variant"}
Ext.define('Shopware.apps.Article.controller.Variant', {

    /**
     * The parent class that this class extends.
     * @string
     */
    extend:'Ext.app.Controller',

    refs: [
        { ref: 'mainWindow', selector: 'article-detail-window' },
        { ref: 'saveArticleButton', selector: 'article-detail-window button[name=save-article-button]' },
        { ref: 'saveConfiguratorButton', selector: 'article-detail-window button[name=configurator-save-button]' },
        { ref: 'variantListing', selector: 'article-detail-window article-variant-list' },
        { ref: 'configuratorGroupListing', selector: 'article-detail-window article-variant-configurator grid[name=configurator-group-listing]' },
        { ref: 'configuratorTypeCombo', selector: 'article-detail-window article-variant-configurator combobox[name=type]' },
        { ref: 'configuratorOptionListing', selector: 'article-detail-window article-variant-configurator grid[name=configurator-option-listing]' },
        { ref: 'configurator', selector: 'article-detail-window article-variant-configurator' },
        { ref: 'configuratorTabPanel', selector: 'article-detail-window container[name=variant-tab] tabpanel[name=configurator-tab]' },
        { ref: 'dependencyWindow', selector: 'article-configurator-dependency-window' },
        { ref: 'dependencyFieldSet', selector: 'article-configurator-dependency-window fieldset[name=row-field-set]' }
    ],

    snippets: {
        growlMessage: '{s name=growl_message}Article{/s}',
        generateNumberProcess: '{s name=generate_numbers_text}Generate numbers [0] of [1]{/s}',
        generateNumbersDone: '{s name=generate_numbers_done}Numbers generated{/s}',

        success: {
            title: '{s name=variant/success/title}Success{/s}',
            groupSave: '{s name=variant/success/group_saved}The configurator group [0] saved.{/s}',
            optionSave: '{s name=variant/success/option_saved}The configurator option [0] saved{/s}',
            variantSave: '{s name=variant/success/variant_save}The article variant [0] saved{/s}',
            setSave: '{s name=variant/success/set_saved}The configurator set [0] saved{/s}',
            setLoad: '{s name=variant/success/set_loaded}The configurator set [0] loaded{/s}',
            dependencySave: '{s name=variant/success/dependency_saved}The configurator dependency saved{/s}',
            groupRemove: '{s name=variant/success/group_removed}The configurator group [0] removed{/s}',
            optionRemove: '{s name=variant/success/option_removed}The configurator option [0] removed{/s}',
            dependencyRemove: '{s name=variant/success/dependency_removed}The configurator dependency [0] removed{/s}',
            variantRemove: '{s name=variant/success/variant_removed}The article variant [0] removed{/s}',
            variantsRemove: '{s name=variant/success/variants_removed}The article variants removed{/s}'
        },
        failure: {
            title: '{s name=variant/failure/title}Failure{/s}',
            noIdViolation: '{s name=variant/failure/no_id_violation}No id passed to the php controller action{/s}',
            boundedArticlesViolation: '{s name=variant/failure/bounded_articles_violation}The following articles bounded on the configurator [0]:{/s}',
            unknownViolation: '{s name=variant/failure/unknown_violation}An unknown exception occurred:{/s}',
            noMoreInformation: '{s name=variant/failure/no_more_information}No more information available.{/s}',
            groupSave: '{s name=variant/failure/group_saved}An error occurred while saving the configurator group [0]:{/s}',
            optionSave: '{s name=variant/failure/option_saved}An error occurred while saving the configurator option [0]:{/s}',
            variantSave: '{s name=variant/failure/variant_saved}An error occurred while saving the article variant [0]:{/s}',
            setSave: '{s name=variant/failure/set_saved}An error occurred while saving the configurator set [0]:{/s}',
            setLoad: '{s name=variant/failure/set_loaded}An error occurred while loading the configurator set [0]:{/s}',
            dependencySave: '{s name=variant/failure/dependency_saved}An error occurred while saving the configurator dependency:{/s}',
            groupRemove: '{s name=variant/failure/group_removed}An error occurred while removing the configurator group [0]:{/s}',
            groupBounded: '{s name=variant/failure/group_bounded}You are trying to delete an active, used configurator group. This group is used by the following articles:{/s}',
            optionRemove: '{s name=variant/failure/option_removed}An error occurred while removing the configurator option [0]:{/s}',
            optionBounded: '{s name=variant/failure/option_bounded}You are trying to delete an active, used configurator option. This option is used by the following articles:{/s}',
            dependencyRemove: '{s name=variant/failure/dependency_removed}An error occurred while removing the configurator dependency:{/s}',
            articleNotFoundViolation: "{s name=variant/failure/article_not_found_violation}The article and first variant couldn't be determined. Please reload the detail page.{/s}",
            variantRemove: '{s name=variant/failure/variant_removed}An error occurred while removing the article variant [0]:{/s}',
            variantsRemove: '{s name=variant/failure/variants_removed}The article variants removed{/s}',
            fieldsViolation: '{s name=variant/failure/fields_violation}The following fields are not valid:{/s}',
            generateNumbers: '{s name=variant/failure/generate_number}An error occurred while regenerate the order number:{/s}'
        },
        messages: {
            tableConfigurator: '{s name=variant/message/notice}A table configurator can only have two active groups!{/s}',
            warningTitle: '{s name=variant/message/option/warning_title}Warning{/s}',
            optionExists: '{s name=variant/message/option/option_exists}There is already an option named [0]{/s}',
            noValidForm: '{s name=variant/message/option/no_valid_form}The base data form panel is invalid, please check the values of the this tab.{/s}',
            articleNotSaved: "{s name=variant/message/option/article_not_saved}The article wasn't saved, please check the different tab panels for valid data.{/s}",
            saveArticleBefore: '{s name=variant/message/option/save_article_before_generate}If you generate article variants all current changes will be reverted, do you want to save the article first?{/s}',
            loadSetWarning: "{s name=variant/message/load_set_warning}The article already contains generated variants. If you load the selected set, please note that all generated variants will be deleted. Are you sure you want to continue the loading of the variant set?{/s}",
            groupRemove: '{s name=variant/message/remove_group}Are you sure, you want to delete the selected configurator group: [0]?{/s}',
            dependencyRemove: '{s name=variant/message/dependency_removed}Are you sure, you want to delete the selected configurator dependency?{/s}',
            optionRemove: '{s name=variant/message/remove_option}Are you sure you want to delete the selected configurator option: [0]?{/s}',
            generateVariants: '{s name=variant/message/generate_variants}The article already contains generated variants. Please note that all generated variants will be overwritten. Are you sure you want to continue the variant generation?{/s}',
            variantsRemove: '{s name=variant/message/variants_removed}Are you sure, you want to delete all selected article variants?{/s}',
            variantRemove: '{s name=variant/message/variant_removed}Are you sure, you want to delete the article variant: [0]?{/s}'
        },
        labels: {
            title: '{s name=variant/configurator/option_panel/title}Manage attribute options{/s}',
            titleLoaded: '{s name=variant/configurator/option_panel/title_loaded}Manage attribute options of group:{/s}'
        }

    },

    /**
     * A template method that is called when your application boots.
     * It is called before the Application's launch function is executed
     * so gives a hook point to run any code before your Viewport is created.
     *
     * @return void
     */
    init:function () {
        var me = this;
        me.control({
            'article-variant-progress-window': {
                variantsGenerated: me.onVariantsGenerated
            },
            'article-mapping-window': {
                acceptBaseData: me.onAcceptBaseData,
                cancel: me.onCancelEdit
            },
            'article-detail-window tabpanel[name=main-tab-panel]': {
                beforetabchange: me.onMainTabChange
            },
            'article-detail-window tabpanel[name=configurator-tab]': {
                tabchange: me.onConfiguratorTabChanged
            },
            'article-group-window': {
                cancel: me.onCancelEdit,
                saveGroup: me.onSaveGroup
            },
            'article-option-window': {
                cancel: me.onCancelEdit,
                saveOption: me.onSaveOption
            },
            'article-sets-window': {
                saveSet: me.onSaveConfiguratorSet,
                cancel: me.onCancelEdit,
                loadSet: me.onLoadConfiguratorSet
            },
            //all events of the variant listing component
            'article-detail-window article-variant-list': {
                deleteVariant: me.onDeleteVariant,
                deleteMultipleVariants: me.onDeleteVariant,
                saveVariant: me.onSaveVariantInline,
                searchVariants: me.onSearchVariants,
                editVariant: me.onEditVariant,
                generateOrderNumbers: me.onGenerateOrderNumbers,
                applyData: me.onDisplayMappingWindow,
                editVariantPrice: me.onEditVariantPrice,
                editVariantPseudoPrice: me.onEditVariantPseudoPrice,
                createVariants: me.onCreateVariants,
            },
            //global event of the configurator tab
            'article-detail-window article-variant-configurator': {
                displaySetSaveWindow: me.onDisplaySetSaveWindow,
                displaySetLoadWindow: me.onDisplaySetLoadWindow,
                groupClick: me.onGroupClick,
                groupSelect: me.onGroupSelect,
                groupDeselect: me.onGroupDeselect,
                groupDropped: me.onGroupDropped,
                optionSelect: me.onOptionSelect,
                optionDeselect: me.onOptionDeselect,
                optionDropped: me.onOptionDropped,
                createGroup: me.onCreateGroup,
                createOption: me.onCreateOption,
                deleteGroup: me.onDeleteGroup,
                deleteOption: me.onDeleteOption,
                editGroup: me.onEditGroup,
                editOption: me.onEditOption,
                defineDependency: me.onDefineDependency,
                defineConfiguratorTemplate: me.onDefineConfiguratorTemplate
            },
            'article-configurator-dependency-window': {
                leftGroupChanged: me.onLeftGroupChanged,
                rightGroupChanged: me.onRightGroupChanged,
                saveDependency: me.onSaveDependency,
                removeDependency: me.onRemoveDependency
            },
            'article-variant-detail-window': {
                saveVariant: me.onSaveVariant,
                cancelEdit: me.onCancelEdit,
                applyData: me.onApplyDataOnDetailPage
            },
            'article-configurator-template-window': {
                saveTemplate: me.onSaveTemplate,
                cancelEdit: me.onCancelEdit
            },
            'article-number-progress-window': {
                startNumberProcess: me.onStartNumberProcess,
                cancelNumberProcess: me.onCancelNumberProcess
            }
        });
        me.callParent(arguments);
    },

    onDefineConfiguratorTemplate: function() {
        var me = this,
            template = null,
            article = me.subApplication.article,
            listing = me.getVariantListing();

        if (article.getConfiguratorTemplate() instanceof Ext.data.Store && article.getConfiguratorTemplate().first() instanceof Ext.data.Model) {
            template = article.getConfiguratorTemplate().first();
        } else if (article.getMainDetail() instanceof Ext.data.Store && article.getMainDetail().first() instanceof Ext.data.Model) {
            template = Ext.create('Shopware.apps.Article.model.ConfiguratorTemplate');
            template.set('id', null);

            template = me.setMainDetailDataIntoTemplate(template);
            article.getConfiguratorTemplateStore = Ext.create('Ext.data.Store', {
                model: 'Shopware.apps.Article.model.ConfiguratorTemplate'
            });
            article.getConfiguratorTemplateStore.add(template);

        } else {
            template = Ext.create('Shopware.apps.Article.model.ConfiguratorTemplate');
            article.getConfiguratorTemplateStore = Ext.create('Ext.data.Store', {
                model: 'Shopware.apps.Article.model.ConfiguratorTemplate'
            });
            article.getConfiguratorTemplateStore.add(template);
        }

        me.getView('variant.configurator.Template').create({
            record: template,
            article: article,
            customerGroupStore: listing.customerGroupStore,
            unitStore: listing.unitStore
        }).show();
    },

    /**
     * Helper function to duplicate the main detail data into the passed object.
     * @param template
     * @return
     */
    setMainDetailDataIntoTemplate: function(template) {
        var me = this,
            article = me.subApplication.article;

        var mainDetail = article.getMainDetail().first();
        var prices = Ext.create('Ext.data.Store', { model: 'Shopware.apps.Article.model.Price' });
        template.set(mainDetail.data);

        var priceStore = article.getPrice();
        var lastFilter = priceStore.filters.items;

        priceStore.clearFilter();

        if (priceStore instanceof Ext.data.Store && priceStore.getCount() > 0) {
            priceStore.each(function(item) {
                var newPrice = Ext.create('Shopware.apps.Article.model.Price', item.data);
                newPrice.set('id', null);
                prices.add(newPrice);
            });
        }

        priceStore.filter(lastFilter);
        template.getPriceStore = prices;
        return template;
    },

    onSaveVariantInline: function(record) {
        var me = this;

        me.saveVariant(record,null, {
            callback: function() {
                me.getVariantListing().getSelectionModel().deselectAll();

                me.getVariantListing().getStore().load();

                if (record.get('standard') || record.get('kind') == 1) {
                    me.subApplication.getController('Detail').reloadArticle(record.get('articleId'));
                }
            }
        });
    },

    /**
     * Event will be fired when the user clicks the apply data button on the detail page.
     *
     */
    onApplyDataOnDetailPage: function(window, record) {
        var me = this,
            listing = me.getVariantListing();

        var mappingRecord = Ext.create('Shopware.apps.Article.model.Mapping', {
            articleId: me.subApplication.article.get('id')
        });
        var store = Ext.create('Ext.data.Store', { model: 'Shopware.apps.Article.model.Detail' });
        store.add(record);
        mappingRecord.getDetailsStore = store;

        if (listing.getStore().getCount() > 0)  {
            var mappingWindow = me.getView('variant.configurator.Mapping').create({
                record: mappingRecord,
                detailWindow: window
            });
            mappingWindow.show();
        }
    },

    /**
     * Event listener function of the group grid. Fired over the edit action column
     */
    onEditGroup: function(record) {
        var me = this;

        if (!record) {
            return false;
        }
        var window = me.getView('variant.configurator.GroupEdit').create({
            record: record
        });
        window.show();
    },

    /**
     * Event listener function of the option grid. Fired over the edit action column
     */
    onEditOption: function(record) {
        var me = this;

        if (!record) {
            return false;
        }
        var window = me.getView('variant.configurator.OptionEdit').create({
            record: record
        });
        window.show();
    },

    /**
     * Internal helper function to find all active groups.
     * @return
     */
    getActiveGroups: function() {
        var me = this,
            groups = [],
            groupGrid = me.getConfiguratorGroupListing();

        groupGrid.getStore().each(function(group) {
             if (group.get('active')) {
                 groups.push(group);
             }
        });
        return groups;
    },

    /**
     * Event listener function of the group edit window, fired over the save button.
     * @param group
     * @param form
     * @param window
     */
    onSaveGroup: function(group, form, window) {
        var me = this;

        if (!form.getForm().isValid()) {
            return;
        }
        form.getForm().updateRecord(group);
        var name = group.get('name');
        group.save({
            success: function(record, operation) {
                window.attributeForm.saveAttribute(record.get('id'));
                window.destroy();

                var message = Ext.String.format(me.snippets.success.groupSave, name);
                Shopware.Notification.createGrowlMessage(me.snippets.success.title, message, me.snippets.growlMessage);
                me.getConfiguratorGroupListing().reconfigure(me.getConfiguratorGroupListing().getStore());
            },
            failure: function(record, operation) {
                window.destroy();

                var rawData = record.getProxy().getReader().rawData,
                    message = rawData.message;

                if (Ext.isString(message) && message.length > 0) {
                    message = Ext.String.format(me.snippets.failure.groupSave, name) + '<br>' + message;
                } else {
                    message = Ext.String.format(me.snippets.failure.groupSave, name) + '<br>' + me.snippets.failure.noMoreInformation;
                }
                Shopware.Notification.createGrowlMessage(me.snippets.failure.title, message, me.snippets.growlMessage);
            }
        });
    },

    /**
     * Event listener function of the option edit window, fired over the save button.
     * @param group
     * @param form
     * @param window
     */
    onSaveOption: function(option, form, window) {
        var me = this;

        if (!form.getForm().isValid()) {
            return;
        }
        form.getForm().updateRecord(option);
        var name = option.get('name');
        option.save({
            success: function(record, operation) {
                window.attributeForm.saveAttribute(record.get('id'));
                window.destroy();
                var message = Ext.String.format(me.snippets.success.optionSave, name);
                Shopware.Notification.createGrowlMessage(me.snippets.success.title, message, me.snippets.growlMessage);
                me.getConfiguratorOptionListing().reconfigure(me.getConfiguratorOptionListing().getStore());
            },
            failure: function(record, operation) {
                window.destroy();
                var rawData = record.getProxy().getReader().rawData,
                    message = rawData.message;

                if (Ext.isString(message) && message.length > 0) {
                    message = Ext.String.format(me.snippets.failure.optionSave, name) + '<br>' + message;
                } else {
                    message = Ext.String.format(me.snippets.failure.optionSave, name) + '<br>' + me.snippets.failure.noMoreInformation;
                }
                Shopware.Notification.createGrowlMessage(me.snippets.failure.title, message, me.snippets.growlMessage);
            }
        });
    },

    /**
     * Event listener function of the article set window which fired over the cancel button.
     */
    onCancelEdit: function(window) {
        if (window) {
            window.destroy();
        }
    },

    /**
     * Event listener function of the article set window which fired over the save button.
     * @param configuratorSet
     * @param window
     */
    onSaveConfiguratorSet: function(configuratorSet, window) {
        var me = this,
            configuratorTypeCombo = me.getConfiguratorTypeCombo();

        configuratorSet.set('articleId', me.subApplication.article.get('id'));
        configuratorSet.set('type', configuratorTypeCombo.getValue());
        var store = Ext.create('Shopware.apps.Article.store.ConfiguratorSet');
        var name = configuratorSet.get('name');
        store.add(configuratorSet);
        store.sync({
            success: function(record, operation) {
                var message = Ext.String.format(me.snippets.success.setSave, name);
                if (window) {
                    window.destroy();
                }
                me.subApplication.article.set('configuratorSetId', configuratorSet.get('id'));
                Shopware.Notification.createGrowlMessage(me.snippets.success.title, message, me.snippets.growlMessage);
                me.selectAllActiveRows();
            },
            failure: function(record, operation) {
                var rawData = record.getProxy().getReader().rawData,
                    fields = rawData.fields,
                    message = rawData.message;

                if (Ext.isString(message) && message.length > 0) {
                    message = Ext.String.format(me.snippets.failure.setSave, name) + '<br>' +  message;
                } else {
                    message = Ext.String.format(me.snippets.failure.setSave, name) + '<br>' + me.snippets.failure.noMoreInformation;
                }
                Shopware.Notification.createGrowlMessage(me.snippets.failure.title, message, me.snippets.growlMessage);
                me.selectAllActiveRows();
            }
        });
    },

    /**
     * Event listener function which fired when the user clicks the load button in the set window.
     * @param newConfiguratorSet
     * @param window
     * @return
     */
    onLoadConfiguratorSet: function(newConfiguratorSet, window) {
        var me = this, detail, name,
            article = me.subApplication.article,
            listing = me.getVariantListing();

        if (window && !window.formPanel.getForm().isValid()) {
            return;
        }
        window.destroy();

        if (!article || !article.getMainDetail()) {
            Shopware.Notification.createGrowlMessage(me.snippets.failure.title, me.snippets.failure.articleNotFoundViolation, me.snippets.growlMessage);
            return false;
        }

        detail = article.getMainDetail().first();
        if (!detail) {
            Shopware.Notification.createGrowlMessage(me.snippets.failure.title, me.snippets.failure.articleNotFoundViolation, me.snippets.growlMessage);
            return false;
        }
        name = newConfiguratorSet.get('name');

        if (listing.getStore() && listing.getStore().getCount() > 0) {
            Ext.MessageBox.confirm(me.snippets.messages.warningTitle, me.snippets.messages.loadSetWarning, function (response) {
                if ( response === 'yes' ) {
                    Ext.Ajax.request({
                        url:'{url controller="Article" action="deleteAllVariants"}',
                        params:{
                            articleId: me.subApplication.article.get('id')
                        },
                        success: function(record, operation) {
                            me.continueConfiguratorSetLoad(newConfiguratorSet, detail);
                        },
                        failure: function(record, operation) {
                            var rawData = record.getProxy().getReader().rawData,
                                message = rawData.message;

                            if (Ext.isString(message) && message.length > 0) {
                                message = Ext.String.format(me.snippets.failure.setLoad, name) + '<br>' + message;
                            } else {
                                message = Ext.String.format(me.snippets.failure.setLoad, name) + '<br>' + me.snippets.failure.noMoreInformation;
                            }
                            Shopware.Notification.createGrowlMessage(me.snippets.failure.title, message, me.snippets.growlMessage);
                        }
                    });
                } else {
                    return false;
                }
            });
        } else {
            me.continueConfiguratorSetLoad(newConfiguratorSet, detail);
        }
    },

    /**
     * Helper function to continue the set loading.
     * @param newConfiguratorSet
     */
    continueConfiguratorSetLoad: function(newConfiguratorSet, detail) {
        var me = this, name,
            configurator = me.getConfigurator(),
            typeCombo = me.getConfiguratorTypeCombo(),
            groupListing = me.getConfiguratorGroupListing();

        //we have to clear the id property, because the original configurator set can't be changed.
        newConfiguratorSet.set('id', null);
        newConfiguratorSet.set('public', false);
        //we add the article detail order number to the original configurator set name.
        newConfiguratorSet.set('name', newConfiguratorSet.get('name') + '-' + detail.get('number'));
        newConfiguratorSet.set('articleId', me.subApplication.article.get('id'));
        typeCombo.setValue(newConfiguratorSet.get('type'));
        name = newConfiguratorSet.get('name');

        newConfiguratorSet.getConfiguratorGroups().each(function(item) {
            item.set('active', true);
        });
        newConfiguratorSet.getConfiguratorOptions().each(function(item) {
            item.set('active', true);
        });

        //to abort inconsistent data we save the set directly.
        var store = Ext.create('Shopware.apps.Article.store.ConfiguratorSet');
        store.add(newConfiguratorSet);
        store.sync({
            success: function(record, operation) {
                var id = record.operations[0].resultSet.records[0].data.id;
                newConfiguratorSet.set('id', id);
                var message = Ext.String.format(me.snippets.success.setLoad, newConfiguratorSet.get('name'));
                Shopware.Notification.createGrowlMessage(me.snippets.success.title, message, me.snippets.growlMessage);

                //after we saved the set, we set the set id into the article association id property.
                me.subApplication.article.set('configuratorSetId', newConfiguratorSet.get('id'));
                configurator.articleConfiguratorSet = newConfiguratorSet;
                me.loadConfiguratorSet(newConfiguratorSet);
            },
            failure: function(record, operation) {
                var rawData = record.getProxy().getReader().rawData,
                    message = rawData.message;

                if (Ext.isString(message) && message.length > 0) {
                    message = Ext.String.format(me.snippets.failure.setLoad, name) + '<br>' + message;
                } else {
                    message = Ext.String.format(me.snippets.failure.setLoad, name) + '<br>' + me.snippets.failure.noMoreInformation;
                }
                Shopware.Notification.createGrowlMessage(me.snippets.failure.title, message, me.snippets.growlMessage);
            }
        })
    },

    /**
     * Internal helper function to load the passed configurator set groups and options.
     * @param configuratorSet
     * @return
     */
    loadConfiguratorSet: function(configuratorSet) {
        var me = this, setGroups, groupActive, setOption, setOptions, setGroup,
            groupListing = me.getConfiguratorGroupListing();

        if (!configuratorSet || !configuratorSet.getConfiguratorGroups()) {
            return false;
        }

        //get all activated groups of the passed configurator set
        setGroups = configuratorSet.getConfiguratorGroups();
        setOptions = configuratorSet.getConfiguratorOptions();
        var selModel = groupListing.getSelectionModel();

        //iterate all groups in the group listing to active and deactive the groups
        groupListing.getStore().each(function(gridGroup) {
            //check if the current group activated in the passed configurator set
            setGroup = setGroups.getById(gridGroup.get('id'));

            //if this is the case, the group has to be activated.
            if (setGroup) {
                groupActive = true;
            } else {
                groupActive = false;
            }
            //we save the active flag in an internal property, because the group options can only be activated if the group is activated.
            gridGroup.set('active', groupActive);

            //get all options of the grid group
            var gridOptions = gridGroup.getConfiguratorOptions();
            if (!gridOptions) {
                return true;
            }

            //iterate all options of the grid group to activate or deactivate them.
            gridOptions.each(function(gridOption) {
                setOption = null;
                //if the grid option acivated in the configurator set, we can set the active flag to true.
                if (setOptions) {
                    setOption = setOptions.getById(gridOption.get('id'));
                }
                if (setOption && groupActive)  {
                    gridOption.set('active', true);
                } else {
                    gridOption.set('active', false);
                }
            });
        });
        me.getConfiguratorGroupListing().reconfigure(me.getConfiguratorGroupListing().getStore());
        me.deselectAllActiveRows();
        me.selectAllActiveRows();
        me.sortGroupGrid();
        me.sortOptionGrid();
    },

    /**
     * Prepares the configurator set for saving.
     * @return
     */
    prepareConfiguratorSet: function() {
        var me = this,
            configurator = me.getConfigurator(),
            article = me.subApplication.article,
            configuratorSet = configurator.articleConfiguratorSet,
            groupListing = me.getConfiguratorGroupListing();

        if (!configuratorSet) {
            configuratorSet = Ext.create('Shopware.apps.Article.model.ConfiguratorSet', {
                articleId: article.get('id')
            });
        }
        var setGroups = configuratorSet.getConfiguratorGroups();
        var setOptions = configuratorSet.getConfiguratorOptions();
        if (!setGroups) {
            setGroups = Ext.create('Ext.data.Store', { model: 'Shopware.apps.Article.model.ConfiguratorGroup' });
        }
        if (!setOptions) {
            setOptions = Ext.create('Ext.data.Store', { model: 'Shopware.apps.Article.model.ConfiguratorOption' });
        }
        setGroups.removeAll();
        setOptions.removeAll();

        groupListing.getStore().each(function(group) {
            setGroups.add(group);
            if (group.get('active')) {
                var selectedOptions = group.getConfiguratorOptions();
                if (!selectedOptions) {
                    return true;
                }
                selectedOptions.each(function(option) {
                    if (option.get('active')) {
                        setOptions.add(option);
                    }
                });
            }
        });
        configuratorSet.getConfiguratorGroupsStore = setGroups;
        configuratorSet.getConfiguratorOptionsStore = setOptions;
        configuratorSet.setDirty();
        return configuratorSet;
    },


    /**
     * Internal helper function to save the configurator set changes.
     */
    onDisplaySetSaveWindow: function() {
        var me = this;
        var configuratorSet = me.prepareConfiguratorSet();
        me.selectAllActiveRows();
        var setWindow = me.getView('variant.configurator.Sets').create({
            configuratorSet: configuratorSet,
            mode: 'save'
        });
        setWindow.show();
    },

    /**
     * Event listener function which fired when the user want to edit a configurator group.
     */
    onDisplaySetLoadWindow: function() {
        var me = this,
            configurator = me.getConfigurator();

        var setWindow = me.getView('variant.configurator.Sets').create({
            configuratorSet: configurator.articleConfiguratorSet,
            mode: 'load'
        });
        setWindow.show();
    },

    /*********************************************************************************************
     *************************EVENTS OF THE CONFIGURATOR COMPONENT********************************
     *********************************************************************************************/

    /**
     * Event listener function of the group panel. Fired when the user clicks on a group row.
     */
    onGroupClick: function(view, record) {
        var me = this;

        //if the group will be dragged, the select event will be fired, but the records are empty.
        if (!record) {
            return;
        }

        var optionStore, title = me.snippets.labels.title,
            configurator = me.getConfigurator(),
            optionListing = me.getConfiguratorOptionListing(),
            groupListing = me.getConfiguratorGroupListing();

        if (record && record.get('active')) {
            optionStore = record.getConfiguratorOptions();
            title = me.snippets.labels.titleLoaded + ' <b>' + record.get('name') + '</b>';
            if (!optionStore) {
                optionStore = Ext.create('Ext.data.Store', {
                    model: 'Shopware.apps.Article.model.ConfiguratorOption'
                });
            }
            optionListing.setDisabled(false);
            optionListing.activeGroup = record;
            optionListing.reconfigure(optionStore);
            optionStore.each(function(item) {
                if(item.get('active')) {
                    optionListing.getSelectionModel().select(item, true, true);
                }
            });
        } else {
            var store = Ext.create('Ext.data.Store', {
                fields: ['id']
            });
            optionListing.reconfigure(store);
            optionListing.setDisabled(true);
        }
        optionListing.setTitle(title);
    },

    /**
     * Event listener function of the group panel. Fired when the user select a group row.
     */
    onGroupSelect: function(record, configurator) {
        var me = this;

        if (record) {
            record.set('active', true);
            me.sortGroupGrid();
        }
        return true;
    },

    /**
     * Event listener function of the group panel. Fired when the user deselect a group row.
     */
    onGroupDeselect: function(record, configurator) {
        var me = this,
            groupListing = me.getConfiguratorGroupListing();

        record.set('active', false);
        groupListing.getSelectionModel().deselect(record, true);
        me.sortGroupGrid();
    },

    /**
     * Internal helper function to sort the configurator group grid.
     */
    sortGroupGrid: function() {
        var me = this,
            groupListing = me.getConfiguratorGroupListing();

        groupListing.getStore().sort([
            { property: 'active', 'direction': 'DESC' },
            { property: 'position', 'direction': 'ASC' }]
        );
    },

    /**
     * Event listener function of the configurator grid, fired when the user moves a configurator group
     * over the drag and drop function
     * @param Ext.data.Model source
     * @param Ext.data.Model target
     */
    onGroupDropped: function(source, target) {
        var me = this,
            groupListing = me.getConfiguratorGroupListing(),
            optionListing = me.getConfiguratorOptionListing();

        //creates an array with all active groups.
        var activeGroups = me.getActiveGroups();

        source.set('active', target.get('active'));
        if (source.get('active')) {
            groupListing.getSelectionModel().select(source, true, true);
        } else {
            groupListing.getSelectionModel().deselect(source, true);
        }
        var targetPosition = target.get('position');
        groupListing.getStore().each(function(item) {
            if (item.get('position') >= targetPosition) {
                item.set('position', item.get('position') + 1);
            }
        });
        source.set('position', targetPosition);

        me.sortGroupGrid();
        var store = Ext.create('Ext.data.Store', {
            fields: ['id']
        });
        optionListing.reconfigure(store);
        optionListing.setDisabled(true);
    },

    /**
     * Event listener function of the option panel. Fired when the user select a option row.
     */
    onOptionSelect: function(record, configurator) {
        var me = this;

        if (record) {
            record.set('active', true);
            me.sortOptionGrid();
            me.getConfiguratorGroupListing().reconfigure(me.getConfiguratorGroupListing().getStore());
        }
    },

    /**
     * Event listener function of the option panel. Fired when the user deselect a option row.
     */
    onOptionDeselect: function(record, configurator) {
        var me = this,
            optionListing = me.getConfiguratorOptionListing();

        record.set('active', false);
        optionListing.getSelectionModel().deselect(record, true);
        me.sortOptionGrid();
        me.getConfiguratorGroupListing().reconfigure(me.getConfiguratorGroupListing().getStore());
    },

    /**
     * Internal helper function to sort the configurator option grid.
     */
    sortOptionGrid: function() {
        var me = this,
            optionListing = me.getConfiguratorOptionListing();

        optionListing.getStore().groupField = 'active';
        optionListing.getStore().group('active', 'DESC');
        optionListing.getStore().sort([
            { property: 'active', 'direction': 'DESC' },
            { property: 'position', 'direction': 'ASC' }]
        );
    },

    /**
     * Event listener function of the configurator grid, fired when the user moves a configurator option
     * over the drag and drop function
     * @param Ext.data.Model source
     * @param Ext.data.Model target
     */
    onOptionDropped: function(source, target) {
        var me = this,
            groupListing = me.getConfiguratorGroupListing(),
            optionListing = me.getConfiguratorOptionListing();

        source.set('active', target.get('active'));
        var targetPosition = target.get('position');
        optionListing.getStore().each(function(item) {
            if (item.get('position') >= targetPosition) {
                item.set('position', item.get('position') + 1);
            }
        });
        source.set('position', targetPosition);
        if (source.get('active')) {
            optionListing.getSelectionModel().select(source, true, true);
        } else {
            optionListing.getSelectionModel().deselect(source, true);
        }
        me.sortOptionGrid();
        groupListing.reconfigure(groupListing.getStore());
    },

   /**
    * Event will be fired over the save button if the user is on the configurator tab.
    * Fired from the detail.window component
    */
    onCreateVariants: function(article) {
        var me = this;

        Ext.MessageBox.confirm(me.snippets.messages.warningTitle, me.snippets.messages.saveArticleBefore, function(btn) {
            if(btn == 'yes') {
                var detailController = me.getController('Detail');
                detailController.onSaveArticle(null, me.subApplication.article, {
                    callback: function(article, success, failure) {
                        if (success) {
                            me.openProgressWindow(article);
                        } else {
                            if (failure === 'no_valid_form') {
                                Shopware.Notification.createGrowlMessage(me.snippets.failure.title, me.snippets.messages.noValidForm, me.snippets.growlMessage);
                            } else {
                                Shopware.Notification.createGrowlMessage(me.snippets.failure.title, me.snippets.messages.noValidForm, me.snippets.growlMessage);
                            }
                        }
                    }
                });
            } else {
                me.openProgressWindow(article);
            }
        });
    },

    openProgressWindow: function(article) {
        var me = this, totalCount = 1, count, groupsChanged = false,
            configurator = me.getConfigurator();

        var oldGroups = Ext.create('Ext.data.Store', {
            model:'Shopware.apps.Article.model.ConfiguratorGroup'
        });

        if (article.getConfiguratorSet() instanceof Ext.data.Store &&
            article.getConfiguratorSet().first() instanceof Ext.data.Model) {

            oldGroups = article.getConfiguratorSet().first().getConfiguratorGroups();
        }

        //first we create a new store for the activated groups
        var activeGroups = Ext.create('Ext.data.Store', {
            model:'Shopware.apps.Article.model.ConfiguratorGroup'
        });

        //now we create a new model as data container
        var model = Ext.create('Shopware.apps.Article.model.Configurator', {
            articleId:article.get('id')
        });

        //then we have to iterate the configurator groups to filter all active groups
        configurator.configuratorGroupStore.each(function (group) {
            if ( group.get('active') ) {
                activeGroups.add(group);

                if (!(oldGroups.getById(group.get('id')))) {
                    groupsChanged = true;
                }

                count = 0;
                group.getConfiguratorOptions().each(function (option) {
                    if ( option.get('active') ) {
                        count++;
                    }
                });
                if ( count > 0 ) {
                    totalCount = totalCount * count;
                }
            }
        });

        if (oldGroups.getCount() !== activeGroups.getCount()) {
            groupsChanged = true;
        }

        //at least we set the active groups in the model association store and save the model.
        model.getConfiguratorGroups();
        model.set('totalCount', totalCount);
        model['getConfiguratorGroupsStore'] = activeGroups;

        var store = me.getVariantListing().getStore();
        if (store.getCount() == 0) {
            // always override the variants when no variants exist
            groupsChanged = true;
        }

        var progress = me.getView('variant.Progress').create({
            configurator: model,
            article: article,
            groupsChanged: groupsChanged
        }).show();
    },

    /**
     * @param article
     * @param window
     */
    onVariantsGenerated: function(article, window) {
        var me = this;

        var configuratorTabPanel = me.getConfiguratorTabPanel();
        configuratorTabPanel.setActiveTab(1);

        var configurator = me.getConfigurator();
        window.destroy();
        me.subApplication.getController('Detail').reloadArticle(article.get('id'));
        configuratorTabPanel.setActiveTab(0);

        var variantListing = me.getVariantListing();
        variantListing.configuratorGroupStore = configurator.configuratorGroupStore;
        variantListing.refreshColumns();
        variantListing.getSelectionModel().deselectAll();
        variantListing.getStore().load();
        var configuratorSet = me.prepareConfiguratorSet();
        me.onSaveConfiguratorSet(configuratorSet, null);

        // create the image relation process window
        Ext.create('Shopware.apps.Article.view.variant.ImageRelationProcess', {
            article: article
        }).show();
    },

    /**
     * Event will be fired when the user clicks on the "create" button in the
     * group panel. If the user choose "create & activate" the "activate" parameter
     * will be set to true, otherwise the parameter is set to false.
     * @event
     * @param name - The value of the text field in the toolbar
     * @param activate - If the user clicks the button "create & activate" the parameter will be set to true
     */
    onCreateGroup: function(name, activate) {
        var me = this, position = 1,
            groupListing = me.getConfiguratorGroupListing();

        name = Ext.String.trim(name) + '';
        if (name.length === 0) {
            return;
        }

        //creates an array with all active groups.
        var activeGroups = me.getActiveGroups();

        groupListing.getStore().each(function(item) {
            //check the new position for the created group.
            if (position <= item.get('position')) {
                position = item.get('position') + 1;
            }
        });

        var record = Ext.create('Shopware.apps.Article.model.ConfiguratorGroup', {
            name: name,
            active: activate,
            position: position
        });

        groupListing.getStore().add(record);
        if (activate) {
            groupListing.getSelectionModel().select(record, true, true);
        }

        var store = Ext.create('Shopware.apps.Article.store.Group');
        store.add(record);
        store.save({
            callback: function() {
                me.sortGroupGrid();
            }
        });
    },

    /**
     * Event will be fired when the user clicks the "create" button which
     * displayed in the top toolbar of the option panel.
     * @event
     * @param name - The value of the text field
     */
    onCreateOption: function(name, activate) {
        var me = this, position = 1,
            optionListing = me.getConfiguratorOptionListing(),
            groupListing = me.getConfiguratorGroupListing();

        name = Ext.String.trim(name) + '';
        if (name.length === 0) {
            return;
        }

        optionListing.getStore().each(function(item) {
            //check the new position for the created group.
            if (position <= item.get('position')) {
                position = item.get('position') + 1;
            }
        });

        // SW-4440 There cannot be two options with the same name
        if(optionListing.getStore().findRecord('name', name, 0, false, false, true )) {
            Shopware.Notification.createGrowlMessage(me.snippets.failure.title, Ext.String.format(me.snippets.messages.optionExists, name), me.snippets.growlMessage);
            return;
        }


        var record = Ext.create('Shopware.apps.Article.model.ConfiguratorOption', {
            name: name,
            active: activate,
            groupId: optionListing.activeGroup.get('id'),
            position: position
        });
        optionListing.getStore().add(record);

        if (activate) {
            optionListing.getSelectionModel().select(record, true, true);
        }
        var store = Ext.create('Shopware.apps.Article.store.Option');
        store.add(record);
        store.save({
            success: function(record, operation) {
                var message = Ext.String.format(me.snippets.success.optionSave, name);
                Shopware.Notification.createGrowlMessage(me.snippets.success.title, message, me.snippets.growlMessage);
                groupListing.reconfigure(groupListing.getStore());
                me.sortOptionGrid();
            },
            failure: function(record, operation) {
                var rawData = record.getProxy().getReader().rawData,
                    message = rawData.message + '';

                if (Ext.isString(message) && message.length > 0) {
                    message = Ext.String.Format(me.snippets.failure.optionSave, name) + '<br>' + message;
                } else {
                    message = Ext.String.Format(me.snippets.failure.optionSave, name) + '<br>' + me.snippets.failure.noMoreInformation;
                }
                Shopware.Notification.createGrowlMessage(me.snippets.failure.title, message, me.snippets.growlMessage);
            }
        });
    },

    /**
    * Event will be fired when the user clicks the delete column in the group grid.
    * @event
    * @param record - The selected grid record
    */
    onDeleteGroup: function(record) {
       var me = this, articleString, message,
           groupListing = me.getConfiguratorGroupListing(),
           optionListing = me.getConfiguratorOptionListing();

        if (!(record instanceof Ext.data.Model)) {
            return;
        }
        var name = record.get('name');
        message = Ext.String.format(me.snippets.messages.groupRemove, name);

        // we do not just delete - we are polite and ask the user if he is sure.
        Ext.MessageBox.confirm(me.snippets.growlMessage, message, function (response) {
            if ( response !== 'yes' ) {
                return;
            }
            if ( record.get('id') > 0 ) {
                record.destroy({
                    success:function (record, operation) {
                        message = Ext.String.format(me.snippets.success.groupRemove, name);
                        Shopware.Notification.createGrowlMessage(me.snippets.success.title, message, me.snippets.growlMessage);
                        groupListing.getStore().remove(record);
                        var store = Ext.create('Ext.data.Store', {
                            fields:['id']
                        });
                        optionListing.reconfigure(store);
                        optionListing.setDisabled(true);
                    },
                    failure:function (record, operation) {
                        var rawData = record.getProxy().getReader().rawData,
                            articles = rawData.articles;

                        message = rawData.message + '';
                        if ( articles.length > 0 ) {
                            if ( articles.length > 10 ) {
                                articles = articles.slice(0, 10);
                                articleString = articles.join('<br>') + '<br>...';
                            } else {
                                articleString = articles.join('<br>');
                            }
                            message = Ext.String.format(me.snippets.failure.groupBounded, name);
                            Shopware.Notification.createGrowlMessage(me.snippets.failure.title, message + '<br>' + articleString, me.snippets.growlMessage);
                        } else {
                            if (Ext.isString(message) && message.length > 0) {
                                message = me.snippets.failure.groupRemove + '<br>' + message;
                            } else {
                                message = me.snippets.failure.groupRemove + '<br>' + me.snippets.failure.noMoreInformation;
                            }
                            Shopware.Notification.createGrowlMessage(me.snippets.failure.title, message, me.snippets.growlMessage);
                        }
                    }
                });
            }
        });
   },

    /**
    * Event will be fired when the user clicks the delete column in the option grid.
    * @event
    * @param record - The selected grid record
    */
    onDeleteOption: function(record) {
       var me = this, articleString, message, name,
           groupListing = me.getConfiguratorGroupListing(),
           optionListing = me.getConfiguratorOptionListing();

        if ( !(record instanceof Ext.data.Model) ) {
            return;
        }
        name = record.get('name');
        message = Ext.String.format(me.snippets.messages.optionRemove, name);

        // we do not just delete - we are polite and ask the user if he is sure.
        Ext.MessageBox.confirm(me.snippets.growlMessage, message, function (response) {
            if ( response !== 'yes' ) {
                return;
            }
            if ( record.get('id') > 0 ) {
                record.destroy({
                    success:function (record, operation) {
                        message = Ext.String.format(me.snippets.success.optionRemove, name);
                        Shopware.Notification.createGrowlMessage(me.snippets.success.title, message, me.snippets.growlMessage);

                        optionListing.getStore().remove(record);
                        groupListing.reconfigure(groupListing.getStore());
                    },
                    failure:function (record, operation) {
                        var rawData = record.getProxy().getReader().rawData,
                                articles = rawData.articles;

                        message = rawData.message + '';
                        if ( articles.length > 0 ) {
                            if ( articles.length > 10 ) {
                                articles = articles.slice(0, 10);
                                articleString = articles.join('<br>') + '<br>...';
                            } else {
                                articleString = articles.join('<br>');
                            }
                            message = Ext.String.format(me.snippets.failure.optionBounded, name);
                            Shopware.Notification.createGrowlMessage(me.snippets.failure.title, message + '<br>' + articleString, me.snippets.growlMessage);
                        } else {
                            if ( Ext.isString(message) && message.length > 0 ) {
                                message = me.snippets.failure.optionRemove + '<br>' + message;
                            } else {
                                message = me.snippets.failure.optionRemove + '<br>' + me.snippets.failure.noMoreInformation;
                            }
                            Shopware.Notification.createGrowlMessage(me.snippets.failure.title, message, me.snippets.growlMessage);
                        }
                    }
                });
            }
        });
    },

    /**
     * Event listener function of the main tab panel in the detail window.
     * Fired when the user want to change the tab.
     */
    onMainTabChange: function(panel, newTab, oldTab) {
        var me = this,
            variantListing = me.getVariantListing(),
            configuratorTabPanel = me.getConfiguratorTabPanel();



        //if the user want to change to the variant tab, load the store of the variant listing.
        if (newTab.name === 'variant-tab') {
            if (variantListing.getStore().getCount() > 0) {
                configuratorTabPanel.setActiveTab(0);
            } else {
                variantListing.getSelectionModel().deselectAll();
                variantListing.getStore().load({
                    callback: function() {
                        if (variantListing.getStore().getCount()===0) {
                            configuratorTabPanel.setActiveTab(1);
                            me.selectAllActiveRows();
                        } else {
                            configuratorTabPanel.setActiveTab(0);
                        }
                    }
                });
            }
            me.switchSaveButton(true, false);
        } else {
            me.switchSaveButton(false, true);
        }
    },

    /**
     * Helper function to hide the article or configurator button.
     * @param hideArticleButton
     * @param hideConfiguratorButton
     */
    switchSaveButton: function(hideArticleButton, hideConfiguratorButton) {
        var me = this,
            articleButton = me.getSaveArticleButton(),
            configuratorButton = me.getSaveConfiguratorButton();

        if(articleButton){
            if (hideArticleButton) {
                articleButton.hide();
            } else {
                articleButton.show();
            }
        }

        if (hideConfiguratorButton) {
            configuratorButton.hide();
        } else {
            configuratorButton.show();
        }
    },

    /**
     * Event listener function of the configurator tab panel. Fired when the user changes the tab.
     */
    onConfiguratorTabChanged: function(panel, newTab) {
        var me = this;
        if (newTab.name === 'configurator') {
            me.selectAllActiveRows();
        }
    },

    deselectAllActiveRows: function() {
        var me = this,
            grids = [
                me.getConfiguratorGroupListing(),
                me.getConfiguratorOptionListing()
            ];

        Ext.each(grids, function(grid) {
            var selModel = grid.getSelectionModel();
            selModel.deselectAll(true);
        });
    },

    /**
     * Selects all active groups in the group grid.
     */
    selectAllActiveRows: function() {
        var me = this,
            grids = [
                me.getConfiguratorGroupListing(),
                me.getConfiguratorOptionListing()
            ];

        Ext.each(grids, function(grid) {
            var selModel = grid.getSelectionModel();
            grid.getStore().each(function(item) {
                if (item.get('active')) {
                    selModel.deselect(item, true);
                    selModel.select(item, true, true);
                }
            });
        });
    },


    /*********************************************************************************************
     *************************EVENTS OF THE VARIANT DETAIL PAGE***********************************
     *********************************************************************************************/

    onSaveTemplate: function(win, form, template) {
        var me = this, priceStore, number;

        if (!form.getForm().isValid()) {
            return;
        }

        if (form && template) {
            form.getForm().updateRecord(template);
            var articleController = me.getController('Detail');
            articleController.onSaveArticle(
                win.mainWindow,
                win.article,
                {
                    callback: function(newArticle, success) {
                        if (success)  {
                            win.attributeForm.saveAttribute(newArticle.getConfiguratorTemplate().first().get('id'));
                        }
                    }
            });
            win.destroy();
        }
    },

    /**
     * Event listener function which fired when the user clicks the save button in the article variant detail window.
     * @param win
     * @param form
     * @param variant
     */
    onSaveVariant: function(win, form,variant) {
        var me = this, priceStore, number;

        if (!form.getForm().isValid()) {
            return;
        }
        if (form && variant) {
            form.getForm().updateRecord(variant);
            me.saveVariant(variant,win);
        }
    },

    saveVariant: function(variant, win, options) {
        var me = this;

        if (!variant) {
            return;
        }
        var priceStore = variant.getPrice();
        me.removeClonedPrices(priceStore);
        var number = variant.get('number');

        variant.save({
            success: function(record, operation) {
                var message = Ext.String.format(me.snippets.success.variantSave, number);
                Shopware.Notification.createGrowlMessage(me.snippets.success.title, message, me.snippets.growlMessage);
                if (win) {
                    win.attributeForm.saveAttribute(record.get('id'));
                    win.destroy();
                }
                if (options !== Ext.undefined && Ext.isFunction(options.callback)) {
                    options.callback(record);
                }

                if (record.get('standard') || record.get('kind') == 1) {
                    me.subApplication.getController('Detail').reloadArticle(record.get('articleId'));
                }
            },
            failure: function(record, operation) {
                var rawData = record.getProxy().getReader().rawData,
                    fields = rawData.fields,
                    message = rawData.message + '';

                if (fields && fields.length > 0) {
                    Shopware.Notification.createGrowlMessage(me.snippets.failure.errorTitle, me.snippets.failure.fieldsViolation + '<br>' + fields.join('<br>'), me.snippets.growlMessage);
                } else {
                    if (Ext.isString(message) && message.length > 0) {
                        message = Ext.String.format(me.snippets.failure.variantSave, number) + '<br>' + message;
                    } else {
                        message = Ext.String.format(me.snippets.failure.variantSave, number) + '<br>' + me.snippets.failure.noMoreInformation;
                    }
                    Shopware.Notification.createGrowlMessage(me.snippets.failure.errorTitle, message, me.snippets.growlMessage);
                }
            }
        });
    },

    /**
     * Removes the cloned prices of the article price store.
     * @param article
     */
    removeClonedPrices: function(priceStore) {
        var me = this,
            toRemove = [];

        priceStore.clearFilter();
        priceStore.each(function(price) {
            if (price instanceof Ext.data.Model && price.get('cloned')) {
                toRemove.push(price);
            }
        });

        priceStore.remove(toRemove);
    },

    /**
     * Event listener function which fired when the user edits a variant price over the listing row editor.
     */
    onEditVariantPrice: function(record, price) {
        var me = this;

        if (!record) {
            return false;
        }

        if (!record.getPrice()) {
            record.getPriceStore = Ext.create('Ext.data.Store', { model: 'Shopware.apps.Article.model.Price' })
        }

        var newPrice = Ext.create('Shopware.apps.Article.model.Price', {
            pseudoPrice: 0,
            percent: 0,
            customerGroupKey: me.subApplication.firstCustomerGroup.get('key')
        });

        if (record.getPrice().getCount() > 0) {
            newPrice = record.getPrice().first();
        }
        newPrice.set('price', price);
        newPrice.set('to', 'beliebig');
        newPrice.set('from', 1);
        newPrice.set('cloned', false);

        record.getPrice().removeAll();
        record.getPrice().add(newPrice);
        record.save();
    },

    /**
     * Event listener function which fired when the user edits a variant pseudoprice over the listing row editor.
     */
    onEditVariantPseudoPrice: function(record, pseudoPrice) {
        var me = this;

        if (!record) {
            return false;
        }

        if (!record.getPrice()) {
            record.getPriceStore = Ext.create('Ext.data.Store', { model: 'Shopware.apps.Article.model.Price' });
        }

        var newPrice = Ext.create('Shopware.apps.Article.model.Price', {
            percent: 0,
            customerGroupKey: me.subApplication.firstCustomerGroup.get('key')
        });

        if (record.getPrice().getCount() > 0) {
            newPrice = record.getPrice().first();
        }
        newPrice.set('pseudoPrice', pseudoPrice);
        newPrice.set('to', 'beliebig');
        newPrice.set('from', 1);
        newPrice.set('cloned', false);
        newPrice.commit();

        record.getPrice().removeAll();
        record.getPrice().add(newPrice);
        record.set('pseudoPrice', pseudoPrice);
        record.save();
    },



    /*********************************************************************************************
     *******************EVENTS OF THE DEPENDENCY AND PRICE VARIATION COMPONENT********************
     *********************************************************************************************/

    /**
     * Event will be fired when the user changes the group selection in the
     * left combo box.
     * @event
     * @param row Ext.form.Panel
     * @param value
     */
    onLeftGroupChanged: function(row, value, groupStore) {
        var me = this, group, leftOptionCombo;

        if (value && groupStore) {
            group = groupStore.getById(value);
            leftOptionCombo = row.down('combobox[name=parentId]');
            if (group && leftOptionCombo) {
                leftOptionCombo.setValue(null);
                leftOptionCombo.bindStore(group.getConfiguratorOptions());
            }
        }
    },

    /**
     * Event will be fired when the user changes the group selection in the
     * right combo box.
     * @event
     */
    onRightGroupChanged: function(row, value, groupStore) {
        var me = this, group, rightOptionCombo;

        if (value && groupStore) {
            group = groupStore.getById(value);
            rightOptionCombo = row.down('combobox[name=childId]');
            if (group && rightOptionCombo) {
                rightOptionCombo.setValue(null);
                rightOptionCombo.bindStore(group.getConfiguratorOptions());
            }
        }
    },

    /**
     * Event will be fired when the user clicks the save button.
     * @event
     */
    onSaveDependency: function(row, windowStore) {
        var me = this,
            record = row.record,
            deleteButton, createNewRow = true,
            variantListing = me.getVariantListing(),
            newRow, fieldSet = me.getDependencyFieldSet(),
            dependencyWindow = me.getDependencyWindow();


        if (!record) {
            record = Ext.create('Shopware.apps.Article.model.Dependency');
        }
        if (!row.getForm().isValid()) {
            return;
        }
        if (record.get('id') > 0) {
            createNewRow = false;
        } else {
            windowStore.add(record);
        }

        row.getForm().updateRecord(record);
        record.set('configuratorSetId', me.subApplication.article.get('configuratorSetId'));
        var store = Ext.create('Shopware.apps.Article.store.Dependency');
        store.add(record);
        store.sync({
            success: function(savedRecord, operation) {
                var id = savedRecord.operations[0].resultSet.records[0].data.id;
                record.set('id', id);
                row.record = record;

                Shopware.Notification.createGrowlMessage(me.snippets.success.title, me.snippets.success.dependencySave, me.snippets.growlMessage);
                deleteButton = row.down('button[name=delete-button]');
                if (deleteButton) {
                    deleteButton.setDisabled(false);
                }
                if (dependencyWindow && createNewRow) {
                    newRow = dependencyWindow.createContainerRow(null, dependencyWindow.snippets.dependency);
                    if (newRow && fieldSet) {
                        fieldSet.add(newRow);
                    }
                }
            },
            failure: function(savedRecord, operation) {
                var rawData = savedRecord.getProxy().getReader().rawData,
                    message = rawData.message;

                if (Ext.isString(message) && message.length > 0) {
                    message = me.snippets.failure.dependencySave + '<br>' + message;
                } else {
                    message = me.snippets.failure.dependencySave + '<br>' + me.snippets.failure.noMoreInformation;
                }
                Shopware.Notification.createGrowlMessage(me.snippets.failure.title, message, me.snippets.growlMessage);
            }
        });
    },

    /**
     * Event will be fired when the user clicks the delete button.
     * @event
     */
    onRemoveDependency: function(row, store) {
        var me = this,
            fieldSet = me.getDependencyFieldSet();

        store.remove(row);

        if (fieldSet && row && row.record) {
            // we do not just delete - we are polite and ask the user if he is sure.
            Ext.MessageBox.confirm(me.snippets.growlMessage, me.snippets.messages.dependencyRemove, function (response) {
                if ( response !== 'yes' ) {
                    return;
                }
                row.record.destroy({
                    success: function(record, operation) {
                        Shopware.Notification.createGrowlMessage(me.snippets.success.title, me.snippets.success.dependencyRemoved, me.snippets.growlMessage);
                        fieldSet.remove(row);
                    },
                    failure: function(record, operation) {
                        var rawData = record.getProxy().getReader().rawData,
                            message = rawData.message;

                        if (Ext.isString(message) && message.length > 0) {
                            message = me.snippets.failure.dependencyRemove + '<br>' + message;
                        } else {
                            message = me.snippets.failure.dependencyRemove + '<br>' + me.snippets.failure.noMoreInformation;
                        }
                        Shopware.Notification.createGrowlMessage(me.snippets.failure.title, message, me.snippets.growlMessage);
                    }
                });
            });
        }
    },

    /**
     * Event listener function which fired when the user want define configurator dependencies.
     * The event will be fired over the toolbar button in the configurator tab.
     */
    onDefineDependency: function(store) {
        var me = this,
            groupListing = me.getConfiguratorGroupListing();

        me.getView('variant.configurator.Dependency').create({
            configuratorGroupStore: groupListing.getStore(),
            store: store
        });
    },

    /*********************************************************************************************
     *****************************EVENTS OF VARIANT LISTING COMPONENT*****************************
     *********************************************************************************************/

    /**
     * Event listener function of the article variant list. Fired when the
     * user clicks on the pencil action column to edit the selected variant
     * over the detail page.
     *
     * @param [Ext.data.Model] The selected record
     */
    onEditVariant: function(record) {
        var me = this,
            listing = me.getVariantListing();

        me.getView('variant.Detail').create({
            record: record,
            article: me.subApplication.article,
            customerGroupStore: listing.customerGroupStore,
            unitStore: listing.unitStore
        }).show();
    },

    /**
     * Event listener function which fired when the user selects article variants in the variant listing
     * and clicks the delete button in the toolbar.
     * @param records
     */
    onDeleteVariant: function(records) {
        var me = this, message, number,
            variantListing = me.getVariantListing(),
            store = variantListing.getStore();

        if (records.length > 0) {
            if (records.length > 1) {
                message = me.snippets.messages.variantsRemove;
            } else {
                number = records[0].get('number');
                message = Ext.String.format(me.snippets.messages.variantRemove, number);
            }

            // we do not just delete - we are polite and ask the user if he is sure.
            Ext.MessageBox.confirm(me.snippets.growlMessage, message, function (response) {
                if ( response !== 'yes' ) {
                    return;
                }
                variantListing.getSelectionModel().deselectAll();
                store.remove(records);
                store.sync({
                    success: function(record, operation) {
                        if (name.length > 0) {
                            message = Ext.String.format(me.snippets.success.variantRemove, number);
                        } else {
                            message = me.snippets.success.variantsRemove;
                        }
                        Shopware.Notification.createGrowlMessage(me.snippets.success.title, message, me.snippets.growlMessage);
                        store.currentPage = 1;
                        store.load();
                        me.getController('Detail').reconfigureAssociationComponents(null);
                    },
                    failure: function(record, operation) {
                        var rawData = record.getProxy().getReader().rawData,
                            message = rawData.message;

                        if (Ext.isString(message) && message.length > 0) {
                            message = me.snippets.failure.variantsRemove + '<br>' + message;
                        } else {
                            message = me.snippets.failure.variantsRemove + '<br>' + me.snippets.failure.noMoreInformation;
                        }
                        Shopware.Notification.createGrowlMessage(me.snippets.failure.title, message, me.snippets.growlMessage);
                    }
                });
            });
        }
    },

    /**
     * Event listener function which fired when the user insert a search value into the search field of the variant listing
     * toolbar.
     * Filters the store with the passed value.
     */
    onSearchVariants: function(value) {
        var me = this,
            variantListing = me.getVariantListing(),
            store = variantListing.getStore();

        variantListing.getSelectionModel().deselectAll();
        value = Ext.String.trim(value);
        store.filters.clear();
        store.currentPage = 1;
        if (value.length > 0) {
            store.filter({ property: 'free', value: value });
        } else {
            store.load();
        }
    },

    /**
     * Event listener function which fired when the user clicks the "generate order numbers" button
     * in the variant listing.
     */
    onGenerateOrderNumbers: function(syntaxField) {
        var me = this, window, variantListing = me.getVariantListing(),
            syntax = syntaxField.getValue();

        if (syntax.length === 0) {
            syntaxField.markInvalid();
            return
        }

        window = me.getView('variant.NumberProgress').create({
            totalCount: variantListing.getStore().getTotalCount(),
            syntax: syntax
        }).show();
    },

    /**
     * Called after the user hits the 'start' button of the multiRequestDialog
     */
    onStartNumberProcess: function(window) {
        var me = this,
            totalCount = window.totalCount || 0;

        window.combo.disable();

        me.generateNumbers(0, window, totalCount, window.combo.getValue(), window.progressBar);
    },

    onCancelNumberProcess: function(window) {
        this.cancelOperation = true;
        window.closeButton.enable();
        window.startButton.disable();
        window.cancelButton.disable();
    },

    /**
     * Recursive function which is used to generate the order numbers over a progress window.
     *
     * @param offset
     * @param window
     * @param totalCount
     * @param batchSize
     * @param progressbar
     */
    generateNumbers: function(offset, window, totalCount, batchSize, progressbar) {
        var me = this;

        //last batch size processed?
        if (offset >= totalCount) {

            //is progress bar configured?
            if (progressbar) {
                progressbar.updateProgress(1, me.snippets.generateNumbersDone, true);
            }

            window.cancelButton.disable();
            window.closeButton.enable();
            me.getVariantListing().getStore().load();
            window.destroy();
            return;
        }

        //cancel button pushed?
        if (me.cancelOperation) {
            window.closeButton.enable();
            return;
        }

        //has the current request a progress bar?
        if (progressbar) {
            // updates the progress bar value and text, the last parameter is the animation flag
            progressbar.updateProgress(
                (offset + batchSize) / totalCount,
                Ext.String.format(me.snippets.generateNumberProcess, ( offset + batchSize), totalCount),
                true
            );
        }

        Ext.Ajax.request({
            url: '{url controller="Article" action="regenerateVariantOrderNumbers"}',
            method: 'POST',
            params: {
                articleId: me.subApplication.article.get('id'),
                offset: offset,
                limit: batchSize,
                syntax: window.syntax
            },
            timeout: 4000000,
            success: function(response) {
                var json = Ext.decode(response.responseText);

                // start recusive call here
                me.generateNumbers((offset + batchSize), window, totalCount, batchSize, progressbar);
            },
            failure: function(response) {
                me.cancelOperation = true;
                me.generateNumbers((offset + batchSize), window, totalCount, batchSize, progressbar);
            }
        });
    },


    /**
     * Displays the mapping window to apply data from the
     */
    onDisplayMappingWindow: function() {
        var me = this,
            listing = me.getVariantListing(),
            sm = listing.getSelectionModel();

        var mappingRecord = Ext.create('Shopware.apps.Article.model.Mapping', {
            articleId: me.subApplication.article.get('id')
        });
        var store = Ext.create('Ext.data.Store', { model: 'Shopware.apps.Article.model.Detail' });
        store.add(sm.getSelection());
        mappingRecord.getDetailsStore = store;

        if (listing.getStore().getCount() > 0)  {
            var mappingWindow = me.getView('variant.configurator.Mapping').create({
                record: mappingRecord
            });
            mappingWindow.show();
        }
    },

    /**
     * Event listener function of the mapping window. Fired when the user select the different resources
     * which has to be apply from the main article to the selected/all variants.
     * @param window
     * @param variants
     */
    onAcceptBaseData: function(window, variants) {
        var me = this, ids = [],
            listing = me.getVariantListing(),
            form = window.formPanel;

        var record = form.getRecord();
        form.getForm().updateRecord(record);
        window.destroy();
        if (record.get('prices') === false &&
            record.get('basePrice') === false &&
            record.get('purchasePrice') === false &&
            record.get('attributes') === false &&
            record.get('settings') === false &&
            record.get('translations') === false) {
            return;
        }

        listing.setLoading(true);
        record.setDirty();
        record.save({
            success: function(record, operation) {
                listing.getStore().load();
                listing.setLoading(false);
            },
            failure: function(record, operation) {
                var rawData = record.getProxy().getReader().rawData,
                    message = rawData.message;

                listing.getStore().load();
                listing.setLoading(false);
            }
        });
    }

});
//{/block}
