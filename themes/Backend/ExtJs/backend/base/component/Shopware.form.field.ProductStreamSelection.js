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

//{block name="backend/base/Shopware.form.field.ProductStreamSelection"}
Ext.define('Shopware.form.field.ProductStreamSelection', {

    extend: 'Shopware.form.field.PagingComboBox',

    alias: ['widget.productstreamselection', 'widget.streamselect'],

    name: 'stream_selection',

    valueField: 'id',

    displayField: 'formatted_name',

    pageSize: 15,

    triggerAction: 'all',

    labelWidth: 155,

    /**
     * Snippets for the field.
     *
     * @object
     */
    snippets: {
        fields: {
            streamFieldLabel: '{s name="streamFieldLabel"}Product stream{/s}',
            streamFieldEmptyText: '{s name="streamFieldEmptyText"}Please select ...{/s}'
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

        me.fieldLabel = me.fieldLabel || me.snippets.fields.streamFieldLabel;
        me.emptyText = me.emptyText || me.snippets.fields.streamFieldEmptyText;

        me.store = me.createStore();

        me.loadStore();

        me.callParent(arguments);

        me.on('expand', Ext.bind(me.loadStore, me));
    },

    /**
     * Loads the product stream store and
     * selects the current selected stream
     */
    loadStore: function() {
        var me = this;

        me.store.load({
            callback: function() {
                var record = me.store.getById(~~(1 * me.getValue()));
                me.select(record);
            }
        });
    },

    /**
     * Adds the stream icon to the combo box field body.
     */
    afterRender: function() {
        var me = this,
            el = me.getEl(),
            inputCell = el.select('.x-form-trigger-input-cell', true).first(),
            iconCell = new Ext.Element(document.createElement('td')),
            icon = new Ext.Element(document.createElement('span'));

        icon.set({
            'cls': 'sprite-product-streams',
            'style': {
                display: 'inline-block',
                width: '16px',
                height: '16px',
                margin: '0 4px',
                position: 'relative',
                top: '2px'
            }
        });

        iconCell.set({
            'style': {
                width: '24px'
            }
        });

        icon.appendTo(iconCell);
        iconCell.insertBefore(inputCell);
    },

    /**
     * Fix the position of the picker element because of the stream icon.
     *
     * @returns Ext.form.field.Picker
     */
    createPicker: function() {
        var me = this,
                picker = me.callParent(arguments);

        Ext.apply(picker, {
            margin: '2px 0 0 -26px'
        });

        return picker;
    },

    /**
     * Creates the stream store which will be used for the combo box.
     *
     * @public
     * @return Ext.data.Store
     */
    createStore: function() {
        var me = this,
            storeFields = [ 'id', 'name', 'description', {
                name: 'formatted_name',
                convert: function (v, r) {
                        return r.get('id') + " | " + r.get('name');
                    }
                }
            ];

        return Ext.create('Shopware.store.Search', {
            fields: storeFields,
            pageSize: me.pageSize,
            configure: function() {
                return { entity: "Shopware\\Models\\ProductStream\\ProductStream" }
            }
        });
    }
});
//{/block}