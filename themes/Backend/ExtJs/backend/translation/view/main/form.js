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
 * Shopware UI - Translation Manager Main Form
 *
 * todo@all: Documentation
 */
//{block name="backend/translation/view/main/form"}
Ext.define('Shopware.apps.Translation.view.main.Form',
/** @lends Ext.form.Panel# */
{
    extend: 'Ext.form.Panel',
    alias: 'widget.translation-main-form',
    bodyPadding: 10,
    title: '{s name=form_title}Translatable fields{/s}',
    layout: 'anchor',

    disabled: true,
    defaultType: 'textfield',
    defaults: {
        labelStyle: 'font-weight: 700; text-align: right;',
        labelWidth: 155,
        anchor: '100%'
    },

    /**
     * Original title of the form panel. This is neccessary
     * due to the fact that the title will be overridden in
     * the controller.
     *
     * @default null
     * @string
     */
    originalTitle: null,
    /**
     * Form elements
     * @array
     */
    items : [],

    /**
     * Initializes the component and builds up the main interface
     *
     * @public
     * @return void
     */
    initComponent: function() {
        var me = this, items = [];
        me.originalTitle = me.title;
        Ext.each(me.translatableFields, function(currentField) {
           var elementType = currentField.xtype || '';

           switch(elementType) {
                case 'ace-editor' :
                case 'codemirror' :
                case 'codemirrorfield' :
                    currentField.height = 200;
                    break;
                case 'textfield':
                    currentField.emptyText = Ext.util.Format.htmlEncode(
                        currentField.emptyText
                    );
                    break;
            }
            
            currentField.hidden = false;
            items.push(currentField);
        });
        me.items = items;
        me.callParent(arguments);
    }
});
//{/block}
