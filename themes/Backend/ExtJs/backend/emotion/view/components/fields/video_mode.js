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
 * @package    UserManager
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */
//{namespace name=backend/emotion/view/components/video}
Ext.define('Shopware.apps.Emotion.view.components.fields.VideoMode', {
    extend: 'Ext.form.field.ComboBox',
    alias: 'widget.emotion-components-fields-video-mode',
    name: 'video_mode',

    /**
     * Snippets for the component
     * @object
     */
    snippets: {
        fields: {
            'video_mode': '{s name=video/store/mode}Video Modus{/s}',
            'empty_text': '{s name=video/store/pleaseselect}Bitte auswählen{/s}'
        },
        store: {
            'scalable': '{s name=video/store/scaleable}Skalierien{/s}',
            'fill': '{s name=video/store/fill}Fülllen{/s}',
            'stretch': '{s name=video/store/stretch}Strecken{/s}'
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

        Ext.apply(me, {
            emptyText: me.snippets.fields.empty_text,
            fieldLabel: me.snippets.fields.video_mode,
            displayField: 'display',
            valueField: 'value',
            queryMode: 'local',
            triggerAction: 'all',
            store: me.createStore()
        });

        me.callParent(arguments);
    },

    /**
     * Creates a local store which will be used
     * for the combo box. We don't need that data.
     *
     * @public
     * @return [object] Ext.data.Store
     */
    createStore: function() {
        var me = this, snippets = me.snippets.store;

        return Ext.create('Ext.data.Store', {
            fields: [ 'value', 'display' ],
            data: [{
                value: 'scale',
                display: snippets.scalable
            }, {
                value: 'cover',
                display: snippets.fill
            }, {
                value: 'stretch',
                display: snippets.stretch
            }]
        });
    }
});
