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
 * @package    Emotion
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */
//{namespace name=backend/emotion/view/components/banner}
Ext.define('Shopware.apps.Emotion.view.components.fields.LinkTarget', {

    extend: 'Ext.form.field.ComboBox',

    alias: 'widget.emotion-components-fields-link-target',

    snippets: {
        fieldLabel: '{s name="targetLabel"}{/s}',
        targetTopLabel: '{s name="targetTop"}{/s}',
        targetBlankLabel: '{s name="targetBlank"}{/s}'
    },

    initComponent: function() {
        var me = this;

        Ext.apply(me, {
            fieldLabel: me.snippets.fieldLabel,
            queryMode: 'local',
            valueField: 'value',
            displayField: 'label',
            store: Ext.create('Ext.data.Store', {
                fields: ['value', 'label'],
                data : [
                    { 'value': '_top', 'label': me.snippets.targetTopLabel },
                    { 'value': '_blank', 'label': me.snippets.targetBlankLabel }
                ]
            })
        });

        me.callParent(arguments);
    }
});
