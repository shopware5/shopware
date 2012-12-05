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
 *
 * @category   Shopware
 * @package    SwagAboCommerce
 * @subpackage ExtJs
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     shopware AG
 */
//{namespace name="backend/abo_commerce/article/view/main"}
//{block name="backend/abo_commerce/view/abo_commerce/configuration"}
Ext.define('Shopware.apps.Article.view.abo_commerce.Configuration', {

    /**
     * The parent class that this class extends.
     */
    extend: 'Ext.form.Panel',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets
     */
    alias: 'widget.abo-commerce-configuration',

    cls: 'shopware-form',

    layout: 'anchor',

    /**
     * Specifies the border size for this component. The border can be a single numeric value to apply to all
     * sides or it can be a CSS style specification for each style, for example: '10 5 3 10' (top, right, bottom, left).
     * For components that have no border by default, setting this won't make the border appear by itself.
     */
    border: false,

    /**
     * A shortcut for setting a padding style on the body element. The value can either be
     * a number to be applied to all sides, or a normal css string describing padding. Defaults to undefined.
     */
    bodyPadding: 10,

    /**
     * The initComponent template method is an important initialization step for a Component.
     * It is intended to be implemented by each subclass of Ext.Component to provide any needed constructor logic.
     * The initComponent method of the class being created is called first,
     * with each initComponent method up the hierarchy to Ext.Component being called thereafter.
     * This makes it easy to implement and, if needed, override the constructor logic of the Component at any step in the hierarchy.
     * The initComponent method must contain a call to callParent in order to ensure that the parent class' initComponent method is also called.
     *
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.items = [{
            xtype: 'fieldset',
            defaults: {
                labelWidth: 155,
                labelStyle: 'font-weight: bold'
            },
            title: 'Spar-Abo aktivieren',
            items: [
                {
                    xtype: 'container',
                    html: 'Über Spar-Abos haben Sie die Möglichkeit einzelne Artikel als Abonnement mit festen Laufzeiten zu erstellen. So können Sie Ihre Bindung zum Kunden stärken und Ihren Kunde für Ihre Treue mit einen Rabatt auf den Artikel belohnen.',
                    margin: '0 0 15',
                    style: 'color: #999; font-style: italic;'
                },
                {
                    xtype: 'checkboxfield',
                    name: 'active',
                    inputValue: 1,
                    uncheckedValue: 0,
                    anchor: '100%',
                    fieldLabel: 'Spar-Abo aktiveren',
                    boxLabel: 'Durch die Aktivierung wird den Kunden auf der Detailseite eine Auswahl zur Verfügung gestellt, um diesen Artikel als Abo zu bestellen'
                },
                {
                    xtype: 'checkboxfield',
                    name: 'exclusive',
                    inputValue: 1,
                    uncheckedValue: 0,
                    anchor: '100%',
                    fieldLabel: 'Reiner Abo-Artikel',
                    boxLabel: 'Dieser Artikel kann nicht einzeln erworben werden, sondern nur in einen Spar-Abo'
                }
             ]
        }];

        me.callParent(arguments);
    }
});
//{/block}
