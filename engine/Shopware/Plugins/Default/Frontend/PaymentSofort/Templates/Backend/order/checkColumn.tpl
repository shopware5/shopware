<script type="text/javascript">
<!--
Ext.ns('Ext.ux.grid');

Ext.ux.grid.CheckColumn = function(config){
	this.addEvents({
		click: true
	});
	Ext.ux.grid.CheckColumn.superclass.constructor.call(this);
	
	Ext.apply(this, config, {
		id : null,
		paymentMethod: null,
		paymentStatus: null,
		checked : false,
		init : function(grid){
			this.grid = grid;
			this.grid.on('render', function(){
				var view = this.grid.getView();
				view.mainBody.on('mousedown', this.onMouseDown, this);
			}, this);
		},
		onMouseDown : function(e, t){
			e.preventDefault();
			var index = this.grid.getView().findRowIndex(t);
			this.checked = this.grid.store.getAt(index).data.delete;
			
			if(t.className == 'x-grid3-check-col x-grid3-cc-checkColumn') {
				t.className = 'x-grid3-check-col-on'+' x-grid3-cc-'+this.id;
				this.grid.store.getAt(index).data.delete = !this.checked;
			} else if(t.className == 'x-grid3-check-col-on x-grid3-cc-checkColumn') {
				t.className = 'x-grid3-check-col'+' x-grid3-cc-'+this.id;
				this.grid.store.getAt(index).data.delete = !this.checked;
			}
			
			e.stopEvent();
		},
		renderer : function(v, p, record){
			p.css += ' x-grid3-check-col-td';
			var checkbox = '<div class="x-grid3-check-col'+(this.checked?'-on':'')+' x-grid3-cc-'+this.id+'"></div>';
			if(this.paymentMethod == 'sofortrechnung_multipay' && this.paymentStatus != 'refunded' && this.paymentStatus != 'canceled') {
				return checkbox;
			}
			else return '<div class="">-</div>';
		}
	});
	
	if(!this.id){
		this.id = Ext.id();
	}
	this.renderer = this.renderer.createDelegate(this);
};

// register ptype
Ext.preg('checkcolumn', Ext.ux.grid.CheckColumn);

// backwards compat
Ext.grid.CheckColumn = Ext.ux.grid.CheckColumn;

Ext.extend(Ext.grid.CheckColumn, Ext.util.Observable);
-->
</script>