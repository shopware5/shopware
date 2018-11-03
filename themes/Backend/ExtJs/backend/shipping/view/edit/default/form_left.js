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
 * @package    Shipping
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

/*{namespace name=backend/shipping/view/edit/default}*/

/**
 * Shopware UI - Shipping Costs
 *
 * todo@all: Documentation
 */
//{block name="backend/shipping/view/edit/default/form_left"}
Ext.define('Shopware.apps.Shipping.view.edit.default.FormLeft', {
    extend      :'Ext.container.Container',
    /**
     * Title of the left hand side form box
     * @string
     */
    title : '{s name=left_title}Config{/s}',
    /**
     * Alias for the left hand side form box
     * @string
     */
    alias : 'widget.shipping-top-left-form',

    /**
     * Default column width
     * @float
     */
    columnWidth : 0.49,
    /**
     * Layout Anchor
     * @string
     */
    layout : 'anchor',
    /**
     * Height of the form
     * @string
     */
    height : 275,
    /**
     * Some default values
     * todo@stp Move this to CSS please :)
     */
    defaults : {
        labelStyle  : 'font-weight: 700; text-align: left;',
        anchor      : '100%',
        xtype       : 'textfield',
        labelWidth  : 80,
        minWidth    : 250
    },
    /**
     * Padding
     * @integer
     */
    bodyPadding : 5,
    /**
     * Set border to zero
     * @integer
     */
    border      : 0,

    /**
     * Array of form elements
     */
    items : [],
    /**
     * Initialize the Shopware.apps.Supplier.view.main.List and defines the necessary
     * default configuration
     * @return void
     */
    initComponent : function() {
        var me = this;

        me.items = me.getFormElements();

        me.callParent(arguments);
    },
    /**
     * Receives Form elements
     * @return array of objects
     */
    getFormElements : function() {
        var me = this;
        return [
            {
                fieldLabel: '{s name=left_name}Name{/s}',
                name: 'name',
                dataIndex : 'name',
                allowBlank: false,
                translatable: true // Indicates that this field is translatable
            },
            {
                fieldLabel: '{s name=left_description}Description{/s}',
                emptyText   : '{s name=left_empty_text_description}Description{/s}',
                name: 'description',
                xtype:'textarea',
                height: 50,
                translatable: true // Indicates that this field is translatable
            },
            {
                fieldLabel: '{s name=left_tracking_url}Tracking URL{/s}',
                translatable: true,
                name: 'statusLink'
            },
            {
                fieldLabel: '{s name=left_comment_tracking}Comment{/s}',
                name: 'comment'
            },
            {
                fieldLabel: '{s name=left_sorting}Sorting{/s}',
                name: 'position',
                xtype: 'numberfield'
            },
            {
                fieldLabel: '{s name=left_active}Active{/s}',
                name: 'active',
                xtype: 'checkbox',
                inputValue  : 1,
                uncheckedValue :  0
            }
        ]
    }
});
//{/block}
