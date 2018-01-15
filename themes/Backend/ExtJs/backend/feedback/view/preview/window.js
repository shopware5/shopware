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
 */

//{namespace name=backend/feedback/view/preview}

/**
 * Shopware UI - Feedback preview Window
 *
 * This file contains the business logic for the User Manager module. The module
 * handles the whole administration of the backend users.
 */
//{block name="backend/feedback/view/preview/window"}
Ext.define('Shopware.apps.Feedback.view.preview.Window', {
    extend: 'Enlight.app.Window',
    title: '{s name=window/title}{/s}',
    alias: 'widget.feedback-preview-window',
    border: false,
    layout: {
        type: 'vbox',
        align: 'stretch'
    },
    autoShow: true,
    height: 550,
    width: 500,
    resizable: false,
    maximizable: false,
    stateful: true,
    stateId: 'feedback-preview-window',

    /**
     * Initializes the component and builds up the main interface
     *
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.addEvents(
            'feedback-show-issue-tracker'
        );

        me.imageComponent = Ext.create('Ext.Img', {
            height: 177,
            src: Ext.String.format(
                '{link file="themes/Backend/ExtJs/backend/_resources/resources/themes/images/shopware-ui/beta-feedback-thankyou-[0].png"}',
                Ext.userLanguage !== 'de' ? 'en' : Ext.userLanguage
            )
        });

        me.textComponent = Ext.create('Ext.container.Container', {
            padding: 20,
            flex: 1,
            autoScroll: true,
            style: 'background-color: #ffffff;',
            html: Ext.String.format('{s name=window/info_text}{/s}', '{$SHOPWARE_VERSION}'),
            styleHtmlContent: true
        });

        me.items = [me.imageComponent, me.textComponent];

        me.checkbox = Ext.create('Ext.form.field.Checkbox', {
            padding: '0 0 0 5px',
            itemId: 'disablePreviewFeedback',
            width: 150,
            boxLabel: '{s name=window/do_not_show_again}{/s}'
        });

        me.cancelButton = Ext.create('Ext.button.Button', {
            cls: 'secondary',
            text: '{s name=window/cancel}{/s}',
            handler: function() {
                me.close();
            }
        });

        me.issueTrackerButton = Ext.create('Ext.button.Button', {
            cls: 'primary',
            text: '{s name=window/open_issuetracker}{/s}',
            handler: function() {
                me.fireEvent('feedback-show-issue-tracker', me);
            }
        });

        me.dockedItems = [{
            xtype: 'toolbar',
            dock: 'bottom',
            items:[me.checkbox, '->', me.cancelButton, me.issueTrackerButton]
        }];

        me.callParent(arguments);
    }
});
//{/block}
