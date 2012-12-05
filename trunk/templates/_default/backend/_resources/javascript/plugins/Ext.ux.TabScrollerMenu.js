/*!
 * Ext JS Library 3.0+
 * Copyright(c) 2006-2009 Ext JS, LLC
 * licensing@extjs.com
 * http://www.extjs.com/license
 */
Ext.ux.TabScrollerMenu =  Ext.extend(Object, {
	pageSize       : 10,
	maxText        : 15,
	menuPrefixText : 'Fenster',
	constructor    : function(config) {
		config = config || {};
		Ext.apply(this, config);
	},
	init : function(tabPanel) {
		Ext.apply(tabPanel, this.tabPanelMethods);
		
		tabPanel.tabScrollerMenu = this;
		var thisRef = this;
		
		tabPanel.on({
			render : {
				scope  : tabPanel,
				single : true,
				fn     : function() { 
					var newFn = tabPanel.createScrollers.createSequence(thisRef.createPanelsMenu, this);
					tabPanel.createScrollers = newFn;
				}
			}
		});
	},
	// private && sequeneced
	createPanelsMenu : function() {
		var h = this.stripWrap.dom.offsetHeight;
		
		//move the right menu item to the left 18px
		var rtScrBtn = this.header.dom.firstChild;
		Ext.fly(rtScrBtn).applyStyles({
			right : '18px'
		});
		
		var stripWrap = Ext.get(this.strip.dom.parentNode);
		stripWrap.applyStyles({
			 'margin-right' : '36px'
		});
		
		// Add the new righthand menu
		var scrollMenu = this.header.insertFirst({
			cls:'x-tab-tabmenu-right'
		});
		scrollMenu.setHeight(h);
		
		scrollMenu.addClassOnOver('x-tab-tabmenu-over');
		scrollMenu.on('click', this.showTabsMenu, this);	
		
		this.scrollLeft.show = this.scrollLeft.show.createSequence(function() {
			scrollMenu.show();												 						 
		});
		
		this.scrollLeft.hide = this.scrollLeft.hide.createSequence(function() {
			scrollMenu.hide();								
		});
		
	},
	// public
	getPageSize : function() {
		return this.pageSize;
	},
	// public
	setPageSize : function(pageSize) {
		this.pageSize = pageSize;
	},
	// public
	getMaxText : function() {
		return this.maxText;
	},
	// public
	setMaxText : function(t) {
		this.maxText = t;
	},
	getMenuPrefixText : function() {
		return this.menuPrefixText;
	},
	setMenuPrefixText : function(t) {
		this.menuPrefixText = t;
	},
	// private && applied to the tab panel itself.
	tabPanelMethods : {
		// all execute within the scope of the tab panel
		// private	
		showTabsMenu : function(e) {		
			if (! this.tabsMenu) {
				this.tabsMenu =  new Ext.menu.Menu();
				this.on('beforedestroy', this.tabsMenu.destroy, this.tabsMenu);
			}
			
			this.tabsMenu.removeAll();
			
			this.generateTabMenuItems();
			
			var target = Ext.get(e.getTarget());
			var xy     = target.getXY();
			
			//Y param + 24 pixels
			xy[0] += -100;
			xy[1] += -60;
			
			this.tabsMenu.showAt(xy);
		},
		// private	
		generateTabMenuItems : function() {
			var curActive  = this.getActiveTab();
			var totalItems = this.items.getCount();
			var pageSize   = this.tabScrollerMenu.getPageSize();
			
			
			if (totalItems > pageSize)  {
				var numSubMenus = Math.floor(totalItems / pageSize);
				var remainder   = totalItems % pageSize;
				
				// Loop through all of the items and create submenus in chunks of 10
				for (var i = 0 ; i < numSubMenus; i++) {
					var curPage = (i + 1) * pageSize;
					var menuItems = [];
					
					
					for (var x = 0; x < pageSize; x++) {				
						index = x + curPage - pageSize;
						var item = this.items.get(index);
						menuItems.push(this.autoGenMenuItem(item));
					}
					
					this.tabsMenu.add({
						text : this.tabScrollerMenu.getMenuPrefixText() + ' '  + (curPage - pageSize + 1) + ' - ' + curPage,
						menu : menuItems
					});
					
				}
				// remaining items
				if (remainder > 0) {
					var start = numSubMenus * pageSize;
					menuItems = [];
					for (var i = start ; i < totalItems; i ++ ) {					
						var item = this.items.get(i);
						menuItems.push(this.autoGenMenuItem(item));
					}
					
					
					this.tabsMenu.add({
						text : this.tabScrollerMenu.menuPrefixText  + ' ' + (start + 1) + ' - ' + (start + menuItems.length),
						menu : menuItems
					});
					

				}
			}
			else {
				this.items.each(function(item) {
					if (item.id != curActive.id && ! item.hidden) {
						menuItems.push(this.autoGenMenuItem(item));
					}
				}, this);
			}	
		},
		// private
		autoGenMenuItem : function(item) {
			var maxText = this.tabScrollerMenu.getMaxText();
			var text    = Ext.util.Format.ellipsis(item.title, maxText);
			
			return {
				text      : text,
				handler   : this.showTabFromMenu,
				scope     : this,
				disabled  : item.disabled,
				tabToShow : item,
				iconCls   : item.iconCls
			}
		
		},
		// private
		showTabFromMenu : function(menuItem) {
			this.setActiveTab(menuItem.tabToShow);
		}	
	}	
});