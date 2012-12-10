{literal}
<script type="text/javascript">
Ext.namespace("Ext.ux.grid.filter");
Ext.ux.grid.filter.Filter = function(config){
	Ext.apply(this, config);
		
	this.events = {
		/**
		 * @event activate
		 * Fires when a inactive filter becomes active
		 * @param {Ext.ux.grid.filter.Filter} this
		 */
		'activate': true,
		/**
		 * @event deactivate
		 * Fires when a active filter becomes inactive
		 * @param {Ext.ux.grid.filter.Filter} this
		 */
		'deactivate': true,
		/**
		 * @event update
		 * Fires when a filter configuration has changed
		 * @param {Ext.ux.grid.filter.Filter} this
		 */
		'update': true,
		/**
		 * @event serialize
		 * Fires after the serialization process. Use this to apply additional parameters to the serialized data.
		 * @param {Array/Object} data A map or collection of maps representing the current filter configuration.
		 * @param {Ext.ux.grid.filter.Filter} filter The filter being serialized.
		 **/
		'serialize': true
	};
	Ext.ux.grid.filter.Filter.superclass.constructor.call(this);
	
	this.menu = new Ext.menu.Menu();
	this.init();
	
	if(config && config.value){
		this.setValue(config.value);
		this.setActive(config.active !== false, true);
		delete config.value;
	}
};
Ext.extend(Ext.ux.grid.filter.Filter, Ext.util.Observable, {
	/**
	 * @cfg {Boolean} active
	 * Indicates the default status of the filter (defaults to false).
	 */
    /**
     * True if this filter is active. Read-only.
     * @type Boolean
     * @property
     */
	active: false,
	/**
	 * @cfg {String} dataIndex 
	 * The {@link Ext.data.Store} data index of the field this filter represents. The dataIndex does not actually
	 * have to exist in the store.
	 */
	dataIndex: null,
	/**
	 * The filter configuration menu that will be installed into the filter submenu of a column menu.
	 * @type Ext.menu.Menu
	 * @property
	 */
	menu: null,
	
	/**
	 * Initialize the filter and install required menu items.
	 */
	init: Ext.emptyFn,
	
	fireUpdate: function(){
		this.value = this.item.getValue();
		
		if(this.active)
			this.fireEvent("update", this);
			
		this.setActive(this.value.length > 0);
	},
	
	/**
	 * Returns true if the filter has enough configuration information to be activated.
	 * 
	 * @return {Boolean}
	 */
	isActivatable: function(){
		return true;
	},
	
	/**
	 * Sets the status of the filter and fires that appropriate events.
	 * 
	 * @param {Boolean} active        The new filter state.
	 * @param {Boolean} suppressEvent True to prevent events from being fired.
	 */
	setActive: function(active, suppressEvent){
		if(this.active != active){
			this.active = active;
			if(suppressEvent !== true)
				this.fireEvent(active ? 'activate' : 'deactivate', this);
		}
	},
	
	/**
	 * Get the value of the filter
	 * 
	 * @return {Object} The 'serialized' form of this filter
	 */
	getValue: Ext.emptyFn,
	
	/**
	 * Set the value of the filter.
	 * 
	 * @param {Object} data The value of the filter
	 */	
	setValue: Ext.emptyFn,
	
	/**
	 * Serialize the filter data for transmission to the server.
	 * 
	 * @return {Object/Array} An object or collection of objects containing key value pairs representing
	 * 	the current configuration of the filter.
	 */
	serialize: Ext.emptyFn,
	
	/**
	 * Validates the provided Ext.data.Record against the filters configuration.
	 * 
	 * @param {Ext.data.Record} record The record to validate
	 * 
	 * @return {Boolean} True if the record is valid with in the bounds of the filter, false otherwise.
	 */
	 validateRecord: function(){return true;}
});

Ext.ux.grid.filter.ListFilter = Ext.extend(Ext.ux.grid.filter.Filter, {
	labelField:  'text',
	loadingText: 'Loading...',
	loadOnShow:  true,
	value:       [],
	loaded:      false,
	phpMode:     false,
	
	init: function(){
		this.menu.add('<span class="loading-indicator">' + this.loadingText + '</span>');
		
		if(this.store){
			if(this.loadOnShow)
				this.menu.on('show', this.onMenuLoad, this);
			
		} else if(this.options){
			var options = [];
			for(var i=0, len=this.options.length; i<len; i++){
				var value = this.options[i];
				switch(Ext.type(value)){
					case 'array':  options.push(value); break;
					case 'object': options.push([value.id, value[this.labelField]]); break;
					case 'string': options.push([value, value]); break;
				}
			}
			
			this.store = new Ext.data.Store({
				reader: new Ext.data.ArrayReader({id: 0}, ['id', this.labelField])
			});
			this.options = options;
			
			this.menu.on('show', this.onMenuLoad, this);
		}
		this.store.on('load', this.onLoad, this);
		
		this.bindShowAdapter();
	},
	
	/**
	 * Lists will initially show a 'loading' item while the data is retrieved from the store. In some cases the
	 * loaded data will result in a list that goes off the screen to the right (as placement calculations were done
	 * with the loading item). This adaptor will allow show to be called with no arguments to show with the previous
	 * arguments and thusly recalculate the width and potentially hang the menu from the left.
	 * 
	 */
	bindShowAdapter: function(){
		var oShow    = this.menu.show;
		var lastArgs = null;
		this.menu.show = function(){
			if(arguments.length == 0){
				oShow.apply(this, lastArgs);
			} else {
				lastArgs = arguments;
				oShow.apply(this, arguments);
			}
		};
	},
	
	onMenuLoad: function(){
		if(!this.loaded){
			if(this.options)
				this.store.loadData(this.options);
			else
				this.store.load();
		}
	},
	
	onLoad: function(store, records){
		var visible = this.menu.isVisible();
		this.menu.hide(false);
		
		this.menu.removeAll();
		
		var gid = this.single ? Ext.id() : null;
		for(var i=0, len=records.length; i<len; i++){
			var item = new Ext.menu.CheckItem({
				text:    records[i].get(this.labelField), 
				group:   gid, 
				checked: this.value.indexOf(records[i].id) > -1,
				hideOnClick: false});
			
			item.itemId = records[i].id;
			item.on('checkchange', this.checkChange, this);
						
			this.menu.add(item);
		}
		
		this.setActive(this.isActivatable());
		this.loaded = true;
		
		if(visible)
			this.menu.show(); //Adaptor will re-invoke with previous arguments
	},
	
	checkChange: function(item, checked){
		var value = [];
		this.menu.items.each(function(item){
			if(item.checked)
				value.push(item.itemId);
		},this);
		this.value = value;
		
		this.setActive(this.isActivatable());
		this.fireEvent("update", this);
	},
	
	isActivatable: function(){
		return this.value.length > 0;
	},
	
	setValue: function(value){
		var value = this.value = [].concat(value);

		if(this.loaded)
			this.menu.items.each(function(item){
				item.setChecked(false, true);
				for(var i=0, len=value.length; i<len; i++)
					if(item.itemId == value[i]) 
						item.setChecked(true, true);
			}, this);
			
		this.fireEvent("update", this);
	},
	
	getValue: function(){
		return this.value;
	},
	
	serialize: function(){
    var args = {type: 'list', value: this.phpMode ? this.value.join(',') : this.value};
    this.fireEvent('serialize', args, this);
		return args;
	},
	
	validateRecord: function(record){
		return this.getValue().indexOf(record.get(this.dataIndex)) > -1;
	}
});
</script>
{/literal}