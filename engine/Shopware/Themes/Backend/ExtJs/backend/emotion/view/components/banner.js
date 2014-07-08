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
 * @package    UserManager
 * @subpackage View
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */
//{namespace name=backend/emotion/view/components/banner}
Ext.define('Shopware.apps.Emotion.view.components.Banner', {
    extend: 'Shopware.apps.Emotion.view.components.Base',
    alias: 'widget.emotion-components-banner',

    snippets: {
        file: '{s name=file}Image{/s}',
        link: '{s name=link}Link{/s}'
    },

    basePath: '{link file=""}',

    initComponent: function() {
        var me = this;
        me.callParent(arguments);

        me.addEvents('openMappingWindow');

        me.mediaSelection = me.down('mediaselectionfield');
        me.mediaSelection.on('selectMedia', me.onSelectMedia, me);

        var bannerFile = me.getBannerFile(me.getSettings('record').get('data'));
        if(bannerFile && bannerFile.value && bannerFile.value.length) {
            me.onSelectMedia('', bannerFile.value);
        }
    },

    getBannerFile: function(data) {
        var record = null;
        Ext.each(data, function(item) {
            if (item.key == 'file') {
                record = item;
                return false;
            }
        });
        return record;
    },

    onSelectMedia: function(element, media) {
        var me = this;
        if(!me.previewImage) {
            me.previewFieldset = this.createPreviewImage(media);
        } else {
            me.previewImage.setSrc(me.basePath + media[0].get('path'));
        }
        me.selectedMedia = Ext.isArray(media) ? media[0] : media;
        me.add(me.previewFieldset);
    },

    createPreviewImage: function(media) {
        var me = this;

        me.previewImage = Ext.create('Ext.Img', {
            src: me.basePath + (Ext.isArray(media) ? media[0].get('path') : media),
            style: {
                'display': 'block',
                'max-width': '100%'
            }
        });

        return Ext.create('Ext.form.FieldSet', {
            title: '{s name=preview}Preview image{/s}',
            items: [ me.createMappingButton(), me.previewImage ]
        });
    },

    createMappingButton: function() {
       var me = this;
       var button = Ext.create('Ext.button.Button', {
           text: '{s name=mapping}Create image mapping{/s}',
           iconCls: 'sprite-layer-select',
           cls: 'small secondary',
           handler: function() {
                me.fireEvent('openMappingWindow', me, me.selectedMedia, me.previewImage, me.getSettings('record'));
           }
       });

       return Ext.create('Ext.container.Container', {
           margin: '0 0 10',
           items: [ button ]
       });
    }
});