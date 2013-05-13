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
 * @package    Performance
 * @subpackage View
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/performance/main}

//{block name="backend/performance/view/tabs/settings/navigation"}
Ext.define('Shopware.apps.Performance.view.tabs.settings.Navigation', {
	extend: 'Ext.tree.Panel',
    alias: 'widget.performance-tabs-settings-navigation',
    rootVisible: false,
    title: 'Settings',
    
    collapsed: false,
    collapsible: true,
    
    width: 200,
    expanded: true,
    useArrows: true,
    displayField: 'text',
    
	/*
	 * The internalName of each item is the xtype of the corresponding fieldset
	 * 
	 * If internalName is empty or has no fieldSet associated, all fieldSets will be hidden
	 */
    listeners: {
    	itemclick: function(tree, record, item, index, e, eOpts) {
    		var internalName = record.get('internalName');
    		
    		this.fireEvent('itemClicked', internalName)
    	}
    },
    
    /*
     * Initialize the component and define the event fired
     */
    initComponent: function() {
    	var me = this;
    	
    	me.addEvents('itemClicked');
    	
    	me.callParent(arguments);
    }
    
});
//{/block}
