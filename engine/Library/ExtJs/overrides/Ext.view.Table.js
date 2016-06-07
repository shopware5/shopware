/**
 * Overrides Ext.view.Table to fix a problem where the display style of a row would not
 * reflect the selection of the underlying grid selection model. That is, prior to
 * ExtJs 4.1.2 a selected row would appear as not selected, after updating it using
 * e.g. the row editing plugin, although the row is still contained in the selection.
 * This is a known issue and was also discuss in Sencha's support forum, see:
 *
 * http://www.sencha.com/forum/showthread.php?244974-Grid-loses-%28visible%29-selection-after-store-record-edit-and-after-comit
 *
 * This override fixes the problem by using the solution provided in the linked support
 * forum topic. The respective lines are marked with a 'Fix:' comment.
 */
Ext.override(Ext.view.Table, {

    onUpdate : function(store, record, operation, changedFieldNames) {
        var me = this,
            index,
            newRow, newAttrs, attLen, i, attName, oldRow, oldRowDom,
            oldCells, newCells, len, i,
            columns, overItemCls,
            isHovered, row,
            // See if an editing plugin is active.
            isEditing = me.editingPlugin && me.editingPlugin.editing;

        if (me.viewReady) {

            index = me.store.indexOf(record);
            columns = me.headerCt.getGridColumns();
            overItemCls = me.overItemCls;

            // If we have columns which may *need* updating (think lockable grid child with all columns either locked or unlocked)
            // and the changed record is within our view, then update the view
            if (columns.length && index > -1) {
                newRow = me.bufferRender([record], index)[0];
                oldRow = me.all.item(index);
                if (oldRow) {
                    oldRowDom = oldRow.dom;
                    isHovered = oldRow.hasCls(overItemCls);

                    // Copy new row attributes across. Use IE-specific method if possible.
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

                    // Replace changed cells in the existing row structure with the new version from the rendered row.
                    oldCells = oldRow.query(me.cellSelector);
                    newCells = Ext.fly(newRow).query(me.cellSelector);
                    len = newCells.length;
                    // row is the element that contains the cells.  This will be a different element from oldRow when using a rowwrap feature
                    row = oldCells[0].parentNode;
                    for (i = 0; i < len; i++) {
                        // If the field at this column index was changed, or column has a custom renderer
                        // (which means value could rely on any other changed field) the update the cell's content.
                        if (me.shouldUpdateCell(columns[i], changedFieldNames)) {
                            // If an editor plugin is active, we carefully replace just the *contents* of the cell.
                            if (isEditing) {
                                Ext.fly(oldCells[i]).syncContent(newCells[i]);
                            }
                            // Otherwise, we simply replace whole TDs with a new version
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
