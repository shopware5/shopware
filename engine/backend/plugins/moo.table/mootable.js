/**
* @file mootable.js
* @author Mark Fabrizio Jr.
* @date January 24, 2007
* 
* MooTable class takes an existing table as an argument and turns it into a cooler table.
* MooTables allow headings to be resized as well as sorted.
*/
Element.extend({ setHTML:function(html){ var tagName = this.getTag(); if(window.ActiveXObject && tagName.test(/thead|tbody|tr|td/)){ /*- for mootools v1 if(window.ie && tagName.test(/thead|tbody|tr|td/)){ */ var div = new Element('div'), depth; switch (tagName) { case 'thead': case 'tbody': div.innerHTML = '<table><tbody>'+html+'</tbody></table>';depth = 2;break; case 'tr': div.innerHTML = '<table><tbody><tr>'+ html+'</tr></tbody></table>';depth = 3;break; case 'td': div.innerHTML = '<table><tbody><tr><td>'+html+'</td></tr></tbody></table>';depth = 4; } $A(this.childNodes).each( function(node){this.removeChild(node)}, this); for(var i=0; i< depth; i++) div = div.firstChild; $A(div.childNodes).each( function(node){this.adopt(node)}, this); }else{ this.innerHTML = html; } return this; } });
var MooTable = new Class({
	
	initialize: function( el, options ){
		this.element = $(el);
		
		this.options = $extend( 
			{ 
				height: '100px',
				fixheight: '1.5em',
				columnheight: '',
				resizable: true,
				sortable: true,
				useloading: true,
				position: 'inside',
				section: 100,
				delay: 10,
				fade: true,
				headers: false,
				data: false,
				debug: false
			} , options || {} );
			
		/* set up our reference arrays */
		this.headers = []; 
		this.rows = [];
		this.fade = this.options.fade ? (window.ie6 ? '' : '<div class="fade"></div>') : '';
		this.loading = true;
		/* initalize variables */
		this.sortasc=true;
		this.sortby=false;
		this.sortby2=false;
		
		if( this.options.debug ){
			this.debug = {};
			console.log('debug: on');
			this.addEvent( 'buildStart', function(){
				this.debug.startTime = new Date().getTime();	
			});
			this.addEvent( 'buildFinish', function(){
				console.log( 'build: '+ ( (new Date().getTime() - this.debug.startTime ) / 1000 ) + ' seconds' );
				
			});
			this.addEvent( 'sortStart', function(){
				this.debug.sortStart = new Date().getTime();
			});
			this.addEvent( 'sortFinish', function(){
				console.log( 'sort: '+ ( (new Date().getTime() - this.debug.sortStart ) / 1000 ) + ' seconds' );
			});
		}
		if( this.options.useloading ){
			this.addEvent( 'loadingStart', function(){
				this.tbody.setStyle('overflow', 'hidden');
				this.tbodyloading.setStyle('display', 'block');
			});
			
			this.addEvent( 'loadingFinish', function(){
				this.tbody.setStyle('overflow', 'auto');
				this.tbodyloading.setStyle('display', 'none');	
			});
		}
		/* create the table */
		this._makeTable(this.element);
		
		this.div.setStyle('height', this.options.height );
		this._manageHeight();
		this.tbody.addEvent('scroll', function(event){
			this.thead_tr.setStyle( 'left', '-'+this.tbody.scrollLeft+'px' );
			return true;
		}.bind(this));
		this._initDisplayOptions();
	},
	
	_manageHeight: function(){
		var offset = this.options.resizable ? 8 : 1;
		this.tbody.setStyle('height', (this.div.getSize().y - this.thead.getSize().y - offset ) + 'px' );
		if( this.options.useloading ){
			this.tbodyloading.setStyle('height', (this.div.getSize().y - this.thead.getSize().y - offset)  + 'px' );
		}
		this.tbody.setStyle('top', this.thead.getSize().y + 'px' );
		
	},
	_rememberCookies: function(){
		this.headers.each( function( header ){
			var width = this._getWidthCookie( header.element )
			if( width ){
				header.element.setStyle('width', width );
				this._changeColumnWidth( header.element );
			}
		}, this );
	},
	
	_makeTable: function(el){
		this._fireEvent('buildStart');
		if( !el ){
			return;
		}
		this._createTableFramework();
		if( el.getTag() == 'table'){
			this._fireEvent('loadingStart');
			this._makeTableFromTable( el );
			return;
		}
		this.div.inject( el, this.options.position );
		this._build();
	},
	
	_makeTableFromTable: function(t,count){
		
	/*	var rows = $type(t) == 'array' ? t : t.getElements('tr');
		if( !$chk(count) ) count = 0;
		var section=0;
		while( count < rows.length && section < this.options.section){
			var tr = rows[count];
			if( count == 0 ){
				t.setStyle('display', 'none');
				this.div.injectBefore(t);
				if(t.getElement('tfoot')) t.getElement('tfoot').remove();
				tr.getElementsBySelector('th,td').each( function( th ){
					value = th.innerHTML;
					this._addHeader(value);
				}, this);
				this._setHeaderWidth();
			}
			else if( count > 0 ){
				var values = [];
				tr.getElements('td').each( function( td ){ 
					values.push( td.innerHTML );
					
				}, this);
				this.addRow( values );
				if( count == 1){
					this._setColumnWidths();
				}
			}
			count++;
			section++;
		}
		if( count < rows.length ){
			this.loading = true;
			this._makeTableFromTable.delay(this.options.delay, this, [rows,count] );
		}
		else{
			this.loading = false;
			this._setWidths();
			this._fireEvent('buildFinish');
			this._fireEvent('loadingFinish');
		}*/
	},
	
	_build: function(){
		if( this.options.headers && $type(this.options.headers) == 'array'){
			this.options.headers.each( function( h ){
				switch( $type( h ) ){
					case 'string':
						this._addHeader( h.trim()=='' ? '&nbsp;' : h );
						break;
					
					case 'object':
						this._addHeader( h.text || '&nbsp;', h );
						break;
						
					default:
						break;
				}
			},this ); 
		}
		/* do a little cleanup to keep this object reasonable */
		this.options.headers = null;
		if( this.options.data && $type( this.options.data ) == 'array' ){
			this._loadData( this.options.data );
		}
	},
	
	loadData: function( data, append ){
		if( !$chk(append) ){ append = true; }
		if( !append ){
			this._emptyData();
		}
		this._loadData( data );
	},
	
	_emptyData: function(){
		this.rows.each( function(row){
			row.element.remove();
		});
		this.rows = [];
			
	},
	
	_loadData: function( data, index ){
		if( !$chk(index) ) index = 0;
		var section=0;
		if( index == 0 ){
			this._fireEvent( 'loadingStart' );
		}
		for( index = index; index < data.length && section < this.options.section; index++){
			// load data
			var d = data[index];
			switch( $type( d ) ){
				case 'array':
				case 'object':
					this.addRow( d );
					break;
				default:
					break;
			}
			section++;
		}
		if( index < data.length ){
			this._setColumnWidths.delay( 20, this );
			this.loading = true;
			this._loadData.delay(this.options.delay, this, [data,index] )
		}
		else{
			this._setColumnWidths();
			this._fireEvent('loadingFinish');
			this._fireEvent('buildFinish');
		}
			
	},
	
	_createTableFramework: function(){
		this.div = new Element('div').addClass('mootable_container');
		this.mootable = new Element('div').addClass( 'mootable' ).injectInside( this.div );
		this.thead = new Element('div').addClass('thead').injectInside( this.mootable );
		this.thead_tr = new Element('div').addClass('tr').injectInside( this.thead );
		this.tbody = new Element('div').addClass('tbody').injectAfter( this.thead );
		this.table = new Element('table').setProperties({cellpadding: '0', cellspacing: '0', border: '0'}).injectInside(this.tbody);
		this.tablebody = new Element('tbody').injectInside( this.table );
		if( this.options.useloading ){
			this.tbodyloading = new Element('div').addClass('loading').injectInside( this.tbody );
			this.tbodyloading.setStyle('opacity', '.84');
		}
		if( this.options.resizable ){
			/*this.resizehandle = new Element('div').addClass('resizehandle').injectInside(this.div);
			new Drag.Base( this.div, {
				handle: this.resizehandle,
				modifiers: {y: 'height'},
				onComplete: function(){
					this._manageHeight();
				}.bind(this)
			});*/
		}
	},
	
	
	_addHeader: function( value, opts ){
		var options = $extend({
			fixedWidth: false,
			defaultWidth: '100px',
			sortable: true,
			numeric: false,
			date2: false,
			date: false,
			key: null,
			fade: true
		}, opts || {} ); 
		var cell = new Element('div').injectInside( this.thead_tr ).addClass('th');
		new Element('div').addClass('cell').setHTML( value ).injectInside( cell );
		var h = {
			element: cell,
			value: value,
			options: options
		};
		h.element.col = this.headers.length;
		this.headers.push( h );
		var width = this._getWidthCookie( h.element );
		if( width && !h.options.fixedWidth ){
			h.element.setStyle('width', width );
			//this._changeColumnWidth( h.element );
		}else{
			h.element.setStyle('width', h.options.defaultWidth );
		}
		
		h.width = h.element.getStyle('width');
		if( this.options.sortable && h.options.sortable ){
			h.element.addClass('sortable');
			h.element.addEvent('mouseup', function(ev){
				this.sort( h.element.col, h.options);
			}.pass(h.element, this));
		}
		
		if( !h.options.fixedWidth ){
			var handle = new Element('div').addClass('resize').injectInside( h.element );
			handle.setHTML('&nbsp;');
			/*var resizer = new Drag.Base(h.element, {
				handle: handle,
				modifiers:{x: 'width'},
				onComplete: function(){
					if( h.element.getSize().size.x < 10 ) {
						h.element.setStyle('width', '10px');
						this._setHeaderWidth();
					}
					this._setWidthCookie( h.element );
					this._setColumnWidths();
					this.thead.removeClass('dragging');
					h.element.removeClass('dragging');
				}.bind(this),
				
				onStart: function(ele){
					if( this.options.sortable) this.dragging = true;
					this.thead.addClass('dragging');
					ele.addClass('dragging');
				}.bind(this),
				
				onDrag: function(ele){
					this._setHeaderWidth();
				}.bind(this)
			} );*/
			// best fit
			
			handle.addEvent('dblclick', this.bestFit.pass( h.element.col,this) ); 
			
		}
		h.element.addEvent('mouseover', function(){
			this.addClass('mouseover');
		});
		h.element.addEvent('mouseout', function(){
			this.removeClass('mouseover');
		});
	},
	
	_createRow: function( data ){
		var row = {};
		row.element = new Element( 'tr' );
		row.cols = [];
		i=0;
		this._fireEvent( 'beforeRow', data );
		switch( $type( data ) ){
			case 'array':
				for(var i=0; i<this.headers.length; i++ ){
					var cell = this._createCell( data[i] );
					cell.element.addClass('c'+i).injectInside(row.element);
					cell.element.setStyle('height','30px');
					row.cols.push(cell);
				}
				break;
			case 'object':
				row.data = data;
				for(var i=0; i<this.headers.length; i++ ){
					header = this.headers[i];
					var text = header.options.key ? data[header.options.key] : '&nbsp;';
					var cell = this._createCell( text, header.options.fade );
					cell.element.addClass('c'+i).injectInside(row.element);
					cell.element.setStyle('height','30px');
					row.cols.push(cell);
				}
				break;
				
			default:
				// bad object
				break;
		}
		
		this._fireEvent( 'afterRow', [data, row] );
		return row;	
	},
	
	addRow: function( data ){
		var row = this._createRow( data );
		row.element.injectInside(this.tablebody);
		row.element.addClass( this.rows.length % 2 == 0 ? 'even' : 'odd' );
		this.rows.push( row );
	},
	
	_createCell: function( value, fade ){
		if( !$chk(fade) ){ fade = true; }
		var cell = {};
		cell.value = value;
		cell.element = new Element('td'); 
		try {
			if (value.match(/img/)){
			cell.element.setHTML(value);
			}else {
			
			
				cell.element.setHTML('<div class="cell" style="height:'+this.options.fixheight+'">'+( fade ? this.fade : '' )+'<span>'+value+'</span>&nbsp;</div>');
			}
		}catch (e) {
			cell.element.setHTML('<div class="cell" style="height:'+this.options.fixheight+'">'+( fade ? this.fade : '' )+'<span>'+value+'</span>&nbsp;</div>');
		}
		
		
		
		return cell;
	},
	
	_setColumnWidths: function(){
		this._setWidths();
		if( this.rows.length > 0 ){
			for(i=0;i<this.headers.length;i++){
				var w = this.headers[i].element.getStyle('width');
				w = window.ie ? (w.replace(/px/,"") - 2)+'px' : w;
				//this.rows[0].cols[i].element.setProperty('width', w);
				this.rows[0].cols[i].element.setStyle('width', w);
				//this.rows[0].cols[i].element.setStyle('height', 14);
				
			}
		}
		this._setWidths();
	},
	
	_setHeaderWidth: function(){
		var width=0;
		this.headers.each(function(h){
			width += h.element.getSize().x;	
		});
		this.thead_tr.setStyle('width', width+'px');
		this.tablewidth = width;
	},
	
	_setWidths: function(){
		this._setHeaderWidth();
		var width = this.thead_tr.getSize().x;
		this.table.setStyle( 'width', this.thead_tr.getStyle('width'));
		this.table.setProperty( 'width', this.thead_tr.getStyle('width'));
		this.tbody.fireEvent('scroll');
	},
	
	_copyProperties: function(from,to){
		//to.setProperty( 'class', from.getProperty('class') || '' );
		//to.setProperty( 'style', from.getProperty('style') || '' );
	},
	_initDisplayOptions: function(){
		this.displayOptions = new Element('div').addClass('mootable_options');
		this.form = new Element('form').injectInside( this.displayOptions );
		var i=0;
		this.headers.each( function( header ){
			var id = 'mootable_h'+i;
			var checkbox = new Element('input').setProperty('type','checkbox').setProperty('id',id).setProperty('name',id).injectInside(this.form);
			checkbox.setProperty('checked', 'true');
			checkbox.addEvent('click', this.toggleColumn.pass(i,this) );
			var label = new Element('label').setProperty('for',id).setProperty('htmlFor',id).setHTML(header.value).injectInside(this.form);
			i++;
			if( i < this.headers.length ){
				new Element('br').injectAfter(label);
			}
		}, this);
		this.displayOptionsTrigger = new Element('div').addClass('displayTrigger').injectInside( this.thead );
		this.displayOptionsTrigger.addEvent('click', this._toggleDisplayOptions.bind(this) );
		this.displayOptions.addClass('displayOptions').injectAfter( this.displayOptionsTrigger );
	},
	toggleColumn: function( col ){
		var checked = this.form['mootable_h'+col].checked;
		this.rows.each( function(row){
			row.cols[col].element.setStyle('display', checked ? '' : 'none');	
		});
		this.headers[col].element.setStyle('display', checked ? '' : 'none');
		this._setHeaderWidth();
		this._setWidths();
	},
	_toggleDisplayOptions: function(ev){
		if( this.displayOptions.getStyle('display') == 'none' ){
			this.displayOptions.setStyle('display', 'block');
			document.addEvent('mousemove', this._monitorDisplayOptions.bind(this) );
		}
		else{
			this.displayOptions.setStyle('display', 'none');
			document.removeEvent( 'mousemove', this._monitorDisplayOptions );
		}
	},
	_monitorDisplayOptions: function(ev){
		var e = new Event( ev );
		var pos = this.displayOptions.getPosition();
		if( e.page.x < pos.left || e.page.x > (pos.left + pos.width) ){
			this.displayOptions.setStyle('display', 'none');
			document.removeEvent( 'mousemove', this._monitorDisplayOptions );
		}
		else if( e.page.y < pos.top || e.page.y > (pos.top + pos.height) ){
			this.displayOptions.setStyle('display', 'none');
			document.removeEvent( 'mousemove', this._monitorDisplayOptions );
		}
	},
	_zebra: function(){
		var c = 0;
		this.rows.each( function(row) {
				row.element.addClass( c%2 == 0 ? 'odd' : 'even' );
				row.element.removeClass( c%2 == 1 ? 'odd' : 'even' );
				c++;
		});
	},
	_setWidthCookie: function( ele ){
		//Cookie.set('mootable_h_'+this.headers[ele.col].value , ele.getStyle('width') );
	},
	_getWidthCookie: function( ele ){
		//return Cookie.get('mootable_h_'+this.headers[ele.col].value);
	},
	sort: function( col , option){
		this._fireEvent('sortStart');
		if( this.rows.length == 0 ){
			return;
		}
		this.rows[0].cols.each( function( col ){
			col.element.setProperty('width', '');
			col.element.setStyle('width', 'auto' );
		} );
		if( this.dragging ){
			this.dragging = false;
			return;
		}
		if( $chk(this.sortby) ){
			this.headers[this.sortby].element.removeClass( 'sorted_'+ (this.sortasc ? 'asc' : 'desc' ) );
		}
		if( $chk(this.sortby) && this.sortby == col ){
			this.sortasc = !this.sortasc;
		}
		else if( $chk(this.sortby) ){
			this.sortby2 = this.sortby;
			this.sortasc = true;
		}
		this.sortby = col;
		
		this.numeric = option.numeric;
		this.date = option.date;
		this.date2 = option.date2;
		//console.log(this.numeric);
		this.headers[this.sortby].element.addClass( 'sorted_'+ (this.sortasc ? 'asc' : 'desc' ) );
		this.rows.sort( this.rowCompare.bind(this) );
		this.rows.each( function( item ){
			item.element.remove();
		});
		i=0;
		this.rows.each( function( item ){
			item.element.addClass( i%2 == 0 ? 'even' : 'odd' );
			item.element.removeClass( i%2 == 0 ? 'odd' : 'even' );
			item.element.injectInside(this.tablebody);
			i++;
		}, this );
		this._setColumnWidths();
		this._setWidths();
		this._fireEvent('sortFinish');
	},
	rowCompare: function( r1, r2 ){
	
		if(this.numeric)
		{
			a = r1.cols[this.sortby].value.toFloat();
			b = r2.cols[this.sortby].value.toFloat();
		}
		else if (this.date){
			var aa = r1.cols[this.sortby].value.split(".");
			var bb = r2.cols[this.sortby].value.split(".");
			a = (aa[2]+aa[1]+aa[0]).toInt();
			b = (bb[2]+bb[1]+bb[0]).toInt();
		}
		else if (this.date2){
			var aa = r1.cols[this.sortby].value.split(" ");
			var aaa = aa[0].split(".")
			var aaaa = aa[1].split(":")
			var bb = r2.cols[this.sortby].value.split(" ");
			var bbb = bb[0].split(".")
			var bbbb = bb[1].split(":")
			a = (aaa[2]+aaa[1]+aaa[0]+aaaa[1]+aaaa[0]).toInt();
			b = (bbb[2]+bbb[1]+bbb[0]+bbbb[1]+bbbb[0]).toInt();
		}
		else
		{
			a = r1.cols[this.sortby].value;
			b = r2.cols[this.sortby].value;
			
			a = a.replace(/<\/a>/g,"");
			a = a.replace(/<a (.*)>/g,"");
			b = b.replace(/<\/a>/g,"");
			b = b.replace(/<a (.*)>/g,"");
		//	console.log(a);
		}
		if( a > b ){
			return this.sortasc ? 1 : -1;
		}
		if( a < b ){
			return this.sortasc ? -1 : 1;
		}
		if( this.sortby2 ){
			a = r1.cols[this.sortby2].value;
			b = r2.cols[this.sortby2].value;
			if( a > b ){
				return this.sortasc ? 1 : -1;
			}
			if( a < b ){
				return this.sortasc ? -1 : 1;
			}
		}
		return 0;
	},
	bestFit: function(col){
		var max = 0;
		this.table.getElements('td.c'+col+' span').each( function( el ){
			s = el.getSize().x;
			if( s > max ) max = s;
		});                          
		this.headers[col].element.setStyle('width', (max+(this.headers[col].options.fade && this.options.fade ? 5 : 0)) + 'px' );
		this._setWidthCookie( this.headers[col].element );
		this._setHeaderWidth();
		this._setColumnWidths( this.headers[col] );
	},
	
	addEvent: function(type, fn){
		this.events = this.events || {};
		this.events[type] = this.events[type] || {'keys': []};
		
			this.events[type].keys.push(fn);
		
		return this;
	},
	
	_fireEvent: function(type,args){ 
		
		
		if (this.events && this.events[type]){
		
			this.events[type].keys.each(function(fn){
				if(fn.apply) {
					fn.apply(this, args);
				} else {
					fn.bind(this, args)();
				}
			}, this);
		}
	}	
});

