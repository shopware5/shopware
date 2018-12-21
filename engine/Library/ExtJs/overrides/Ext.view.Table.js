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

/**
 * Overrides Ext.view.Table to fix a problem where the display style of a row would not
 * reflect the selection of the underlying grid selection model. That is, prior to
 * ExtJs 4.1.2 a selected row would appear as not selected, after updating it using
 * e.g. the row editing plugin, although the row is still contained in the selection.
 * This is a known issue and was also discuss in Sencha's support forum, see:
 *
 * http://www.sencha.com/forum/showthread.php?244974-Grid-loses-%28visible%29-selection-after-store-record-edit-and-after-comit
 *
 * This override fixes the problem by using the solution provided in the linked support forum topic.
 * The respective lines are marked with a 'Fix:' comment.
 */
//{block name="extjs/overrides/table"}
Ext.override(Ext.view.Table, {

    onUpdate : function(store, record, operation, changedFieldNames) {
        var me = this,
            index,
            newRow, newAttrs, attLen, i, attName, oldRow, oldRowDom,
            oldCells, newCells, len, i,
            columns, overItemCls,
            isHovered, row,

            isEditing = me.editingPlugin && me.editingPlugin.editing;

        if (me.viewReady) {

            index = me.store.indexOf(record);
            columns = me.headerCt.getGridColumns();
            overItemCls = me.overItemCls;



            if (columns.length && index > -1) {
                newRow = me.bufferRender([record], index)[0];
                oldRow = me.all.item(index);
                if (oldRow) {
                    oldRowDom = oldRow.dom;
                    isHovered = oldRow.hasCls(overItemCls);

                    var rowCls;
                    if (oldRowDom.mergeAttributes) {
                        // Fix: save row class
                        rowCls = oldRowDom.className;
                        oldRowDom.mergeAttributes(newRow, true);
                    } else {
                        // Fix: save row class
                        rowCls = oldRowDom.getAttribute('class');
                        newAttrs = newRow.attributes;
                        attLen = newAttrs.length;
                        for (i = 0; i < attLen; i++) {
                            attName = newAttrs[i].name;
                            if (attName !== 'id') {
                                oldRowDom.setAttribute(attName, newAttrs[i].value);
                            }
                        }
                    }

                    // Fix: Add row class again
                    if (rowCls) {
                        oldRow.addCls(rowCls);
                    }

                    if (isHovered) {
                        oldRow.addCls(overItemCls);
                    }


                    oldCells = oldRow.query(me.cellSelector);
                    newCells = Ext.fly(newRow).query(me.cellSelector);
                    len = newCells.length;

                    row = oldCells[0].parentNode;
                    for (i = 0; i < len; i++) {


                        if (me.shouldUpdateCell(columns[i], changedFieldNames)) {

                            if (isEditing) {
                                Ext.fly(oldCells[i]).syncContent(newCells[i]);
                            }

                            else {
                                row.insertBefore(newCells[i], oldCells[i]);
                                row.removeChild(oldCells[i]);
                            }
                        }
                    }
                }
                me.fireEvent('itemupdate', record, index, newRow);
            }
        }
    }
});
//{/block}