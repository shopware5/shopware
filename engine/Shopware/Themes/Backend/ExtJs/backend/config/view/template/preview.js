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

/**
 * todo@all: Documentation
 */

//{namespace name=backend/config/view/form}

//{block name="backend/config/view/template/view"}
Ext.define('Shopware.apps.Config.view.template.Preview', {
    extend: 'Enlight.app.SubWindow',
    alias: 'widget.config-template-preview',

    height: 768,
    width: 1024,

    layout: 'fit',
    basePath: '{link file="templates/"}',
    title: '{s name=template/preview_title}Preview: [name]{/s}',

    initComponent: function() {
        var me = this;

        me.title = new Ext.Template(me.title).applyTemplate(me.template.data);

        Ext.applyIf(me, {
            items: {
                xtype: 'image',
                src: me.basePath + '/' + me.template.get('previewFull'),
                listeners : {
                    render : function(c) {
                        c.getEl().on('click', function(){ this.fireEvent('click', c); }, c);
                    }
                }
            }
        });

        me.callParent(arguments);
    }
});
//{/block}
