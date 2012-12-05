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
 * @package    Shopware_Plugins
 * @subpackage Plugin
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     $Author$
 */

/**
 * todo@all: Documentation
 */
Ext.define('PaymentBillsafe.view.DetailPause', {
    extend: 'Ext.form.FieldSet',
	title: 'Zahlung pausieren',
    items: [{
		xtype: 'datefield',
		fieldLabel: 'Datum bis',
		name: 'pause',
		hiddenName: 'pause',
		minValue: new Date(),
		anchor: '100%'
	},{
        xtype: 'button',
		style: 'float: right;',
		text: 'Pausieren',
		itemId: 'pause_button'
	}],
	initComponent: function() {
        this.callParent();
        
        this.getComponent('pause_button').on('click', function() {
			Ext.MessageBox.wait('Bitte warten ...', this.title);
			this.detailView.getForm().submit({
				url: '{url action=pause}',
				success: function(form, action) {
					Ext.Msg.alert('Erfolgreich', 'Die Zahlung konnte erfolgreich pausiert werden.');
					this.detailView.loadDetail();
				},
				failure: this.detailView.onSubmitFailure,
				scope: this
			});
		}, this);
    }
});