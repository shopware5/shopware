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
 * @package    Ticket
 * @subpackage View
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Stephan Pohl
 * @author     $Author$
 */

//{namespace name=backend/ticket/main}
//{block name="backend/ticket/view/list/ticket_info"}
Ext.define('Shopware.apps.Ticket.view.list.TicketInfo', {

    /**
     * The parent class that this class extends.
     * @string
     */
    extend:'Ext.panel.Panel',

    /**
     * Set base css class prefix and module individual css class for css styling
     * @string
     */
    cls:Ext.baseCSSPrefix + 'ticket-list-ticket-info',

    /**
     * Layout for the component.
     * @string
     */
    layout: 'fit',

    /**
     * Render the panel collapsed.
     * @boolean
     */
    collapsed: true,

    /**
     * Make the panel collapsible and have an expand/collapse toggle Tool.
     * @boolean
     */
    collapsible: true,

    /**
     * A shortcut for setting a padding style on the body element.
     * @integer
     */
    bodyPadding: 10,

    /**
     * Visible title of the component.
     * @string
     */
    title: '{s name=ticket_info_title}Ticket information{/s}',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.ticket-list-ticket-info',

    /**
     * Initialize the component.
     *
     * @public
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.dataView = me.createDataView();

        me.items = [ me.dataView ];

        me.callParent(arguments);
    },

    /**
     * Creates the data view which renders the ticket
     * information.
     *
     * @public
     * @return [object] Ext.view.View
     */
    createDataView: function() {
        var me = this;

        return Ext.create('Ext.view.View', {
            itemSelector: '.test',
            tpl: me.createViewTemplate()
        });
    },

    /**
     * Creates the template which will be used for
     * the ticket information view.
     *
     * @public
     * @return [object] Ext.XTemplate
     */
    createViewTemplate: function() {
        var me = this,
            snippets = {
                subject: '{s name=dataview/subject}Subject{/s}:',
                message: '{s name=dataview/message}Message{/s}:'
            };

        return new Ext.XTemplate(
            '{literal}<tpl for=".">',
            '<div class="user-request">',
                '<tpl if="subject">',
                    '<h2 class="subject">' + snippets.subject + '&nbsp;{subject}</h2>',
                '</tpl>',
                '<tpl if="message">',
                    '<strong>' + snippets.message + '</strong>',
                    '<div class="message">{message}</div>',
                '</tpl>',
            '</div>',
            '</tpl>{/literal}'
        );
    }

});
//{/block}
