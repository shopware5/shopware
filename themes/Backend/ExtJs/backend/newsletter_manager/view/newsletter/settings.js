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

// {namespace name="backend/newsletter_manager/main"}

/**
 * Shopware UI - Editor
 * View for the editor which allows the user to create new newsletters
 */
// {block name="backend/newsletter_manager/view/newsletter/Settings"}
Ext.define('Shopware.apps.NewsletterManager.view.newsletter.Settings', {
    extend: 'Ext.form.Panel',
    alias: 'widget.newsletter-manager-newsletter-settings',
    title: '{s name=title/Settings}Settings{/s}',
    autoScroll: true,

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
                defaults: { anchor: '100%' },
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
            if (thisIsCustomer == otherIsCustomer && rec.get('internalId') == id) {
                return true;
            }
        });

        if (recipientGroupRecord > -1) {
            recipientGroupRecord = me.recipientGroupStore.getAt(recipientGroupRecord);
            if (recipientGroupRecord.get('number') == null) {
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
        me.customerGroups = [];
        me.customerGroupStore.each(function(record) {
            count = me.getNumberOfCustomersInGroup(record.get('id'), true);
            checkBox = Ext.create('Ext.form.field.Checkbox', {
                boxLabel: record.get('name') + Ext.String.format('{s name=receiverCount} ({literal}{0}{/literal} receiver(s)){/s}', count),
                name: record.get('name'),
                count: count,
                record: record
            });
            foundGroup = groups.findRecord('groupkey', record.get('key'), 0, false, false, true);
            if (foundGroup !== null) {
                checkBox.setValue(true);
            }
            me.customerGroups.push(checkBox);
        });

        // create newsletter group checkboxes and put them into an array
        me.newsletterGroups = [];
        me.newsletterGroupStore.each(function(record) {
            count = me.getNumberOfCustomersInGroup(record.get('id'), false);
            checkBox = Ext.create('Ext.form.field.Checkbox', {
                boxLabel: record.get('name') + Ext.String.format('{s name=receiverCount} ({literal}{0}{/literal} receiver){/s}', count),
                name: record.get('name'),
                count: count,
                record: record
            });
            foundGroup = groups.findRecord('internalId', record.get('id'), 0, false, false, true);
            if (foundGroup !== null) {
                checkBox.setValue(true);
            }
            me.newsletterGroups.push(checkBox);
        });

        var factory = Ext.create('Shopware.attribute.SelectionFactory');
        me.customerStreamSelection = Ext.create('Shopware.form.field.CustomerStreamGrid', {
            name: 'customerStreamIds',
            labelWidth: 170,
            height: 150,
            fieldLabel: '{s name="customer_streams"}{/s}',
            helpText: '{s name="customer_streams_help"}{/s}',
            store: factory.createEntitySearchStore('Shopware\\Models\\CustomerStream\\CustomerStream'),
            searchStore: factory.createEntitySearchStore('Shopware\\Models\\CustomerStream\\CustomerStream'),
            displayNewsletterCount: true
        });

        var value = [];
        groups.each(function(group) {
            if (group.get('streamId')) {
                value.push(group.get('streamId'));
            }
        });
        me.customerStreamSelection.setValue(value.join('|'));

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
            },
            me.customerStreamSelection
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
                xtype: 'combobox',
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
                xtype: 'container',
                layout: {
                    type: 'hbox',
                    border: false
                },
                margin: '3 0 5 0',
                items: me.getTimedDeliveryFieldSet()
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
            },
            {
                xtype: 'checkbox',
                fieldLabel: '{s name=ready_for_sending}Release for sending:{/s}',
                name: 'released',
                inputValue: 1,
                uncheckedValue: 0,
                helpTitle: '{s name=active/help_title}Release a newsletter{/s}',
                helpText: '{s name=active/help_text}These option releases the newsletter for the cronjob. If you don\'t use a cronjob you can ignore this option.{/s}',
                listeners: {
                    change: function(field, newValue, oldValue) {
                        me.fireEvent('changeActive', me.record, newValue);
                    },
                    render: function (field) {
                        if (me.record.get('status') == 2) {
                            field.setDisabled(true);
                            field.helpTitle = '{s name=active/error/help_title}Error{/s}';
                            field.helpText = '{s name=error/active_text}A delivered newsletter can\'t change the released option.{/s}';
                        } else if (me.record.get('status') > 0) {
                            field.setValue(1);
                        } else {
                            field.setValue(0);
                        }
                    }
                }
            }
        ];
    },

    /**
     * Returns the date and time configuration for the timed delivering of newsletter
     */
    getTimedDeliveryFieldSet: function() {
        var me = this;

        me.timedDeliveryTimeField = Ext.create('Ext.form.field.Time', {
            allowBlank: true,
            name: 'timedDeliveryTime',
            value: Ext.Date.parse('6pm', 'ga'),
            helpTitle: '{s name=send_at/support/title}Configure time of delivery{/s}',
            helpText: '{s name=send_at/support/text}With this setting you can define when you want to send the newsletter. The execution of the newsletter can be for example via a cron job. This setting is optional.{/s}',
            listeners: {
                change: function(field, newValue, oldValue) {
                    me.fireEvent('changeDeliveryTime', me.record, newValue, oldValue);
                },
                render: function(field) {
                    field.setValue(me.record.get('timedDelivery'));
                }
            },
            validator: function(value) {
                var timedDelivery = Ext.getCmp('timedDeliveryDate');

                if (!me.timedDeliveryDateField.getValue() && value) {
                    me.timedDeliveryDateField.markInvalid('{s name=send_at/error/no_date}You must configure the date.{/s}');
                }

                if (value && !timedDelivery.getValue()) {
                    return '{s name=send_at/error/no_date}You must configure the date.{/s}';
                }

                return true;
            }
        });

        me.timedDeliveryDateField = Ext.create('Ext.form.field.Date', {
            fieldLabel: '{s name=send_at}Send at:{/s}',
            allowBlank: true,
            name: 'timedDeliveryDate',
            id: 'timedDeliveryDate',
            width: '40%',
            minValue: new Date(),
            listeners: {
                change: function(field, newValue, oldValue) {
                    me.fireEvent('changeDeliveryDate', me.record, newValue, oldValue);
                    me.setTimeFieldMinValue();
                },
                render: function(field) {
                    field.setValue(me.record.get('timedDelivery'));
                }
            }
        });

        return [
            me.timedDeliveryDateField,
            me.timedDeliveryTimeField
        ];
    },

    /**
     * Sets the time field min value to get a time in the future if the user set the current date
     */
    setTimeFieldMinValue: function() {
        var me = this,
            timedDeliveryDate = me.record.get('timedDeliveryDate'),
            currentDate = new Date();

        // don't set a min value if the time is not defined at the moment
        if (timedDeliveryDate == Ext.undefined) {
            return;
        }

        timedDeliveryDate = Ext.Date.clearTime(timedDeliveryDate);
        currentDate = Ext.Date.clearTime(currentDate);

        // set the min value if the date the user set is equal to the current date
        if (Ext.Date.isEqual(currentDate, timedDeliveryDate)) {
            me.timedDeliveryTimeField.setMinValue(new Date());
        } else {
            // Resets the min value
            me.timedDeliveryTimeField.setMinValue(Ext.Date.clearTime(new Date()));
        }
    }
});
// {/block}
