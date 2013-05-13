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
 * @subpackage Store
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

/**
 * todo@all: Documentation
 */
//{block name="backend/performance/store/tree"}
Ext.define('Shopware.apps.Performance.store.Tree', {
    extend: 'Ext.data.TreeStore',
    batch: true,
    clearOnLoad: false,
    model : 'Shopware.apps.Performance.model.Tree',
    
    // No need to get the data from the php controller, so defining it inline
    root: {
    expanded: true,
    children: [{
    	text: "Allgemein",
    	expanded: true,
    	children: [{
        	text: "HTTP Cache",
        	leaf: true,
        	internalName: 'performance-tabs-settings-http-cache'
    	}, {
        	text: "SEO",
        	leaf: true,
        	internalName: 'performance-tabs-settings-seo'
    	}, {
        	text: "Suche", 
        	leaf: true, 
        	internalName: 'performance-tabs-settings-search'
    	}, {
        	text: "Kategorien", 
        	leaf: true, 
        	internalName: 'performance-tabs-settings-categories'
    	},{
            text: "Verschiedenes",
            leaf: true,
            internalName: 'performance-tabs-settings-various'
        }]
	}, {
    	text: "Crossselling",
    	expanded: true, 
    	children: [{ 
    		text: "TopSeller",
    		name: '21',
    		leaf: true, 
    		internalName: 'performance-tabs-settings-topseller'
		}, {
			text: "Andere Kunden",
			leaf: true,
			internalName: 'performance-tabs-settings-customers'
		}]
	}]
	},
});
//{/block}
