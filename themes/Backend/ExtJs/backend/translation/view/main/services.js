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
 * @package    Translation
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/translation/view/main}

/**
 * Shopware UI - Translation Manager Services Window
 *
 * todo@all: Documentation
 */
//{block name="backend/translation/view/main/window"}
Ext.define('Shopware.apps.Translation.view.main.Services',
/** @lends Ext.window.Window# */
{
    extend: 'Ext.window.Window',
    title: '{s name=services_title}Translation services{/s}',
    alias: 'widget.translation-main-services-window',
    width: 400,
    height: 275,

    /**
     * Initializes the component and builds up the main interface
     *
     * @public
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.items = [{
            xtype: 'form',
            layout: 'anchor',
            border: false,
            defaults: {
                anchor: '100%',
                labelWidth: 155,
                labelStyle: 'font-weight: 700;'
            },
            bodyPadding: '10 10 10 10',
            items: me.createItems()
        }];
        me.buttons = me.createActionButtons();

        me.callParent(arguments);
    },

    /**
     * Creates the items for the main window.
     *
     * @private
     * @return [array] generated items array
     */
    createItems: function() {
        var me = this;

        return [{
            xtype: 'displayfield',
            fieldLabel: '{s name=service_name}Service{/s}',
            fieldStyle: 'font-weight: bold',
            value: me.serviceName
        }, {
            xtype: 'displayfield',
            fieldLabel: '{s name=source_language}Original language{/s}',
            value: me.activeLanguage.get('text')
        }, {
            xtype: 'combobox',
            fieldLabel: '{s name=select_field}Select field{/s}',
            queryMode: 'local',
            displayField: 'displayField',
            valueField: 'valueField',
            triggerAction: 'all',
            store: me.fieldStore,
            name: 'translationField'
        }, {
            xtype: 'combobox',
            fieldLabel: '{s name=translate_to}Translate into{/s}',
            displayField: 'name',
            valueField: 'id',
            triggerAction: 'all',
            queryMode: 'remote',
            store: me.langStore,
            name: 'language'
        }, {
            xtype: 'container',
            style: 'font-style: italic; color: #999', // todo@stp - remove styling in sw4 phase "decline"
            html: '{s name=note_external_services}Please note that external translation services open in a new browser window when you press the start translation button.{/s}'
        }];
    },

    /**
     * Creates the buttons which will be rendered
     * in an docked toolbar at the bottom of the
     * window
     *
     * @private
     * @return [array] generated buttons array
     */
    createActionButtons: function() {
        var me = this;

        return [{
            text: '{s name=button/cancel}Cancel{/s}',
            cls: 'secondary',
            handler: function() {
                me.destroy();
            }
        },{
            text: '{s name=button/start_translation}Start translation{/s}',
            cls: 'primary',
            action: 'translation-main-services-window-translate'
        }]
    }
});
//{/block}
