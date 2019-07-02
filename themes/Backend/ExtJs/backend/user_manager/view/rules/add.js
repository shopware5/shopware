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
 * @package    UserManager
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/user_manager/view/main}

/**
 * Shopware Backend - User Manager add window
 *
 * The user manager add window is used to create new resources or new privileges.
 */
//{block name="backend/user_manager/view/rules/add"}
Ext.define('Shopware.apps.UserManager.view.rules.Add', {
    /**
     * Extends the Ext.window.Window component
     * @string
     */
    extend: 'Ext.window.Window',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets
     */
    alias: 'widget.user-manager-rule-add',

    /**
     * Configuration for the window border
     */
    border: false,

    /**
     * Window width
     * @int
     */
    width: 400,

    /**
     * True to automatically show the component upon creation. This config option may only be used for floating components or components that use autoRender. Defaults to false.
     * @boolean
     */
    autoShow: true,

    /**
     * Contains all snippets for this component
     * @object
     */
    snippets: {
        resource: {
            title: '{s name=add/resource/title}Create new resource{/s}',
            label: '{s name=add/resource/title}Resource name{/s}',
            empty: '{s name=add/resource/title}Please insert a resource name{/s}'
        },
        privilege: {
            title: '{s name=add/privilege/title}Create new privilege{/s}',
            empty: '{s name=add/privilege/title}Privilege name{/s}',
            label: '{s name=add/privilege/title}Please insert a privilege name{/s}'
        },
        cancel: '{s name=add/cancel}Cancel{/s}',
        accept: '{s name=add/save}Save{/s}'
    },

    /**
     * The initComponent template method is an important initialization step for a Component.
     * It is intended to be implemented by each subclass of Ext.Component to provide any needed constructor logic.
     * The initComponent method of the class being created is called first, with each initComponent method up the hierarchy to
     * Ext.Component being called thereafter. This makes it easy to implement and, if needed,
     * override the constructor logic of the Component at any step in the hierarchy.
     * The initComponent method must contain a call to callParent in order to ensure that the parent class' initComponent method is also called.
     */
    initComponent: function() {
        var me = this, label, title, empty;

        me.registerEvents();

        if (me.record.modelName === 'Shopware.apps.UserManager.model.Resource') {
            title = me.snippets.resource.title;
            label = me.snippets.resource.label;
            empty = me.snippets.resource.empty;
        } else {
            title = me.snippets.privilege.title;
            label = me.snippets.privilege.label;
            empty = me.snippets.privilege.empty;
        }
        me.title = title;

        me.nameField = Ext.create('Ext.form.field.Text', {
            fieldLabel: label,
            emptyText: empty,
            labelWidth: 155,
            name: 'name',
            allowBlank: false,
            anchor: '100%'
        });

        me.formPanel = Ext.create('Ext.form.Panel', {
            bodyPadding: 12,
            defaults: {
                labelStyle: 'font-weight: 700'
            },
            items: [ me.nameField ]
        });

        me.items = [ me.formPanel ];
        me.buttons = me.createActionButtons();
        me.callParent(arguments);
        me.formPanel.loadRecord(me.record);
    },

    /**
     * Registers the custom component events.
     */
    registerEvents: function() {
        this.addEvents(
            /**
             * Event will be fired when the user want to create a new resource.
             *
             * @event
             * @param [Ext.window.Window] - The add window
             * @param [Ext.form.Panel] - The form panel
             * @param [Ext.data.Model] - The new record
             * @param [Ext.data.Store] - The rule store
             */
            'saveResource',

            /**
             * Event will be fired when the user want to create a new privilege.
             *
             * @event
             * @param [Ext.window.Window] - The add window
             * @param [Ext.form.Panel] - The form panel
             * @param [Ext.data.Model] - The new record
             * @param [Ext.data.Store] - The rule store
             */
            'savePrivilege'
        );
    },

    /**
     * Creates the accept and cancel button for the form panel.
     * @return [array]
     */
    createActionButtons: function() {
        var me = this;

        this.closeBtn = Ext.create('Ext.button.Button', {
            cls: 'secondary',
            text: me.snippets.cancel,
            handler: function() {
                me.destroy();
            }
        });

        /* {if {acl_is_allowed privilege=create}} */
        this.addBtn = Ext.create('Ext.button.Button', {
            text: me.snippets.accept,
            action: 'user-manager-add-model',
            cls: 'primary',
            handler: function() {
                me.formPanel.getForm().updateRecord(me.record);
                if (me.record.modelName === 'Shopware.apps.UserManager.model.Resource') {
                    me.fireEvent('saveResource', me, me.formPanel,  me.record, me.ruleStore)
                } else {
                    me.fireEvent('savePrivilege', me, me.formPanel, me.record, me.ruleStore)
                }
            }
        });
        /* {/if} */

        return [
            this.closeBtn
        /* {if {acl_is_allowed privilege=create}} */
            ,this.addBtn
        /* {/if} */ ];

    }
});
//{/block}
