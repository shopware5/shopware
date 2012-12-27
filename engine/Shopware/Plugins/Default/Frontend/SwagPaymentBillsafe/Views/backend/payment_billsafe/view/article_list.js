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
Ext.define('PaymentBillsafe.view.ArticleList', {

	extend: 'Ext.grid.Panel',

	title: 'Artikel / Positionen',
	anchor: '100%',
	height: 200,
	margin: '5 0',
	viewConfig: {
        stripeRows: true
    },
    selType: 'cellmodel',
    
    columns: [{
        text     : 'Artikelnummer',
        width    : 85,
        sortable : true,
        dataIndex: 'number',
        field    : 'textfield'
    },{
        text     : 'Name',
        width    : 160,
        sortable : true,
        dataIndex: 'name',
        field    : 'textfield'
    },{
        text     : 'Typ',
        width    : 60,
        sortable : true,
        dataIndex: 'type',
        editor   : {
			xtype: 'combo',
			queryMode: 'local',
			displayField: 'name',
			valueField: 'type'
        }
    },{
        text     : 'Anzahl',
        width    : 60,
        sortable : true,
        dataIndex: 'quantity',
        field    : 'numberfield'
    },{
        text     : 'Verschickt',
        width    : 60,
        sortable : true,
        dataIndex: 'quantity_shipped',
        field    : 'numberfield'
    },{
        text     : 'MwSt.',
        width    : 60,
        sortable : true,
        align    : 'right',
        dataIndex: 'tax',
        renderer : function(value, column, model) {
        	return Ext.util.Format.number(value, '0.000,00/i') + ' %';
        },
        field    : 'numberfield'
    },{
        text     : 'Betrag',
        width    : 80,
        sortable : true,
        align    : 'right',
        renderer : function(value, column, model) {	
        	return Ext.util.Format.number(value, '0.000,00/i') + ' ' + 'EUR';
        	return model.data.price_format;
        },
        dataIndex: 'price',
        field    : 'numberfield'
    }],
    initComponent: function() {
    	this.cellEditing = Ext.create('Ext.grid.plugin.CellEditing', {
            clicksToEdit: 1
        });
        this.plugins = [this.cellEditing];
        this.columns[2].editor.store = this.detailView.articleTypeStore;
        
    	this.buttons = [{
			text:'Position hinzufügen',
			handler: function (a, b, c){
                var r = this.store.add({ quantity: 1 });
                this.cellEditing.startEdit(r);
			},
			scope: this
		},{
			text:'Teilstornierung / Teilretoure / Artikel speichern',
			handler: function (a, b, c){
				Ext.MessageBox.confirm('Artikel speichern', 'Wollen Sie wirklich die Artikel speichern?', function(r){
					if(r!='yes') {
						return;
					}
					var form = this.detailView.getForm();
					if (!form.isValid()) {
						return;
					}
					Ext.MessageBox.wait('Bitte warten ...', 'Artikel speichern');
					
					var data = {};
					this.store.each(function(record, i) {
						for(key in record.data) {
							data['articleList['+i+']['+key+']'] = record.data[key];
						}
					}, this);
					
					form.submit({
						params: data,
						url: '{url action=cancel}',
						success: function(form, action) {
							Ext.Msg.alert('Erfolgreich', 'Die Artikel konnten erfolgreich gespeichert werden.');
							this.detailView.loadDetail();
						},
						failure: this.detailView.onSubmitFailure,
						scope: this
					});
				}, this);
			},
			scope: this
		}];
		this.callParent();
    }
});