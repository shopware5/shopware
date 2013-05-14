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

//{namespace name=backend/performance/main}

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
    	text: "{s name=navigation/general}General{/s}",
    	expanded: true,
    	children: [{
        	text: "{s name=navigation/cache}HTTP Cache{/s}",
        	leaf: true,
        	internalName: 'performance-tabs-settings-http-cache'
    	}, {
        	text: "{s name=navigation/seo}SEO{/s}",
        	leaf: true,
        	internalName: 'performance-tabs-settings-seo'
    	}, {
        	text: "{s name=navigation/search}Search{/s}",
        	leaf: true, 
        	internalName: 'performance-tabs-settings-search'
    	}, {
        	text: "{s name=navigation/categories}Categories{/s}",
        	leaf: true, 
        	internalName: 'performance-tabs-settings-categories'
    	},{
            text: "{s name=navigation/various}Various{/s}",
            leaf: true,
            internalName: 'performance-tabs-settings-various'
        }]
	}, {
    	text: "{s name=navigation/crossselling}CrossSelling{/s}",
    	expanded: true, 
    	children: [{ 
    		text: "{s name=navigation/topseller}TopSeller{/s}",
    		name: '21',
    		leaf: true, 
    		internalName: 'performance-tabs-settings-topseller'
		}, {
			text: "{s name=navigation/otherCustomers}Other customers{/s}",
			leaf: true,
			internalName: 'performance-tabs-settings-customers'
		}]
	}]
	},
});
//{/block}
