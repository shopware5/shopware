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
 * @package    Customer
 * @subpackage Detail
 * @version    $Id$
 * @author shopware AG
 */

// {namespace name=backend/customer/view/detail}

/**
 * Shopware UI - Customer detail page
 *
 * The comment field set contains the internal comment to the customer
 * which is stored in the base model and filled over the s_user table
 */
// {block name="backend/customer/view/detail/comment"}
Ext.define('Shopware.apps.Customer.view.detail.Comment', {
    /**
     * Define that the comment field set is an extension of the Ext.form.FieldSet
     * @string
     */
    extend: 'Ext.form.FieldSet',
    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias: 'widget.customer-comment-field-set',
    /**
     * Set css class for this component
     * @string
     */
    cls: Ext.baseCSSPrefix + 'comment-field-set',
    /**
     * Contains all snippets for the view component
     * @object
     */
    snippets: {
        title: '{s name=comment/title}Comment{/s}',
        label: '{s name=comment/comment_label}Comment{/s}',
        support: '{s name=comment/comment_support}Internal communication only{/s}'
    },

    /**
     * Layout type for the component.
     * @string
     */
    layout: 'anchor',

    /**
     * Component event method which is fired when the component
     * is initials. The component is initials when the user
     * want to create a new customer or edit an existing customer
     * @return void
     */
    initComponent: function () {
        var me = this;
        me.title = me.snippets.title;

        me.items = [{
            anchor: '100%',
            minWidth: 300,
            xtype: 'textarea',
            name: 'internalComment',
            margin: '5 0 0',
            padding: '0 0 5',
            style: 'font-size: 12px',
            grow: true,
            supportText: me.snippets.support
        }];

        me.callParent(arguments);
    }
});
// {/block}
