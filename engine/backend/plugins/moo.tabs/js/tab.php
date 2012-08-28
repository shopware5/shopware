<?php
define('sAuthFile', 'sSUMMARY');
define('sConfigPath',"../../../../");
include("../../../backend/php/check.php");

$result = new checkLogin();

$result = $result->checkUser();
if ($result!="SUCCESS"){
	die();
}

?>
<?php if (1!=1) { ?> <script> <?php } ?>
var TabGroups = {
	tabGroups: [],
	loadingFx: null,
	themes: ['default', 'blue', 'simple'],
	msgs: {
		loading:	'Please wait while loading...',
		errorAjax:	'Content could not be updated - please try again!'
	},

	_register: function(tabGroup){
		if (!this.loading){
			// Seperater Ladescreen für Tabs
			//this.loading = new Element('div').setProperties({'id': 'loading', 'class': 'loading'}).setStyles({'position': 'relative'}).injectInside($('loader'));
			//new Element('div').setHTML(this.msgs.loading).injectInside(this.loading);
			
			//this.loadingFx = new Fx.Opacity(this.loading, {duration: 750}).set(0);
		}
		this.tabGroups.push(tabGroup);
	},
	_position: function(el){
		this.loading.setStyles({'width': el.offsetWidth + 'px','position': 'relative'});
		if (this.loadingFx) this.loadingFx.goTo(0.75);
		else this.loading.setStyle('display', '');
	},

	toggleGroups: function(){
		if (!arguments) return false;
	}
};

