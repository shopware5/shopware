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
 * @package    NewsletterManager
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name="backend/newsletter_manager/main"}

/**
 * Shopware UI - Editor
 * View for the editor which allows the user to create new newsletters
 */
//{block name="backend/newsletter_manager/view/newsletter/Settings"}
Ext.define('Shopware.apps.NewsletterManager.view.newsletter.Settings', {
    extend: 'Ext.form.Panel',
    alias: 'widget.newsletter-manager-newsletter-settings',
    title: '{s name=title/Settings}Settings{/s}',
    autoScroll:true,

    cls: 'shopware-form',
    layout: 'anchor',
    defaults: {
        anchor: '100%',
        margin: 10
    },
    labelWidth: 170,

    /**
     * Initializes the component, sets up toolbar and pagingbar and and registers some events
     *
     * @return void
     */
    initComponent: function() {
        var me = this;

        // Create the items of the container
        me.items = me.getFieldSets();

        me.addEvents(
            /**
             * Fired when the user changes a formfield
             * @param field
             */
            'formChanged'
        );

        me.callParent(arguments);

        me.form.getFields().each(
                function (field) {
                    field.on('change', function (f, n, o) {
                        me.fireEvent('formChanged', me.form);
                    });
                }
        );

    },

    /**
     * Returns a array with fieldset-components - one for the settings, one for the recipient-groups
     */
    getFieldSets: function() {
        var me = this;


        return [
            {
                xtype: 'fieldset',
                defaults: {
                    anchor: '100%'
                },
                title: '{s name=campaignSettings}Newsletter settings{/s}',
                items: me.getCampaignFieldset(),
                flex: 1
            },
            {
                xtype: 'fieldset',
                title: '{s name=selectNewsletterRecipients}Select newsletter recipients{/s}',
                items: me.getRecipientsFieldset(),
                flex: 1,
                defaults:  { anchor: '100%' },
                layout: 'anchor'
            }
        ];
    },

    /**
     * Helper function to get the number of customers from a group id
     *
     * @param id
     * @param thisIsCustomer
     * @return int
     */
    getNumberOfCustomersInGroup: function(id, thisIsCustomer) {
        var me = this,
            recipientGroupRecord;

        recipientGroupRecord = me.recipientGroupStore.findBy(function(rec) {
            var otherIsCustomer = rec.get('isCustomerGroup');
            if(thisIsCustomer == otherIsCustomer && rec.get('internalId') == id){
                return true;
            }
        });

        if(recipientGroupRecord > -1) {
            recipientGroupRecord = me.recipientGroupStore.getAt(recipientGroupRecord);
            if(recipientGroupRecord.get('number') == null){
                return 0;
            }
            return recipientGroupRecord.get('number');
        }
        return 0;
    },

    /**
     * Dynamically creates a fieldset with checkboxes for each customer / newsletter group
     *
     * @return Array
     */
    getRecipientsFieldset: function() {
        var me = this,
            groups = me.record.getGroups(),
            foundGroup, count,
            checkBox;

        // create customer checkboxes and put them into an array
        me.customerGroups = new Array();
        me.customerGroupStore.each(function(record) {
            count = me.getNumberOfCustomersInGroup(record.get('id'), true);
            checkBox = Ext.create('Ext.form.field.Checkbox', {
                boxLabel: record.get('name') + Ext.String.format("{s name=customerCount} ({literal}{0}{/literal} customer(s)){/s}", count),
                name: record.get('name'),
                count: count,
                record: record
            });
            foundGroup = groups.findRecord('groupkey', record.get('key'), 0, false, false, true);
            if(foundGroup !== null) {
                checkBox.setValue(true);
            }
            me.customerGroups.push(checkBox);
        });

        // create newsletter group checkboxes and put them into an array
        me.newsletterGroups = new Array();
        me.newsletterGroupStore.each(function(record) {
            count = me.getNumberOfCustomersInGroup(record.get('id'), false);
            checkBox = Ext.create('Ext.form.field.Checkbox', {
                boxLabel: record.get('name') + Ext.String.format("{s name=receiverCount} ({literal}{0}{/literal} receiver){/s}", count),
                name: record.get('name'),
                count: count,
                record: record
            });
            foundGroup = groups.findRecord('internalId', record.get('id'), 0, false, false, true);
            if(foundGroup !== null) {
                checkBox.setValue(true);
            }
            me.newsletterGroups.push(checkBox);
        });

        return [
            {
                xtype: 'checkboxgroup',
                fieldLabel: '{s name=customerGroups}Customer groups{/s}',
                items: me.customerGroups,
                labelWidth: 170,
                columns: 2,
                vertical: false,
                name: 'customerGroups'
            },
            {
                xtype: 'checkboxgroup',
                fieldLabel: '{s name=ownNewsletterGroups}Own recipient groups{/s}',
                labelWidth: 170,
                items: me.newsletterGroups,
                columns: 2,
                vertical: false,
                name: 'newsletterGroups'
            }
        ];
    },

    /**
     * Create the main settings fieldset and return it
     */
    getCampaignFieldset: function() {
        var me = this;

        return [
            {
                xtype: 'textfield',
                fieldLabel: '{s name=subject}Subject:{/s}',
                name: 'subject',
                allowBlank: false
            },
            {
                xtype: 'combobox',
                fieldLabel: '{s name=sender}Sender:{/s}',
                allowBlank: false,
                valueField: 'id',
                displayField: 'name',
                store: me.senderStore,
                queryMode: 'local',
                name: 'senderId',
                editable: false
            },
            {
                xtype:  'combobox',
                fieldLabel: '{s name=customerGroupLabel}Customer group:{/s}',
                allowBlank: false,
                valueField: 'key',
                displayField: 'name',
                queryMode: 'local',
                store: me.customerGroupStore,
                name: 'customerGroup',
                editable: false
            },
            {
                xtype: 'combobox',
                fieldLabel: '{s name=languageSelection}Select Language:{/s}',
                allowBlank: false,
                store: me.shopStore,
                valueField: 'id',
                displayField: 'name',
                queryMode: 'local',
                name: 'languageId',
                editable: false
            },
            {
                xtype: 'combobox',
                fieldLabel: '{s name=dispatch}Dispatch:{/s}',
                allowBlank: false,
                store: me.dispatchStore,
                valueField: 'id',
                displayField: 'name',
                queryMode: 'local',
                name: 'dispatch',
                editable: false
            },
            {
                xtype: 'checkbox',
                fieldLabel: '{s name=publish}Published:{/s}',
                name: 'publish',
                checked: me.record.get('publish'),
                inputValue: 1,
                uncheckedValue: 0,
                listeners: {
                    change: function(field, newValue, oldValue) {
                        me.fireEvent('changePublish', me.record, newValue);
                    }
                }
            }
        ];
    }


});
//{/block}
