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
 * @package    SwagMigration
 * @subpackage Controller
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Daniel Nögel
 * @author     $Author$
 */

/**
 *
 */
//{namespace name=backend/swag_migration/main}
//{block name="backend/swag_migration/controller/wizard"}
Ext.define('Shopware.apps.SwagMigration.controller.Wizard', {

    /**
     * The parent class that this class extends.
     * @string
     */
    extend:'Ext.app.Controller',

    /**
     * Set component references for easy access
     * @array
     */
    refs: [
        { ref: 'wizardPanel', selector: 'migration-wizard' }
    ],

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
            'migration-wizard': {
                'navigate': me.onNavigate
                },
            'migration-form-mapping': {
                'beforequery': me.onQueryMappingValues,
                'validate': me.onValidateCurrentCard
                },
            'migration-form-import': {
                'validate': me.onValidateCurrentCard
            }
        });

        me.callParent(arguments);
    },


    /**
     * Fired when the user expands a combo editor in one of the mapping grids
     * Will load available mapping values from the backend controller
     * @param e
     * @param migrationCard
     */
    onQueryMappingValues: function(e, migrationCard) {
        var me = this,
            panel = migrationCard.up("panel"),
            layout = panel.getLayout(),
            items = layout.getLayoutItems(),
            databaseCard = items[0],
            databaseValues = databaseCard.getForm().getValues();

        databaseValues.mapping = e.combo.ownerCt.editingPlugin.context.record.data.group;
        e.combo.store.getProxy().extraParams = databaseValues;
        e.combo.store.load();
    },

    /**
     * Called when the user clicked on of the navigation buttons in the wizard's bottom toolbar
     * @param panel
     * @param direction
     */
    onNavigate: function(panel, direction) {
        var me = this,
            layout = panel.getLayout(),
            activeCard = layout.getActiveItem();
        
        switch(direction) {
            case 'next':
                if (!activeCard.getForm().isValid()) {
                    return;
                }

                switch (activeCard.internalId) {
                    case 0:
                        activeCard.getForm().submit({
                            url: '{url action="checkForm"}',
                            success: function(fp, o) {
                                layout.next();
                                me.checkLayoutButtons(panel);
                                me.loadMappingStores(panel);
                            },
                            failure: function (fp, o) {
                                Ext.Msg.alert('{s name=error}Error{/s}', o.result.message);
                            }
                        });
                        break;
                    case 1:
                        layout.next();
                        me.checkLayoutButtons(panel);
                        break;
                    case 2:
                        me.startImport();
                        break;
                }
                break;
            case 'prev':
                layout.prev();
                break;
        }

        me.checkLayoutButtons(panel);

    },

    /**
     * Triggers the import
     */
    startImport: function() {
        var me = this,
            panel = me.getWizardPanel(),
            layout = panel.getLayout(),
            items = layout.getLayoutItems(),
            databaseCard = items[0],
            databaseValues = databaseCard.getForm().getValues(),
            mappingCard = items[1],
            mappingStore = mappingCard.getGridStore(),
            importCard = items[2],
            importCardValues = importCard.getForm().getValues(),
            config = databaseValues,
            total = 0;

        Ext.iterate(importCardValues, function(key, value) {
            config[key] = value;
            if(key.substring(0, 7) === "import_") {
                total += 1;
            }
        });
        config.tasks = total;

        mappingStore.each(function(record){
            if(record.get('mapping') == 0) {
                config[record.get('group')+'['+record.get('internalId')+']'] = "";
            } else {
                config[record.get('group')+'['+record.get('internalId')+']'] = record.get('mapping');
            }
        }, this);

        config.action = 'import';

//            me.progressWindow = me.getView('Shopware.apps.SwagMigration.view.Progress').create();

        me.progressWindow = Ext.MessageBox.show({
            title        : 'Import',
            msg          : "{s name=importPendingMessage}Depending on the import settings and the amount of data being imported, import might take a while.{/s}",
            width        : 300,
            progress     : true,
            closable     : false
        });

        // workaround to set the height of the MessageBox
        me.progressWindow.setSize(300, 150);
        me.progressWindow.doLayout();


        me.progressWindow.progressBar.reset();
        me.progressWindow.progressBar.updateProgress(0, '{s name=startingImport}Starting Import...{/s}');

        me.runImportRequest(config);

    },

    /**
     * Runs the actual import request
     * @param config The config object with the database credentials, the mapping information and the import settings
     */
    runImportRequest: function(config) {
        var me = this;
        console.log("Config", config);

        Ext.Ajax.request({
            url: '{url controller="SwagMigration"}/'+config.action,
            params : config,
            method: 'POST',
            success: function (response, request) {
                if(!response.responseText) {
                    return;
                }
                result = Ext.JSON.decode(response.responseText);
                if(!result) {
                    me.progressWindow.close();
                    Ext.Msg.alert('{s name=importFailes}Import failed{/s}', response.responseText);
                } else if(!result.success) {
                    me.progressWindow.close();

                    var message = '<b>' + result.message + '</b>' +  '<br><br>' +
                               '<b>Code</b>  : ' + result.code + '<br>' +
                               '<b>Line</b>  : ' + result.line + '<br>' +
                               '<b>File</b>  : ' + result.file + '<br><br>' +
                               '<b>Error</b> : ' + result.error + '<br>' +
                               '<b>Trace</b> : ' + result.trace + '<br>';

                    Ext.Msg.alert('{s name=importFailes}Import failed{/s}', message);
                } else if(result.progress<1 || result.done !== true) {
                    // If special value -1 was returned, calculate total progress from number of done tasks
                    if(result.progress === -1) {
                        result.progress = me.getDoneTasks(config);
                    }
                    me.progressWindow.progressBar.updateProgress(result.progress, result.message);
                    Ext.iterate(result, function(key, value) {
                        config[key] = value;
                    });
                    me.runImportRequest(config);
                } else {
                    me.progressWindow.close();
                    Ext.Msg.alert('Import', result.message);
                }
            },
            failure: function (response, request) {
                me.progressWindow.close();
                if(response.responseText) {
                    Ext.Msg.alert('{s name=importFailes}Import failed{/s}', response.responseText);
                }
            }
        });
    },

    /**
     * Helper function which returns the number of done tasks from a given config object
     * @param config
     */
    getDoneTasks: function(config) {
        var me = this,
        done = 0,
        total = config.tasks;

        if(total === 0) {
            return 1;
        }

        Ext.iterate(config, function(key, value) {
            if(key.substring(0, 7) === "import_" && value === null) {
                done += 1;
            }
        });

        if(done === 0) {
            return 0;
        }

        return done/total;

    },

    /**
     * Helper method to load the mapping stores on the second card
     */
    loadMappingStores: function(panel) {
        var me = this,
            layout = panel.getLayout(),
            items = layout.getLayoutItems(),
            databaseCard = items[0],
            databaseValues = databaseCard.getForm().getValues(),
            mappingCard = items[1];

        mappingCard.mappingStoreLeft.getProxy().extraParams = databaseValues;
        mappingCard.mappingStoreRight.getProxy().extraParams = databaseValues;

        // Load the stores and force validation one they have been loaded
        mappingCard.mappingStoreLeft.load(function(data, operation, success) {
            if (success) {
                me.onValidateCurrentCard();
            }
        });
        mappingCard.mappingStoreRight.load(function(data, operation, success) {
            if (success) {
                me.onValidateCurrentCard();
            }
        });

    },

    /**
     * Called when a card fires the "validate" event
     */
    onValidateCurrentCard: function() {
        var me = this,
            panel = me.getWizardPanel();

        me.checkLayoutButtons(panel);
    },

    /**
     * Helper function to enable and disable the navigation buttons
     * @param panel
     */
    checkLayoutButtons: function(panel) {
        var me = this,
            valid = false,
            layout = panel.getLayout(),
            prevButton = panel.buttonPrev,
            nextButton = panel.buttonNext,
            activeCard = layout.getActiveItem();

        if(activeCard.internalId === 2) {
            nextButton.setText('{s name=startBtn}Start{/s}');
        }else{
            nextButton.setText('{s name=nextBtn}Next{/s}');
        }

        prevButton.setDisabled((activeCard.internalId <= 0));

        if(activeCard.validate) {
            valid = activeCard.validate();
        }else{
            valid = activeCard.getForm().isValid();
        }

        if(valid && activeCard.internalId >= 3) {
            valid = false;
        }

        nextButton.setDisabled(!valid);

    }


});
//{/block}
