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
//{block name="backend/plugin_manager/view/detail/settings"}
Ext.define('Shopware.apps.PluginManager.view.detail.Settings', {
    extend: 'Ext.panel.Panel',
    alias: 'widget.plugin-manager-detail-settings',
    autoScroll: true,
    border: 0,
    layout: 'auto',
    cls: Ext.baseCSSPrefix + 'plugin-manager-detail-settings',
    items: [],

	snippets:{
		properties: '{s name=detail/settings/properties}Plugin properties{/s}',
		description: '{s name=detail/settings/description}Description{/s}',
		key: '{s name=detail/settings/key}Key{/s}',
		name: '{s name=detail/settings/name}Name{/s}',
		author: '{s name=detail/settings/author}Author{/s}',
		copyright: '{s name=detail/settings/copyright}Copyright{/s}',
		support: '{s name=detail/settings/support}Support{/s}',
		link: '{s name=detail/settings/link}Link{/s}',
		active: '{s name=detail/settings/active}Active{/s}',
		version: '{s name=detail/settings/version}Version{/s}',
		namespace: '{s name=detail/settings/namespace}Namespace{/s}',
		source: '{s name=detail/settings/source}Source{/s}',
		added: '{s name=detail/settings/added}Added on{/s}',
		installed: '{s name=detail/settings/installed}Installed on{/s}',
		updated: '{s name=detail/settings/updated}Last update on{/s}',
		activate_plugin: '{s name=detail/settings/activate_plugin}Active plugin{/s}'
	},

    /**
     * Initializes the component.
     *
     * @public
     * @return void
     */
    initComponent: function() {
        var me = this, configForms = [], formId;

        me.items = [];

        if (me.plugin instanceof Ext.data.Model) {
            me.items.push(me.createDescriptionFieldSet());
        }

        if (me.plugin) {
            configForms = me.plugin.get('configForms');
        }

        if(me.plugin) {
            me.checkboxContainer = me.createActiveCheckbox();
            me.items.push(me.checkboxContainer);
        }


        me.callParent(arguments);

        if(configForms.length) {
            configForms = configForms[0];
            formId = configForms.id;
            me.form = Ext.create('Shopware.form.PluginPanel', { formId: formId, descriptionField: false });
            me.add(me.form);
            me.doComponentLayout();
        }
    },

    /**
     * Creates the description field set for the plugin informations.
     */
    createDescriptionFieldSet: function() {
        var me = this;

        return Ext.create('Ext.form.FieldSet', {
            xtype: 'fieldset',
            layout: 'column',
            margin: '10 10 0',
            cls: Ext.baseCSSPrefix + 'plugin-manager-plugin-properties',
            bodyPadding: 5,
            title: me.snippets.properties,
            items: [
                me.createLeftContainer(),
                me.createRightContainer(),
                me.createBottomContainer()
            ]
        });
    },

    createLeftContainer: function() {
        var me = this;

        return Ext.create('Ext.container.Container', {
            defaults: {
                labelWidth: 130,
                xtype: 'displayfield'
            },
            columnWidth: 0.5,
            items: [{
                value: me.plugin.get('name'),
                fieldLabel: me.snippets.key
            }, {
                value: me.plugin.get('label'),
                fieldLabel: me.snippets.name
            },
            {
                value: me.plugin.get('author'),
                fieldLabel: me.snippets.author
            },
            {
                value: me.plugin.get('copyright'),
                fieldLabel: me.snippets.copyright
            },
            {
                value: me.plugin.get('support'),
                fieldLabel: me.snippets.support
            },
            {
                value: me.plugin.get('link'),
                fieldLabel: me.snippets.link
            }]
        });
    },

    createRightContainer: function() {
        var me = this;

        return Ext.create('Ext.container.Container', {
            defaults: {
                xtype: 'displayfield',
                labelWidth: 130
            },
            columnWidth: 0.5,
            items: [{
                value: me.plugin.get('version'),
                fieldLabel: me.snippets.version
            },
            {
                value: me.plugin.get('namespace'),
                fieldLabel: me.snippets.namespace
            },
            {
                value: me.plugin.get('source'),
                fieldLabel: me.snippets.source
            },
            {
                value: me.plugin.get('added'),
                fieldLabel: me.snippets.added
            },
            {
                value: me.plugin.get('installed'),
                fieldLabel: me.snippets.installed
            },
            {
                value: me.plugin.get('updated'),
                fieldLabel: me.snippets.updated
            }]
        });
    },

    createBottomContainer: function() {
        var me = this;

        return Ext.create('Ext.container.Container', {
            margin: '10 0 0',
            layout: {
                type: 'anchor'
            },
            columnWidth: 1,
            items: [{
                xtype: 'displayfield',
                labelWidth: 130,
                value: me.plugin.get('description'),
                fieldLabel: me.snippets.description,
                anchor: '100%'
            }]
        });
    },


    /**
     * Creates the active checkbox within a container.
     *
     * @public
     * @return [object] Ext.container.Container
     */
    createActiveCheckbox: function() {
        var me = this;

        me.checkbox = Ext.create('Ext.form.field.Checkbox', {
            fieldLabel: me.snippets.active,
            inputValue: true,
            labelWidth: 130,
            uncheckedValue: false,
            boxLabel: me.snippets.activate_plugin,
            checked: me.plugin.get('active')
        });

        return Ext.create('Ext.container.Container', {
            padding: '10 10 10 30',
            items: [ me.checkbox ]
        });
    }
});
//{/block}
