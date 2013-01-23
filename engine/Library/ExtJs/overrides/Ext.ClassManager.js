/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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
;(function(Manager, global) {
    /**
     * Checks if a class has already been created.
     *
     * Overridden because Ext does not usually handle situations correctly, where namespaces and classes with the same name exist.
     * Shopware uses this feature extensively:
     *
     * @example
     * <code>
     *     Shopware.apps.Index
     *     Shopware.apps.Login
     *     Shopware.apps.Login.controller.Main
     *     ...
     * </code>
     *
     * @param { String } className
     * @return { Boolean } exist
     */
    Manager.isCreated = function(className) {
        var existCache = this.existCache,
            i, ln, part, root, parts;


        if (this.classes[className] || existCache[className]) {
            return true;
        }

        root = global;
        parts = this.parseNamespace(className);

        for (i = 0, ln = parts.length; i < ln; i++) {
            part = parts[i];

            if (typeof part != 'string') {
                root = part;
            } else {
                if (!root || !root[part]) {
                    return false;
                }

                root = root[part];
            }
        }

        // We need to on [a-z].apps.[a-z] to vertify that sub app classes are ready instead of only their namespace being present.
        if (/^.*\.apps\./.test(className) && typeof root != 'function') {
            return false;
        }

        existCache[className] = true;

        this.triggerCreated(className);

        return true;
    }
})(Ext.ClassManager, Ext.global);