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

//{block name="backend/emotion/view/components/content_type"}
//{namespace name=backend/emotion/view/components/content_type}
Ext.define('Shopware.apps.Emotion.view.components.ContentType', {
    extend: 'Shopware.apps.Emotion.view.components.Base',
    alias: 'widget.emotion-components-content-type',

    snippets: {
        content_type: '{s name="content_type"}{/s}',
        mode: '{s name="sorting"}{/s}'
    },


    initComponent: function() {
        var me = this;
        me.callParent(arguments);

        me.contentTypeSelection = me.down('[name="content_type"]');
        me.contentTypeModeSelection = me.down('[name="mode"]');
        me.hiddenIdsField = me.down('[name="ids"]');

        me.selectionGrid = Ext.create('Shopware.form.field.Grid', {
            labelWidth: 170,
            model: me.getCurrentContentType(),
            hidden: parseInt(me.contentTypeModeSelection.getValue()) !== 2,
            fieldLabel: '{s name="selection"}{/s}'
        });

        if (me.hiddenIdsField.getValue()) {
            me.selectionGrid.setValue(me.hiddenIdsField.getValue());
        }

        me.selectionGrid.on('change', function (grid, value) {
            me.hiddenIdsField.setValue(value);
        });

        me.contentTypeSelection.on('select', this.changeListener, this);
        me.contentTypeModeSelection.on('select', this.changeListener, this);

        me.elementFieldset.add(me.selectionGrid);
    },

    getCurrentContentType: function() {
        var cur = null;

        if (typeof this.settings.record.data.data !== 'object') {
            return cur;
        }

        this.settings.record.data.data.forEach(function (item) {
            if (item.key === 'content_type') {
                cur = item.value;
            }
        });

        return cur;
    },

    changeListener: function () {
        if (this.contentTypeModeSelection.getValue() === 2 && this.contentTypeSelection.getValue()) {
            this.selectionGrid.show();
            this.reconfigureSelection(this.contentTypeSelection.getValue());
        } else {
            this.selectionGrid.hide();
            this.selectionGrid.setValue('');
        }
    },

    reconfigureSelection: function (model) {
        var factory = Ext.create('Shopware.attribute.SelectionFactory');
        this.selectionGrid.store = factory.createEntitySearchStore(model);
        this.selectionGrid.searchStore = factory.createEntitySearchStore(model);
        this.selectionGrid.searchStore.load();

        this.selectionGrid.grid.reconfigure(this.selectionGrid.store);
        this.selectionGrid.searchField.combo.bindStore(this.selectionGrid.searchStore);
    }
});
//{/block}
