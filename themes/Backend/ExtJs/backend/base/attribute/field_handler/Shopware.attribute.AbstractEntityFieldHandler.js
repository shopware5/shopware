/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 *
 * @category    Shopware
 * @package     Base
 * @subpackage  Attribute
 * @version     $Id$
 * @author      shopware AG
 */

Ext.define('Shopware.attribute.AbstractEntityFieldHandler', {
    extend: 'Shopware.attribute.FieldHandlerInterface',
    mixins: {
        factory: 'Shopware.attribute.SelectionFactory'
    },

    entity: null,
    singleSelectionClass: null,
    multiSelectionClass: null,

    supports: function(attribute) {
        if (this.entity == null) {
            return false;
        }
        return (
            (attribute.get('columnType') === 'multi_selection' || attribute.get('columnType') === 'single_selection') &&
            (attribute.get('entity') == this.entity)
        );
    },

    create: function(field, attribute) {
        var me = this;

        if (attribute.get('columnType') === 'single_selection') {
            return me.createSelection(
                field,
                attribute,
                me.singleSelectionClass,
                me.createDynamicSearchStore(attribute)
            );
        }

        return me.createSelection(
            field,
            attribute,
            me.multiSelectionClass,
            me.createDynamicSearchStore(attribute),
            me.createDynamicSearchStore(attribute)
        );
    }
});
