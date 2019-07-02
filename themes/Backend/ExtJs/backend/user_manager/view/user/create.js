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
 * Shopware Backend - User administration detail view
 *
 * todo@all: Documentation
 */
//{block name="backend/user_manager/view/user/create"}
Ext.define('Shopware.apps.UserManager.view.user.Create', {
    extend: 'Enlight.app.Window',
    alias : 'widget.usermanager-user-create',
    title : '{s name="create_user/title"}Add/edit user{/s}',
    layout: 'fit',
    autoShow: true,
    autoScroll:true,
    width       : 700,
    height: '90%',
    modal: true,
    apiKeyField: null,
    lastApiKey: '',

    /**
     * Initialize the view components
     *
     * @return void
     */
    initComponent: function() {
        var me = this;

        // Load locales & roles
        me.localeStore = Ext.create('Shopware.apps.UserManager.store.Locale').load();
        me.roleStore = Ext.create('Shopware.apps.UserManager.store.Roles').load();

        me.items = me.getUserForm();

        me.formPanel.loadRecord(me.record);
        me.attributeForm.loadAttribute(null);
        if (me.record.get('id')) {
            me.attributeForm.loadAttribute(me.record.get('id'));
        }

        me.addEvents('saveUser');

        me.dockedItems = [{
            xtype: 'toolbar',
            dock: 'bottom',
            ui: 'shopware-ui',
            cls: 'shopware-toolbar',
            items: ['->', {
                text: '{s name="create_user/cancel"}Cancel{/s}',
                cls: 'secondary',
                scope: me,
                handler: me.close
            }
        /* {if {acl_is_allowed privilege=create}} */
            ,{
                text: '{s name="create_user/save"}Save{/s}',
                action: 'save',
                cls: 'primary',
                handler: function(btn) {
                    me.fireEvent('saveUser', me.record, me.formPanel);
                }
            }
        /* {/if} */]
        }];

        // Add own vtypes to validate password fields
        Ext.apply(Ext.form.field.VTypes, {
            password: function(val, field) {
                if (!field.up('window').edit && !val) return false;
                var repeatField = field.up('window').down('[name=password2]');
                var success = true;
                if (!val) success = true;
                if (val != repeatField.getValue()) success = false;
                if (val.length < 8) success = false;
                repeatField.validate();
                return success;
            },
            passwordText: '{s name="create_user/password_error"}Repeat password and use minimum 8 characters!{/s}',
            passwordRepeat: function(val, field) {
                if (!field.up('window').edit && !val) return false;
                var originalField = field.up('window').down('[name=password]');
                var success = true;
                if (val != originalField.getValue()) success = false;
                return success;
            },
            passwordRepeatText: '{s name="create_user/password_error_repeat"}Repeat password properly!{/s}'
        });

        me.callParent(arguments);
    },

    /**
     *
     * @return
     */
    getUserForm: function(){
        this.attributeForm = Ext.create('Shopware.attribute.Form', {
            table: 's_core_auth_attributes'
        });

        this.formPanel = Ext.create('Ext.form.Panel', {
            border      : false,
            layout      : 'anchor',
            autoScroll:true,
            name: 'main-form',
            bodyPadding : 10,
            defaults    : {
                labelWidth: 155
            },
            items : [
                this.getLoginFieldset(),
                this.getApiFieldset(),
                this.getUserBaseFieldset(),
                this.getUserOptionsFieldset(),
                this.attributeForm
            ]
        });
        return this.formPanel;
    },

    /**
     * @return
     */
    getLoginFieldset: function() {
        return Ext.create('Ext.form.FieldSet',
        {
            title: 'Login',
            bodyPadding : 10,
            defaults    : {
                labelWidth: 155
            },
            items: [{
                  // Implementiert das Column Layout
                  xtype: 'container',
                  unstyled: true,
                  layout: 'column',
                  items: [
                  {
                       // Linke Spalte im Column Layout
                       xtype: 'container',
                       unstyled: true,
                       columnWidth: 0.5,
                       items: [
                           {
                                xtype: 'textfield',
                                fieldLabel: '{s name=create_user/username}Username{/s}',
                                anchor: '100%',
                                name: 'username',
                                allowBlank: false
                           },
                           {
                               xtype: 'checkbox',
                               name: 'active',
                               boxLabel: '{s name=create_user/enabled}Enabled{/s}',
                               anchor: '100%',
                               uncheckedValue: 0,

                               inputValue:1,
                               supportText: '{s name=create_user/enabled_info}Enable or disable this account{/s}'
                           }
                       ]
                  },
                  {
                       // Rechte Spalte im Column Layout
                       xtype: 'container',
                       unstyled: true,
                       columnWidth: 0.5,
                       items: [
                           {
                               xtype: 'passwordmeter',
                               fieldLabel: '{s name=create_user/password}Password{/s}',
                               name: 'password',
                               anchor: '100%',
                               margin: '0 0 20 0',
                               labelAlign: 'left',
                               allowBlank: this.edit,
                               vtype: 'password'
                           },
                           this.getUserPasswordFieldRepeat(),
                           this.createUnlockField()
                       ]
                  }
                  ]
                 }]
        }
        );

    },

    /**
     *
     * @return
     */
    getUserPasswordFieldRepeat: function () {
        return Ext.create('Ext.form.field.Text',{
            inputType: 'password',
            fieldLabel: '{s name=create_user/repeat_password}Repeat password{/s}',
            name: 'password2',
            anchor: '100%',
            allowBlank: this.edit,
            supportText: '{s name=create_user/repeat_password_info}Repeat your password{/s}',
            vtype: 'passwordRepeat'
        });
    },

    createUnlockField: function() {
        var me = this,
            disabled = true;

        if (me.record.get('locked')) {
            disabled = false;
        }

        me.unlockContainer = Ext.create('Ext.container.Container', {
            items: [
                {
                    xtype: 'displayfield',
                    fieldLabel: '{s name="create_user/locked_until"}Locked until{/s}',
                    labelAlign: 'left',
                    margin: '0 0 5 0',
                    name: 'lockedUntil',
                    fieldStyle: 'margin-top: 5px',
                    renderer: function (val) {
                        if (disabled) {
                            return '';
                        }

                        return Ext.util.Format.date(val) + ' ' + Ext.util.Format.date(val, timeFormat)
                    }
                }, {
                    xtype: 'button',
                    text: '{s name="create_user/unlock"}Unlock{/s}',
                    iconCls: 'sprite-key--pencil',
                    anchor: '100%',
                    cls: 'small secondary',
                    margin: '0 0 0 105',
                    disabled: disabled,
                    handler: Ext.bind(me.onClickUnlock, me)
                }
            ]
        });

        return me.unlockContainer;
    },

    onClickUnlock: function () {
        this.fireEvent('unlockUser', this.unlockContainer, this.record, this.formPanel);
    },

    randomString: function() {
        var chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz";
        var stringlength = 40;
        var randomstring = '';
        for (var i = 0; i < stringlength; i++) {
            var rnum = Math.floor(Math.random() * chars.length);
            randomstring += chars.substring(rnum, rnum + 1);
        }

        return randomstring;
    },


    /**
     * Creates the container for the password field and the generateApiKey button.
     * @return [Ext.container.Container] - Contains the text field and the button
     */
    getApiKeyContainer:function () {
        var me = this;

        //create the password generation button
        me.apiKeyButton = Ext.create('Ext.button.Button', {
            iconCls:'sprite-license-key',
            tooltip: '{s name=create_user/generate_api_key}Generate API-Key{/s}',
            width: 24,
            /**
             * Add button handler to fire the generatePassword event which is handled
             * in the detail controller. The detail controller generates a password and set it into the password field
             */
            handler: function () {
                var randomString = me.randomString();
                me.apiKeyField.setValue(randomString);
            }
        });

        me.apiKeyField = Ext.create('Ext.form.field.Text', {
            name:'apiKey',
            labelWidth: 75,
            flex: 1,
            allowBlank: false,
            supportText: "{s name=create_user/generate_api_key_support_text}To use the REST API possibly a server configuration is necessary.{/s}",
            readOnly: true,
            fieldLabel: '{s name=create_user/api_key}API-Key{/s}'
        });

        return Ext.create('Ext.container.Container', {
            layout: {
                type: 'hbox',
                pack: 'start'
            },
            items:[ me.apiKeyField, me.apiKeyButton ]
        });
    },



    /**
     * @return
     */
    getApiFieldset: function () {
        var me = this;
        var checked = (me.record.getId() > 0 && me.record.get('apiKey') !== null);

        var fieldset = Ext.create('Ext.form.FieldSet', {
            title: '{s name=create_user/fieldset_api}API Access{/s}',
            bodyPadding: 10,
            items: [{
                // Implementiert das Column Layout
                xtype: 'container',
                unstyled: true,
                layout: 'column',
                items: [{
                    // Linke Spalte im Column Layout
                    xtype: 'container',
                    unstyled: true,
                    columnWidth: 0.3,
                    items: [{
                        xtype: 'checkbox',
                        checked: checked,
                        name: 'apiActive',
                        boxLabel: '{s name=create_user/checkbox_api}Enabled{/s}',
                        anchor: '100%',
                        uncheckedValue: 0,
                        inputValue: 1,
                        handler: function (checkbox, checked) {
                            if (checked) {
                                me.apiKeyField.enable();
                                me.apiKeyButton.enable();
                                if (me.lastApiKey.length > 0) {
                                    me.apiKeyField.setValue(me.lastApiKey);
                                } else {
                                    // call button click event to generate API-Key
                                    me.apiKeyButton.handler.call(me.apiKeyButton.scope, me.apiKeyButton, Ext.EventObject);
                                }
                            } else {
                                me.lastApiKey = me.apiKeyField.getValue();
                                me.apiKeyField.setValue('');
                                me.apiKeyField.disable();
                                me.apiKeyButton.disable();
                            }
                        }
                    }]
                }, {
                    // Rechte Spalte im Column Layout
                    xtype: 'container',
                    unstyled: true,
                    columnWidth: 0.7,
                    items: [ me.getApiKeyContainer() ]
                }]
            }]
        });

        if (!checked) {
            me.apiKeyField.disable();
            me.apiKeyButton.disable();
        }

        return fieldset;
    },

    /**
     *
     * @return
     */
    getUserBaseFieldset: function() {
        return Ext.create('Ext.form.FieldSet',
        {
            title: '{s name=create_user/main_data}Main data{/s}',
            bodyPadding : 10,
            defaults    : {
                labelWidth: 155
            },
            items: [{
                  // Implementiert das Column Layout
                  xtype: 'container',
                  unstyled: true,
                  layout: 'column',
                  items: [
                  {
                       // Linke Spalte im Column Layout
                       xtype: 'container',
                       unstyled: true,
                       columnWidth: 0.5,
                       items: [
                           {
                              xtype: 'textfield',
                              fieldLabel: '{s name=create_user/realname}Real name{/s}',
                              anchor: '100%',
                              name: 'name',
                              allowBlank: false
                           },
                           {
                               xtype: 'textfield',
                               fieldLabel: '{s name=create_user/email}Email address{/s}',
                               anchor: '100%',
                               vtype: 'remote',
                               validationUrl: '{url controller="base" action="validateEmail"}',
                               validationErrorMsg: '{s name=invalid_email namespace=backend/base/vtype}The email address entered is not valid{/s}',
                               name: 'email',
                               allowBlank: false

                           }
                       ]
                  },
                  {
                       // Rechte Spalte im Column Layout
                       xtype: 'container',
                       unstyled: true,
                       columnWidth: 0.5,
                       items: [
                           {
                               xtype:'combobox',
                               triggerAction:'all',
                               name:'localeId',
                               fieldLabel: '{s name=create_user/language}Default language{/s}',
                               store:this.localeStore,
                               valueField:'id',
                               displayField:'name',
                               queryMode: 'local',
                               mode: 'local',
                               required:true,
                               editable:false,
                               forceSelection:true,
                               listConfig: {
                                 action: 'locale'
                               }

                           },
                           {
                              xtype:'combobox',
                              triggerAction:'all',
                              name:'roleId',
                              fieldLabel: '{s name=create_user/role}Member of role{/s}',
                              store: this.roleStore,
                              valueField:'id',
                              displayField:'name',
                              mode: 'local',
                              queryMode: 'local',
                              required:true,
                              editable:false,
                              allowBlank:false,
                              listConfig: {
                               action: 'role'
                              }
                          }
                       ]
                  }
                  ]
                 }]
        }
        );
    },

    /**
     * @return
     */
    getUserOptionsFieldset: function() {
        return Ext.create('Ext.form.FieldSet',
                {
                    title: '{s name=create_user/individual_user_options}Individual user options{/s}',
                    bodyPadding : 10,
                    defaults    : {
                        labelWidth: 155
                    },
                    items: [{
                        // Implementiert das Column Layout
                        xtype: 'container',
                        unstyled: true,
                        layout: 'column',
                        items: [
                            {
                                // Linke Spalte im Column Layout
                                xtype: 'container',
                                unstyled: true,
                                columnWidth: 0.5,
                                items: [
                                    {
                                        xtype: 'checkbox',
                                        name: 'extendedEditor',
                                        boxLabel: '{s name=create_user/checkbox_extended_editor}Extended Editor{/s}',
                                        anchor: '100%',
                                        uncheckedValue: 0,
                                        inputValue: 1,
                                        supportText: '{s name=create_user/checkbox_extended_editor_info}Enable or disable extended editor{/s}'
                                    },
                                    {
                                        xtype: 'checkbox',
                                        name: 'disabledCache',
                                        boxLabel: '{s name=create_user/checkbox_disabled_cache}Disabled cache{/s}',
                                        anchor: '100%',
                                        uncheckedValue: 0,
                                        inputValue: 1,
                                        supportText: '{s name=create_user/checkbox_disabled_cache_info}Enable or disable backend-cache{/s}'
                                    }
                                 ]
                            },
                            {
                                // Rechte Spalte im Column Layout
                                xtype: 'container',
                                unstyled: true,
                                columnWidth: 0.5,
                                items: []
                            }
                        ]
                    }]
                }
        );
    }
});
//{/block}