/*------------------------------------------------------------------------------
| Tab group
------------------------------------------------------------------------------*/
TabGroup = new Class({

	initialize: function(el, options, parentWindow) {
		this.options = $extend({
		  active:		2,
		  width:		300,
		  height:		300,
		  sortable:		false,
		  scrollbar:	'auto',	// auto, hidden, scroll, 
		  label:		'Stammdaten', // default label if panel heading is not set
		  effects:   	true,	// true = use animation effects
		  position:		'top',	// position of tab bar; values: 'top' or 'bottom'
		  orientation:	'left', // orientation of tab bar; values: 'left' or 'right'
		  order:		'asc',	// order of tabs; values: 'asc' or 'desc'
		  theme:		'simple',
		  show:			0,
		  hide:			0
		}, options || {});

		this.tabs				= [];
		this.panels				= [];
		this.tabEvents			= [];
		this.count				= 1;
		this.currentTheme		= this.options.theme;
		this.currentPosition	= this.options.position;
		this.currentOrientation	= this.options.orientation;
		this.currentOrder		= this.options.order;
		/*
		Erweiterung, Speicherung des Fenster-Objekts
		*/
		this.parentWindow = parentWindow; 
		
		this.element   = $(el);
		this.elementId = this.element.getProperty('id');

		// register at tabgroups
		TabGroups._register(this);
		this.loading = TabGroups.loading;

		this.panelContainer = this.element.getFirst();
		this.size('100%', '100%', true);
		this.scrollbar(this.options.scrollbar);

		this.tabContainer = new Element('div').addClass('tabContainer');
		new Element('ul').setProperty('id', this.elementId + '_t').injectInside(this.tabContainer);
		this.tabContainerList = this.tabContainer.getFirst();

		this.setTheme(this.currentTheme);

		// set tab container position
		this._position(this.options.position);

		// register tab for each panel
		this.panels = this.panelContainer.getElements('.panel');
		this.panels.each(function(el){
			this._register(el);
		}, this);

		// set tab orientation and order
		if (this.currentOrientation == 'right' || this.currentOrientation == 'left' && this.currentOrder == 'desc'){
			this.currentOrder = (this.currentOrder	== 'desc') ? 'asc' : this.currentOrder;	// swap order
			this.initOrder = true;
			this._orientation(this.currentOrientation);
		}

		// set sortable
		if (this.options.sortable) this._sortable();

		// activate tab and panel
		this.currentTab = this.tabs[this._posToIndex(this.options.active)];
		this.show(this.options.active);
	},

	// add tab
	_register: function(panel, pos){
		// setup tab
		var tabId = this.elementId + '_t' + this.count;
	  	var tab = new Element('li').setProperty('id', tabId);
		new Element('span').injectInside(tab);

		// set panel attributes
		tab.panel = panel.setProperty('id', this.elementId + '_p' + this.count).setStyle('display', 'none'); // assign panel to tab;

		// add tab to DOM and this.tabs[] array
		if (pos){
			if (this.currentOrientation == 'left'){
				if (pos >= this.tabs.length) tab.injectInside(this.tabContainerList);
				else tab.injectBefore(this.tabs[pos - 1]);
			} else {
				if (pos == 1) tab.injectInside(this.tabContainerList);
				else tab.injectBefore(this.tabs[pos - 2]);
			}
			this.tabs = this._rebuild(); // rebuild tabs array
		} else {
			this.tabs.push(tab.injectInside(this.tabContainerList));
			var pos = this.tabs.length;
		}
		// set tab label
		var label = panel.getProperty('label') ? panel.getProperty('label') : this.options.label + ' ' + this.count;
		
		// Dynamic fade in/out of buttons
		var show = panel.getProperty('show') != 'undefined' ? panel.getProperty('show') : null;
		if (show){
			tab.setProperty("show",show);
		}
		var hide = panel.getProperty('hide') != 'undefined' ? panel.getProperty('hide') : null;
		if (hide){
			tab.setProperty("hide",hide);
		}
		
		this.label(pos, label);
		if (panel.getProperty('active')=="0") tab.addClass('disabled');
	
		this.count++;

		// add event observers
		this.tabEvents[tabId] = this.tabEvent.bind(this, tab);
		tab.addEvent('click',     this.tabEvents[tabId]);
		tab.addEvent('mouseover', this.tabEvents[tabId]);
		tab.addEvent('mouseout',  this.tabEvents[tabId]);

		
 	},

	// remove observers
	_removeObservers: function(tab){
		var tabId = tab.getProperty('id');
		tab.removeEvent('click',     this.tabEvents[tabId]);
		tab.removeEvent('mouseover', this.tabEvents[tabId]);
		tab.removeEvent('mouseout',  this.tabEvents[tabId]);
	},

	// event handlers
	tabEvent: function(e, tab){
		try {
		if (tab.hasClass('disabled')){
			return false;
		}
		} catch(e){}
		switch(e.type){
			case 'click':
				this.show(this.tabs.indexOf(tab) + 1);
				break;
			case 'mouseover':
				if (tab != this.currentTab) tab.addClass('current');
				break;
			case 'mouseout':
				if (tab != this.currentTab) tab.removeClass('current');
				break;
			default:
				return false;
		};
	},

	// convert tab position to index
	_posToIndex: function(pos){
		pos--;
		var last = (this.tabs.length -1);
		return (pos < 0) ? 0 : (pos > last) ? last : pos;
	},

	// rebuild tab array
	_rebuild: function(){
		return (this.currentOrientation == 'right') ? $A(this.tabContainerList.childNodes).reverse() : $A(this.tabContainerList.childNodes);
	},

	// set tab position (top or bottom)
	_position: function(pos){
		if (pos == 'bottom'){
			this.tabContainer.injectInside(this.element);
			this.element.addClass('bottom');
		} else {
			this.tabContainer.injectBefore(this.panelContainer);
			this.element.removeClass('bottom');
		}
		this.currentPosition = pos;
	},

	// set tab orientation
	_orientation: function(orient){
		(orient == 'left') ? this.tabContainerList.removeClass('right') : this.tabContainerList.addClass('right');
		this.currentOrientation = orient;
		this._order();
	},

	// set tab order
	_order: function(){
		// set order on init
		if (this.initOrder){
			if (this.currentOrientation == 'left') this.tabs.reverse();
			this.tabs.each(function(el) {
				el.injectInside(this.tabContainerList);
			}, this);
			if (this.currentOrientation == 'right') this.tabs.reverse();
			this.initOrder = false;
		} else {
			 // rebuild tab array
			this.tabs.reverse();
			this.tabs.each(function(el){
				el.injectInside(this.tabContainerList);
			}, this);
			this.tabs = this._rebuild();
		}
	},

	// create panel HTML
	_createPanelHTML: function(label){
		panel = new Element('div').addClass('panel').injectInside(this.panelContainer);
		if (label) {
			new Element('h1').addClass('tabTitle').set('html',label).injectInside(panel);
		}
		return panel;
	},

	
	// add new tab; element, options: position, label
	add: function(el, options){
		var options = Object.extend({
		  position: 0,
		  label: null
		}, options || {});
		var tab = $(el);
		
		if (tab && tab.parentNode == this.panelContainer) tab.addClass('panel');
		else tab = this._createPanelHTML(options.label);
		

		var pos = (options.position == 0) ? (this.tabs.length + 1) : options.position;
		pos = (pos < 1) ? 1 : (pos > this.tabs.length + 1) ? this.tabs.length + 1 : pos;

		this.panels.push(tab);
		this._register(tab, pos);
		if (this.options.sortable) this._sortable();
	},

	// remove tab(s)
	remove: function(){
		if (!arguments) return false;
		$A(arguments).each( function(pos){
			var tab = this.tabs[this._posToIndex(pos)];
			// current active tab cannot be removed
			if (this.currentTab != tab){
				this._removeObservers(tab);
				tab.remove();
			}
		}, this);

		this.tabs = this._rebuild();

		if (this.options.sortable) this._sortable(); // rebuild sortable
	},

	
	// activate tab and show the selected panel
	show: function(pos) {
		var tab = this.tabs[this._posToIndex(pos)];

		if (tab.hasClass('disabled')) return false;

		// set current tab inactive
		this.currentTab.removeClass('current').panel.setStyle('display', 'none');

		//activate new tab
		tab.setStyle('display', '').addClass('current');

		// Trying to find buttons to fade-in
		var show = tab.getProperty('show') ? tab.getProperty('show') : null;
		if (show){
			//console.log("Show found"+show)
			try {
				sWindows.focus.clone.getElement('#'+show).setStyle('display','block');
			} catch(e){}
			
			
		}
		// Trying to find buttons to fade-out
		var hide = tab.getProperty('hide') ? tab.getProperty('hide') : null;
		if (hide){
			//console.log("hide found"+hide)
			sWindows.focus.clone.getElement('#'+hide).setStyle('display','none');
		}
		// Load content into window
		this.parentWindow.focus();
		//console.log("TEST");
		
		if (tab.panel.getChildren()[0].hasClass('contentFrame')){
			
			//console.log("TEST1");
			// Create new iframe element
			var iframe = new Element('iframe');
			iframe.setProperty('src',tab.panel.getChildren()[0].getProperty('src'));
			iframe.setProperty('id',tab.panel.getChildren()[0].getProperty('id'));
			iframe.setProperty('class',tab.panel.getChildren()[0].getProperty('class'));
			iframe.setProperty('style',tab.panel.getChildren()[0].getProperty('style'));
			iframe.setProperty('border',tab.panel.getChildren()[0].getProperty('border'));
			iframe.setProperty('width',tab.panel.getChildren()[0].getProperty('width'));
			iframe.setProperty('height',tab.panel.getChildren()[0].getProperty('height'));
			// Building parent-div
			var contentDiv = new Element('div');
			contentDiv.setProperty('id','content');
			contentDiv.setProperty('class','content');
			// Replace original one
			iframe.injectInside(contentDiv);
	
			
			sWindows.focus.options.help = tab.panel.getProperty('help');
			$(contentDiv).replaces(sWindows.focus.clone.getElement('.content'));
			//.replaces(contentDiv);
		}else {
			//console.log("TEST2");
			sWindows.focus.setContent(tab.panel.innerHTML);
		}
		
		sWindows.focus.refresh();
		// ---
		
		
		this.currentTab = tab;
	},

	// enables tab(s)
	enable: function(){
		if (!arguments) return false;
		$A(arguments).each(function(pos){
			this.tabs[this._posToIndex(pos)].removeClass('disabled');
		}, this);
	},

	// disable tab(s)
	disable: function(){
		if (!arguments) return false;
		$A(arguments).each(function(pos){
			var tab = this.tabs[this._posToIndex(pos)];
			// currently active tab cannot be disabled
			if (!tab.hasClass('current')) tab.addClass('disabled');
		}.bind(this));
	},

	// toggle tab(s)
	toggle: function(){
		if (!arguments) return false;
		$A(arguments).each(function(pos){
			var tab = this.tabs[this._posToIndex(pos)];
			tab.setStyle('display', (tab.getStyle('display') == 'none') ? '' : 'none');
		}.bind(this));
		if (this.currentTab.getStyle('display') == 'none') this.next(); // activate next tab in case current tab gets disabled
	},

	// set tab content
	content: function(pos, str) {
		this.tabs[this._posToIndex(pos)].panel.set('html',str);
	},

	// set width and height of tab container
	size: function(width, height, noFx){
		if (!noFx && this.options.effects){
			this.element.effect('width').custom(this.element.offsetWidth.toInt(), width);
			this.panelContainer.effect('height').custom(this.panelContainer.offsetHeight.toInt(), height);
		} else {
			this.element.setStyle('width', width + 'px');
			this.panelContainer.setStyle('height', height + 'px');
		}
		this.options.width  = width;
		this.options.height = height;
	},
// set tab label
	label: function(pos, str){
		if (str) this.tabs[this._posToIndex(pos)].getFirst().set('html',str);
	},
	// set width of tab container
	width: function(set){
		this.size(parseFloat(set), this.panelContainer.offsetHeight);
	},

	// set height of tab container
	height: function(set){
		this.size(this.panelContainer.offsetWidth, parseFloat(set));
	},

	// boolean - return true, if tab is visible
	isVisible: function(pos){
		return (this.tabs[this._posToIndex(pos)].getStyle('display') != 'none');
	},

	// boolean - return true, if tab is enabled
	isEnabled: function(pos) {
		return (!this.tabs[this._posToIndex(pos)].hasClass('disabled'));
	},

	// boolean - return true, if tab is current tab
	isCurrent: function(pos){
		return (this.tabs[this._posToIndex(pos)] == this.currentTab) ? true : false;
	},

	// return the current tab
	getCurrent: function() {
		return this.tabs.indexOf(this.currentTab) + 1;
	},

	// set scrollbar of panel
	scrollbar: function(set) {
		this.panelContainer.setStyle('overflow', set ? set : this.options.scrollbar);
	},

	// set tab position
	position: function(set){
		if (!set) set = (this.currentPosition == 'bottom') ? 'top' : 'bottom';
		else set= (set == 'bottom' || set == 'top') ? set : this.options.position;

		if (set == this.currentPosition) return false; // no change
		this._position(set); // change position
	},

	// set tab orientation
	orientation: function(set){
		if (!set) set = (this.currentOrientation == 'right') ? 'left' : 'right';
		else set = (set == 'right' || set == 'left') ? set : this.options.orientation;

		if (set == this.currentOrientation) return false; // orientation NOT changed
		this._orientation(set, (this.currentOrder == 'asc') ? 'desc' : 'asc'); // change orientation
	},

	// set tab order
	order: function(set) {
		if (!set) set = (this.currentOrder == 'desc') ? 'asc' : 'desc';
		else set = (set == 'asc' || set == 'desc') ? set : this.options.order;

		if (set == this.currentOrder) return false; // order NOT changed
		this.currentOrder = set;
		this._order(); // change order
	},

	// set theme
	setTheme: function(set){
		this.tabContainer.removeClass(this.currentTheme);
		if (TabGroups.themes.indexOf(set) > -1) this.currentTheme = set;
		else this.currentTheme = this.options.theme;

		this.tabContainer.addClass(this.currentTheme);
	},

	// returns current theme
	getTheme: function(){
		return this.currentTheme;
	}
});

<?php if (1!=1) { ?> </script> <?php } 


#echo $js;
?>