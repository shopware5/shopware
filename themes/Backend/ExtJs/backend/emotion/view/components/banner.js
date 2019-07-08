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
//{namespace name=backend/emotion/view/components/banner}
Ext.define('Shopware.apps.Emotion.view.components.Banner', {
    extend: 'Shopware.apps.Emotion.view.components.Base',
    alias: 'widget.emotion-components-banner',

    snippets: {
        file: '{s name=file}Image{/s}',
        link: '{s name=link}Link{/s}',
        title: '{s name=title}Title{/s}'
    },

    basePath: '',

    initComponent: function() {
        var me = this,
            bannerFile;

        me.callParent(arguments);

        me.addEvents('openMappingWindow');

        me.mediaSelection = me.down('mediaselectionfield');
        me.mediaSelection.on('selectMedia', me.onSelectMedia, me);
        me.mediaSelection.albumId = -3;

        me.bannerFile = me.getFieldByName('file');
        if(me.bannerFile && me.bannerFile.value && me.bannerFile.value.length) {
            me.onSelectMedia('', me.bannerFile.value);
        }

        me.bannerPositionField = me.getFieldByName('bannerPosition');
    },

    getFieldByName: function(name) {
        var me = this,
            items = me.elementFieldset.items.items,
            storeField;

        Ext.each(items, function(item) {
            if(item.name === name) {
                storeField = item;
                return false;
            }
        });

        return storeField;
    },

    onSelectMedia: function(element, media) {
        var me = this,
            isSvg = false;

        me.selectedMedia = Ext.isArray(media) ? media[0] : media;

        var selectedMedia = (Ext.isArray(media) ? media[0].get('path') : media).toLowerCase();
        if (selectedMedia.substring(selectedMedia.length - 4, selectedMedia.length) === '.svg') {
            isSvg = true;
            if (me.bannerPositionField) {
                me.bannerPositionField.setValue('');
            }
        }

        if(!me.previewFieldset) {
            me.previewFieldset = me.createPreviewImageWithMapping(media);
            me.add(me.previewFieldset);
        } else {
            me.previewFieldset.show();
            me.previewImageWithMapping.update({ src: me.basePath + (Ext.isArray(media) ? media[0].get('path') : media) });
            me.previewImage.update({ src: me.basePath + (Ext.isArray(media) ? media[0].get('path') : media) });
        }

        if (isSvg) {
            me.mappingButton.hide();
            me.previewImageWithMapping.hide();
            me.svgNotice.show();
            me.previewImage.show();
        } else {
            me.mappingButton.show();
            me.previewImageWithMapping.show();
            me.svgNotice.hide();
            me.previewImage.hide();
        }
    },

    createPreviewImage: function(media) {
        var me = this,
            tpl = new Ext.Template(
            '<div>',
                '<img class="preview-image--media" src="[src]" alt="Preview Banner">',
            '</div>'
        );

        return me.previewImage = Ext.create('Ext.container.Container', {
            tpl: tpl,
            hidden: true,
            data: {
                src: me.basePath + (Ext.isArray(media) ? media[0].get('path') : media)
            }
        });
    },

    createPreviewImageWithMapping: function(media) {
        var me = this;

        me.previewImageWithMapping = Ext.create('Ext.container.Container', {
            tpl: me.getPreviewImageTemplate(),
            data: {
                src: me.basePath + (Ext.isArray(media) ? media[0].get('path') : media)
            },
            listeners: {
                'afterrender': me.registerPreviewPositionEvents.bind(me)
            }
        });

        me.mappingButton = me.createMappingButton();
        me.svgNotice = Shopware.Notification.createBlockMessage('{s name="svgNotice"}{/s}', 'notice');

        return Ext.create('Ext.form.FieldSet', {
            title: '{s name=preview}Preview image{/s}',
            items: [
                me.svgNotice,
                me.mappingButton,
                me.previewImageWithMapping,
                me.createPreviewImage(media)
            ]
        });
    },

    registerPreviewPositionEvents: function() {
        var me = this,
            el = me.previewImageWithMapping.getEl();

        el.on('click', function(event, target) {
            var $target = Ext.get(target),
                position = $target.getAttribute('data-position');

            Ext.each(el.dom.querySelectorAll('.preview-image--col'), function() {
                this.classList.remove('is--active');
            });
            $target.addCls('is--active');

            me.bannerPositionField.setValue(position);
        }, me, { delegate: '.preview-image--col' });

        if(me.bannerPositionField) {
            var val = me.bannerPositionField.getValue();

            Ext.each(el.dom.querySelectorAll('.preview-image--col'), function() {
                this.classList.remove('is--active');
            });

            var pos = el.dom.querySelector('.preview-image--col[data-position="' + val + '"]');

            if (pos) {
                pos.classList.add('is--active');
            }
        }
    },

    getPreviewImageTemplate: function() {
        return new Ext.Template(
            '<div class="preview-image--container">',
                '<img class="preview-image--media" src="[src]" alt="Preview Banner">',

                '<div class="preview-image--grid">',
                    '<div class="preview-image--row">',
                        '<div class="preview-image--col" data-position="top left">&nbsp;</div>',
                        '<div class="preview-image--col" data-position="top center">&nbsp;</div>',
                        '<div class="preview-image--col" data-position="top right">&nbsp;</div>',
                    '</div>',

                    '<div class="preview-image--row">',
                        '<div class="preview-image--col" data-position="center left">&nbsp;</div>',
                        '<div class="preview-image--col is--active" data-position="center">&nbsp;</div>',
                        '<div class="preview-image--col" data-position="center right">&nbsp;</div>',
                    '</div>',

                    '<div class="preview-image--row">',
                        '<div class="preview-image--col" data-position="bottom left">&nbsp;</div>',
                        '<div class="preview-image--col" data-position="bottom center">&nbsp;</div>',
                        '<div class="preview-image--col" data-position="bottom right">&nbsp;</div>',
                    '</div>',
                '</div>',
            '</div>'
        );
    },

    createMappingButton: function() {
       var me = this,
           button = Ext.create('Ext.button.Button', {
               text: '{s name=mapping}Create image mapping{/s}',
               iconCls: 'sprite-layer-select',
               cls: 'small secondary',
               handler: function() {
                    me.fireEvent('openMappingWindow', me, me.selectedMedia, me.previewImageWithMapping, me.getSettings('record'));
               }
           });

       return Ext.create('Ext.container.Container', {
           margin: '0 0 10',
           items: [ button ]
       });
    }
});
