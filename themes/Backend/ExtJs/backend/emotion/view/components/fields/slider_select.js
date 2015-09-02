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
//{namespace name=backend/emotion/view/components/slider_select}
Ext.define('Shopware.apps.Emotion.view.components.fields.SliderSelect', {
    extend: 'Ext.form.field.ComboBox',
    alias: 'widget.emotion-components-fields-category-slider-select',
    name: 'slider_type',

    /**
     * Snippets for the component
     * @object
     */
    snippets: {
        fields: {
            'slider_type': '{s name=article/fields/slider_type}Slider type{/s}',
            'empty_text': '{s name=article/fields/empty_text}Please select...{/s}'
        },
        store: {
            'vertical_slider': '{s name=article/store/vertical_slider}Vertical slider{/s}',
            'horizontal_slider': '{s name=article/store/horizontal_slider}Horizontal slider{/s}'
        }
    },

    /**
     * Initialize the component.
     *
     * @public
     * @return void
     */
    initComponent: function() {
        var me = this;

        Ext.apply(me, {
            emptyText: me.snippets.fields.empty_text,
            fieldLabel: me.snippets.fields.slider_type,
            displayField: 'display',
            valueField: 'value',
            queryMode: 'local',
            triggerAction: 'all',
            store: me.createStore()
        });

        me.callParent(arguments);
    },

    /**
     * Creates a local store which will be used
     * for the combo box. We don't need that data.
     *
     * @public
     * @return [object] Ext.data.Store
     */
    createStore: function() {
        var me = this, snippets = me.snippets.store;

        return Ext.create('Ext.data.JsonStore', {
            fields: [ 'value', 'display' ],
            data: [{
                value: 'horizontal',
                display: snippets.horizontal_slider
            }, {
                value: 'vertical',
                display: snippets.vertical_slider
            }]
        });
    }
});
