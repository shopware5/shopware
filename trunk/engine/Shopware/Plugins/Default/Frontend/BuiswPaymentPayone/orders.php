<?php

if (BUIWPAYMENTTOKEN !== "securedCall"){
echo "
	<html><title>Time-Out</title><head></head>
	<script language=\"javascript\">
	parent.location.reload();
	</script>
	<body>Bitte loggen Sie sich neu ein!</body></html>
	";
	die();
}

$result = mysql_query("
    SELECT id, description FROM s_core_paymentmeans ORDER BY position,description ASC
");
$order_status3 = array();
$basepath = $_GET['basepath'];
$htmlbase = str_replace('Shopware/', '', $basepath);


if ($result && mysql_num_rows($result)) {
    while($status = mysql_fetch_assoc($result)) {
        $order_status3[$status["id"]] = $status["description"];
        $checked                      = 'false';
        if ($status['description'] == 'PAYONE') {
            $checked             = 'true';
            $PAYONE_ID           = $status['id'];
            $zahlungs_methoden[] = '{boxLabel: "' . $status['description'] . '", name:"' . $status['id'] . '", checked:' . $checked . '}';
            // [$status["id"]] = $status["description"];
        }
    }
}
// echo join (',', $zahlungs_methoden);
?>
<html>
<head>
	<base href="<?php echo '/'.$htmlbase; ?>backend/modules/orderlist/">
  <title></title>

    <link href="/templates/_default/backend/_resources/resources/css/icon-set.css" type="text/css" rel="stylesheet">

    
<script type="text/javascript" src="http://cdn.shopware.de/assets/library/base.js"></script> 
<script type="text/javascript" src="http://cdn.shopware.de/assets/library/all.js"></script> 
<script type="text/javascript" src="http://cdn.shopware.de/assets/library/locale/de.js" charset="utf-8"></script>
<link rel="stylesheet" type="text/css" href="http://cdn.shopware.de/assets/library/resources/css/all.css" /> 
    
	<script type="text/javascript" src="<?php echo '/'.$htmlbase; ?>backend/modules/orderlist/RowExpander.js"></script>
	<script type="text/javascript" src="<?php echo '/'.$htmlbase; ?>vendor/ext/build/locale/ext-lang-de.js" charset="utf-8"></script>
	<script type="text/javascript" src="<?php echo '/'.$htmlbase; ?>backend/js/mootools.js"></script>

	<link href="../../../backend/css/icons.css" rel="stylesheet" type="text/css" />
 	<link href="../../../backend/css/icons4.css" rel="stylesheet" type="text/css" />
	<style type="text/css">
		a.money_plus { background: url(../../../Shopware/Plugins/Default/Frontend/BuiswPaymentPayone/images/preis_plus.png) no-repeat 0px 0px transparent;}
	  a.money_minus { background: url(../../../Shopware/Plugins/Default/Frontend/BuiswPaymentPayone/images/preis_minus.png) no-repeat 0px 0px transparent;}

	html, body {
        font:normal 12px verdana;
        margin:0;
        padding:0;
        border:0 none;
        overflow:hidden;
        height:100%;
    }
	p {
	    margin:5px;
	}
    .settings {
        background-image:url(../shared/icons/fam/folder_wrench.png);
    }
    .nav {
        background-image:url(../shared/icons/fam/folder_go.png);
    }

    .blue-row .x-grid3-cell-inner{
      color:blue;
    }
    .red-row .x-grid3-cell-inner{
      color:red;
    }
    .green-row .x-grid3-cell-inner{
      color:green;
    }

	a.ico {
		float:left;
		height:20px;
		margin:0 0 0 5px;
		padding:0;
		width:20px;
	}
	.x-form-label-left label {
		font-size:11px !important;
	}
	.x-hide-label .x-form-element {
		overflow:hidden !important;
	}
    </style>
	<script>
	function loadSkeleton(x,y, z){
		parent.loadSkeleton(x,y,z);
	}
	</script>
	<script type="text/javascript">
	var myExt = function(){
		var store;
		var storeDocuments;
		var storeid;
		var myTab;
		var grids = {};
		var window;
		var start;
	return {
	markAll: function(){
    	$$('.markedOrders').each(function(e){
    		e.checked = true;
    	});
    },
	doCombine: function (){
		var countDocuments = 0;
		var currentDocument = 0;
		var progress = 0;
		var queue = new Array();
		Ext.select('.markedOrders').each ( function (e){
			if (e.dom.checked){
				queue[countDocuments] = e.dom.value;
				countDocuments ++;
			}
		});
		if (countDocuments==0){
			alert("Keine Belege ausgew?hlt");
			return;
		}
		var query = queue.join(";");
		var docKind = Ext.getCmp('docKind').getValue();
		var url = "../../../backend/ajax/combinepdf.php?files="+query+"&typ="+docKind;
		parent.window.open(url,"Belegdruck","width=800,height=600");
	},
	doPDF: function (){
		var countDocuments = 0;
		var currentDocument = 0;
		var progress = 0;
		var queue = new Array();

		/*
		0 = Default
		1 = Check if document exists already
		*/
		var docMode = Ext.getCmp('docMode').getValue();

		/*
		0 = RG
		1 = LA
		2 = GS
		3 = ST
		*/
		var docKind = Ext.getCmp('docKind').getValue();

		/*
		-1 = Change nothing
		*/
		var docState = Ext.getCmp('docChangeState').getValue();
		var docPayment = Ext.getCmp('docChangePayment').getValue();
		Ext.select('.markedOrders').each ( function (e){
				if (e.dom.checked){
					queue[countDocuments] = e.dom.value;
					countDocuments ++;
				}
		});
		progress = function()
		{

			Ext.Ajax.request({
				 url: '../../../../backend/document/',
		  		 params: 	{date:'<?php echo date("d.m.Y") ?>',
					delivery_date: '<?php echo date("d.m.Y") ?>',
					id: queue[currentDocument],
					typ: docKind,
					forceTaxCheck:1,
					voucher: 0},
				method: 'POST',
				success: function ( result, request ) {
					if (docState && docState != -1){
						Ext.Ajax.request({
							 url: '../../../backend/ajax/changeOrderState.php',
					  		 params: 	{artID: queue[currentDocument],field: 'status',value: docState},
							 method: 'POST',
							 success: function ( result, request ) {

							 },
							 failure: function ( result, request) {

							 }
						});
					}
					if (docPayment && docPayment != -1){
						Ext.Ajax.request({
							 url: '../../../backend/ajax/changeOrderState.php',
					  		 params: 	{artID: queue[currentDocument],field: 'cleared',value: docPayment},
							 method: 'POST',
							 success: function ( result, request ) {

							 },
							 failure: function ( result, request) {

							 }
						});
					}
					currentDocument++;
					var progressbar = currentDocument / countDocuments;
					Ext.getCmp('install_progress').updateProgress(progressbar, "Beleg "+ currentDocument + " von " + countDocuments + " verarbeitet...");
					if (currentDocument < countDocuments)
					{
						progress();
					}else{
						if(countDocuments == "1")
						{
							Ext.getCmp('install_progress').updateProgress(1, "Beleg-Erstellung abgeschlossen (Ein Beleg erstellt)");
						}else{
							Ext.getCmp('install_progress').updateProgress(1, "Beleg-Erstellung abgeschlossen ("+countDocuments+" Belege erstellt)");
						}

					}

				},
				failure: function ( result, request) {

				}
			});
		};
		if (docMode==1){
			// Check if document already exists
				Ext.Ajax.request({
					url: '../../../backend/ajax/checkDocument.php',
			  		params: {id:  queue.join(';'),	typ: docKind},
					method: 'POST',
					success: function ( result, request ) {
						if (!result.responseText){
							alert("Nothing to do");
							return;
						}
						var test = JSON.decode(result.responseText);
						queue = test;

						countDocuments = queue.length;

						Ext.getCmp('install_progress').updateProgress(0.1, "Beleg-Erstellung l?uft...");


						progress();
						return;
					},
					failure: function ( result, request) {
						alert(result.responseText);
						return;
					}
				});
				return;
		}



		Ext.getCmp('install_progress').updateProgress(0.1, "Beleg-Erstellung l?uft...");


		progress();
	},
	showProcess: function (){
		myExt.window.show();
	},
	reload : function(){
    	store.load({params:{start:0,id:storeid, limit:25}});
    	storeDocuments.load({params:{start:0,id:storeid, limit:25}});
    },
    GetRandom : function ( min, max ) {
    	if( min > max ) {
    		return( -1 );
    	}
    	if( min == max ) {
    		return( min );
    	}
    	var r = parseInt( Math.random() * ( max+1 ) );
    	return( r + min <= max ? r + min : r );
    },
    filterByChar: function(key){
    	store.baseParams["search"] = key;
	    store.lastOptions.params["start"] = 0;
	    store.reload();
    },
    filterGroup: function(key){
    	store.baseParams["group"] = key;
	    store.lastOptions.params["start"] = 0;
	    store.reload();
    },
    openClient : function(id,name){
    	var stammdaten = new Ext.ux.IFrameComponent({
    		id: "idStammdaten"+id+myExt.GetRandom(1,10000),
    		url: '../orders/index.php?id='+id,
    		title: 'Bestellinformationen',
    		listeners: {'activate': function(){Ext.getCmp('leftPanel').collapse()}}
    	});
    	var customer = new Ext.TabPanel({
    		deferredRender:true,
    		width:700,
    		enableTabScroll:true,
    		forceFit:true,
    		height:500,
    		id:'CT'+myExt.GetRandom(1,10000),
    		title:name,
    		activeTab:0,
    		closable:true,
    		items:[
    		stammdaten
    		]
    	});
    	myTab.add(
    		customer
    	).show();
	},
	deleteOrderDetail : function(id, orderID, name){
		Ext.MessageBox.confirm('Best?tigung', 'Soll die Position '+name+' wirklich gel?scht werden?', function(btn){
			if(btn == "yes")
			{
				Ext.Ajax.request({
				   url: '../../../backend/ajax/orderes.php',
				   params: 	{id:orderID,
							s_order_details_id: id,
				   			action:'deletePositionForExt'},
				   success: function(){
				   		var grid = grids[orderID];
        				grid.store.load({params:{orderID:orderID}});
						alert('L?schvorgang erfolgreich!');
				   }
				});
			}else{
				alert('L?schvorgang abgebrochen!');
			}
		});
	},
	deleteOrder : function(id, ordernumber, customer){
		Ext.MessageBox.confirm('<?php echo $sLang["orderlist"]["del_order_confirmtitle"]; ?>', '<?php echo $sLang["orderlist"]["del_order_confirmtxt_1"]; ?> '+ordernumber+' <?php echo $sLang["orderlist"]["del_order_confirmtxt_2"]; ?> '+customer+' <?php echo $sLang["orderlist"]["del_order_confirmtxt_3"]; ?>', function(btn){
			if(btn == 'yes')
			{
				Ext.Ajax.request({
				   url: '<?php echo $_SERVER["SERVER_PORT"] == "80" ? "http" : "https" ?>://<?php echo $sCore->sCONFIG['sBASEPATH']?>/engine/backend/ajax/deleteOrder.php',
				   success: function(){
				   		alert('<?php echo $sLang["orderlist"]["del_order_acknowltxt_1"]; ?> '+ordernumber+' <?php echo $sLang["orderlist"]["del_order_acknowltxt_2"]; ?> '+customer+' <?php echo $sLang["orderlist"]["del_order_acknowltxt_3"]; ?>');
				   		Ext.getCmp('orderlist_grid').store.load();
				   },
				   params: { 'delete': id }
				});
			}else{
				alert('<?php echo $sLang["orderlist"]["del_order_cancel"]; ?>');
			}
		});
	},
	captureAmount : function(id, ordernumber, customer, amount){
		Ext.MessageBox.prompt('Betrag', 'Welcher Betrag soll eingezogen werden?', function(btn, inputAmount){
			if(btn == "ok") {
				Ext.Ajax.request({
					// url: '../../../../shopware.php/sViewport,BuiswPaymentPayone/sAction,captureAmount',
					url: '../../../../backend/BuiswPaymentPayone/captureAmount',
					params:{
						oID:ordernumber,
						amount:inputAmount,
						action: 'capture'
					},
				   	success: function(response){
						var result = Ext.util.JSON.decode(response.responseText);

						if(result.error) {
							alert(result.error);
						} else {
							alert('Der Betrag wurde erfolgreich eingezogen!');
							Ext.getCmp('orderlist_grid').store.load({params:{start:0, limit:25, filterPayment: "<?php echo $PAYONE_ID ?>"}});
						}
					},
					failure: function(){
						alert("FEHLER! Verbindung fehlgeschlagen!");
					}
				});
			}
		}, '', '', amount);
	},
	refundAmount : function(id, ordernumber, customer){
		Ext.MessageBox.prompt('Betrag', 'Welcher Betrag soll gutgeschrieben werden??', function(btn, inputAmount){
			if(btn == "ok") {
				Ext.Ajax.request({
					// url: '../../../../shopware.php/sViewport,BuiswPaymentPayone/sAction,refundAmount',
					url: '../../../../backend/BuiswPaymentPayone/refundAmount',
					params:{
						oID:ordernumber,
						amount:inputAmount,
						action: 'refund'
					},
				   	success: function(response){
						var result = Ext.util.JSON.decode(response.responseText);

						if(result.error) {
							alert(result.error);
						} else {
							alert('Der Betrag wurde erfolgreich eingezogen!');
							Ext.getCmp('orderlist_grid').store.load({params:{start:0, limit:25, filterPayment: "<?php echo $PAYONE_ID ?>"}});
						}
					},
					failure: function(){
						alert("FEHLER! Verindung fehlgeschlagen!");
					}
				});
			}
		});
	},
	init : function(){
       Ext.state.Manager.setProvider(new Ext.state.CookieProvider());




       store = new Ext.data.Store({
	        url: '<?php echo $_SERVER["SERVER_PORT"] == "80" ? "http" : "https" ?>://<?php echo $_SERVER['HTTP_HOST']?>/engine/backend/ajax/getOrders.php',
	        baseParams: {pagingID:storeid, filterPayment: "<?php echo $PAYONE_ID ?>"},
	        // create reader that reads the Topic records
	        reader: new Ext.data.JsonReader({
	            root: 'order',
	            totalProperty: 'totalCount',
	            id: 'id',
	            fields: [
	                'id','ordertimeFormated','ordernumber','invoice_amount','transactionID','taxfree','statusDescription','pdf','status','cleared','clearingDescription','paymentTpl','paymentDescription', 'customer','userID', 'dispatch','subshop'
	            ]
	        }),
			listeners: {
	        	'load' : {fn: function(){
	        		for (var gridID in grids)
		      		{
					    grids[gridID].destroy();
					}
					grids = {};
	        	}}
        	},
	        // turn on remote sorting
	        remoteSort: true
    	});

    store.setDefaultSort('ordertime', 'desc');

    var statestoreArray = new Array();
    /*
    Bestellstatatus
    */
    <?php
    // Read possible order states
    $getStates = mysql_query("
    SELECT * FROM s_core_states WHERE `group`='state' AND id>=0 ORDER BY position ASC
    ");
    while ($state=mysql_fetch_assoc($getStates)){
     	$states[] = "[{$state["id"]},'{$state["description"]}']";
     	?>
     		statestoreArray[<?php echo $state["id"] ?>] = '<?php echo $state["description"] ?>';
     	<?php
    }
    ?>
    // For use in grid
    var states = [<?php echo implode(",",$states) ?>];
    // trigger the data store load
    var statestore = new Ext.data.SimpleStore({
 	    fields: ['id', 'state'],
	    data : states
	});

	// For use in filter-form
	var statesForm = [[-1,'<?php echo $sLang["orderlist"]["orders_show_all"] ?>'],<?php echo implode(",",$states) ?>];
    // trigger the data store load
    var statestoreForm = new Ext.data.SimpleStore({
 	    fields: ['id', 'state'],
	    data : statesForm
	});

	var paymentArray = new Array();
	var groupArray = new Array();
    var shopArray = new Array();
    var dispatchArray = new Array();
	/*
    Bestellstatatus
    */
    <?php
    // Read possible order states
    $getPayment = mysql_query("
    SELECT id, description FROM s_core_paymentmeans ORDER BY id ASC
    ");
    while ($payment=mysql_fetch_assoc($getPayment)){
     	$paymentmeans[] = "[{$payment["id"]},'{$payment["description"]}']";
     	?>
     		paymentArray[<?php echo $payment["id"] ?>] = '<?php echo $payment["description"] ?>';
     	<?php
    }
    ?>
    var paymentmeans = [[-1,'<?php echo $sLang["orderlist"]["orders_show_all"] ?>'],<?php echo implode(",",$paymentmeans) ?>];
    // trigger the data store load
    var paymentstore = new Ext.data.SimpleStore({
	    fields: ['id', 'state'],
	    data : paymentmeans
	});
	 <?php

    // Read possible customergroups
    // -- sth, 304 --
    $getGroups = mysql_query("
    SELECT groupkey,description FROM s_core_customergroups ORDER BY id ASC
    ");
    while ($group=mysql_fetch_assoc($getGroups)){
     	$groups[] = "['{$group["groupkey"]}','{$group["description"]}']";
     	?>
     		groupArray['<?php echo $group["groupkey"] ?>'] = '<?php echo $group["description"] ?>';
     	<?php
    }
    ?>
    var groups = [[-1,'<?php echo $sLang["orderlist"]["orders_show_all"] ?>'],<?php echo implode(",",$groups) ?>];
    // trigger the data store load
    var groupstore = new Ext.data.SimpleStore({
	    fields: ['id', 'state'],
	    data : groups
	});
	 // -- sth, 304 --

	 <?php
	 // Read possible subshops
    // -- sth, 304 --
    $getShops = mysql_query("
    SELECT id AS groupkey,name AS description FROM s_core_multilanguage ORDER BY id ASC
    ");
    while ($shop=mysql_fetch_assoc($getShops)){

     	$shops[] = "['{$shop["groupkey"]}','{$shop["description"]}']";
     	?>
     		shopArray['<?php echo $shop["groupkey"] ?>'] = '<?php echo $shop["description"] ?>';
     	<?php
    }
    ?>
    var shops = [[-1,'<?php echo $sLang["orderlist"]["orders_show_all"] ?>'],<?php echo implode(",",$shops) ?>];
    // trigger the data store load
    var shopstore = new Ext.data.SimpleStore({
	    fields: ['id', 'state'],
	    data : shops
	});
	 // -- sth, 304 --
	<?php
	// Read possible dispatches
    // -- sth, 304 --
    if (!empty($sCore->sCONFIG['sPREMIUMSHIPPIUNG']))
    {
    	$dispatch_table = 's_premium_dispatch';
    }
    else
    {
    	$dispatch_table = 's_trueshippingcosts_dispatch';
    }
    $getDispatches = mysql_query("
    SELECT id AS groupkey,name AS description FROM $dispatch_table ORDER BY id ASC
    ");
    while ($dispatch=mysql_fetch_assoc($getDispatches)){
     	$dispatches[] = "['{$dispatch["groupkey"]}','{$dispatch["description"]}']";
     	?>
     		dispatchArray['<?php echo $dispatch["groupkey"] ?>'] = '<?php echo $dispatch["description"] ?>';
     	<?php
    }
    ?>
    var dispatches = [[-1,'<?php echo $sLang["orderlist"]["orders_show_all"] ?>'],<?php echo implode(",",$dispatches) ?>];
    // trigger the data store load
    var dispatchstore = new Ext.data.SimpleStore({
	    fields: ['id', 'state'],
	    data : dispatches
	});
	// -- sth, 304 --

	/*
	Zahlstati
	*/
	var paystatesArray = new Array();
	<?php
    // Read possible order states
    $getStates = mysql_query("
    SELECT * FROM s_core_states WHERE `group`='payment' AND id>=0 ORDER BY position ASC
    ");
    unset($states);
    while ($state=mysql_fetch_assoc($getStates)){
     	$states[] = "[{$state["id"]},'{$state["description"]}']";
     	?>
     		paystatesArray[<?php echo $state["id"] ?>] = '<?php echo utf8_encode($state["description"]) ?>';
     	<?php
    }
    ?>
    var paystates = [<?php echo implode(",",$states) ?>];
    // trigger the data store load
    var paystore = new Ext.data.SimpleStore({
    	id: id,
	    fields: ['id', 'state'],
	    data : paystates
	});
	// For use in filter-form
	var paystatesForm = [[-1,'<?php echo $sLang["orderlist"]["orders_show_all"] ?>'],<?php echo implode(",",$states) ?>];
    // trigger the data store load
    var paystoreForm = new Ext.data.SimpleStore({
    	id: id,
	    fields: ['id', 'state'],
	    data : paystatesForm
	});

	function renderStatus(val, p, r){
		if (val==2 || val==7 || val==12){
			return '<span style="color:#009933;">' + r.data.statusDescription + '</span>';
		}else {
			return '<span style="color:red;">' + r.data.statusDescription + '</span>';
		}
    }
    function renderCleared(val, p, r){
    	if (val==11 || val==12){
			return '<span style="color:#009933;">' + r.data.clearingDescription + '</span>';
		}else {
			return '<span style="color:red;">' + r.data.clearingDescription + '</span>';
		}
    }
    function renderOption(value, p, record){

    	value = String.format(
    		'<a class="ico cards_minus" style="cursor:pointer; float:right;" onclick="myExt.deleteOrderDetail({0},{1},\'{2}\');"></a>',
    		record.data.id,record.data.orderID,record.data.name
    	);
		if(record.data.articleID)
		{
			value += String.format(
	    		'<a class="ico package_green" style="cursor:pointer; float:right;" onclick="parent.loadSkeleton(\'articles\', false, \{article: {0}\});"></a>',
	    		record.data.articleID
	    	);
		}
		return value;
    }
    function renderEuro(value, meta, record, rowI, colI, store)
    {
    	if(!value)
			return "";
    	var val = String(value);
    	val = val.split(",").join(".");
    	val = val.split(" "+record.data.templatechar).join("");
    	val = Number(val);
    	val = val.toFixed(2);
    	val = String(val);
    	val = val.split(".").join(",");
    	val = val+" "+record.data.templatechar;

    	val.substr(0,1) == "-" ? color='red' : color='inherit';

    	return "<div style='text-align:right;'><font color='"+color+"'>"+val+"</font></div>";
    }


    // Doc-Window -- sth -- 3.0.4
    var windowPanel  =
	   	new Ext.Panel({
			frame: false,
			title: 'Hinweise',
			collapsible: false,
			html: 'Achtung! Falls Sie Bestellungen markiert haben, zu denen bereits ein Beleg existiert, wird dieser neu generiert!<br />Klicken Sie erst auf Belege drucken, wenn alle markierten Bestellungen bereits in Belege umgewandelt wurden!',
			region: 'north',
			height: 80
		});
		/*
		   <option value="0"><?php echo $sLang["orders"]["main_invoice"] ?></option>
		   <option value="1"><?php echo $sLang["orders"]["main_bill_of_delivery"] ?></option>
		   <option value="2"><?php echo $sLang["orders"]["main_credit"] ?></option>
		   <option value="3"><?php echo $sLang["orders"]["main_reversal"] ?></option>
		*/


		var windowForm = new Ext.FormPanel({
	      labelWidth: 180,
	      frame: false,
	      title: 'Einstellungen',
	      id: 'formpanelDoc',
	      region: 'center',
		  bodyStyle:'padding:5px 5px 0',
		  split:false,
		  collapsible: false,
	      defaults: {width: 170},
	      items: [
	      <?php
	      $query = mysql_query("
	      SELECT id, name FROM s_core_documents ORDER BY id ASC
	      ");
	      while ($doc = mysql_fetch_assoc($query)){
	      	$id = ($doc["id"]-1);
	      	$docs[] = "[$id,'{$doc["name"]}']";
	      }
	      ?>
	       new Ext.form.ComboBox({
	      		fieldLabel: 'Art des Belegs',
			    store:new Ext.data.SimpleStore({fields:['id','name'],data:[<?php echo implode(",",$docs) ?>]}),
			    displayField:'name',
			    valueField:'id',
			    typeAhead: true,
			    mode: 'local',
			    id: 'docKind',
			    triggerAction: 'all',
			    selectOnFocus:true,
			    value:0
	      }),
	      new Ext.form.ComboBox({
	      		fieldLabel: 'Modus',
			    store:new Ext.data.SimpleStore({fields:['id','name'],data:[[0,'Alle Belege neu erstellen'],[1,'Nur nicht vorhandene erstellen']]}),
			    displayField:'name',
			    valueField:'id',
			    typeAhead: true,
			    mode: 'local',
			    id: 'docMode',
			    triggerAction: 'all',
			    selectOnFocus:true,
			    value:0
	      }),
	      new Ext.form.ComboBox({
	      		fieldLabel: 'Status ?ndern auf',
			    store: statestoreForm,
			    displayField:'state',
			    valueField:'id',
			    typeAhead: true,
			    mode: 'local',
			    id: 'docChangeState',
			    triggerAction: 'all',
			    emptyText:'Nicht ?ndern',
			    selectOnFocus:true,
			    value:-1
	      }),
	      new Ext.form.ComboBox({
	      		fieldLabel: 'Zahlstatus ?ndern auf',
			    store: paystoreForm,
			    displayField:'state',
			    valueField:'id',
			    typeAhead: true,
			    mode: 'local',
			    id: 'docChangePayment',
			    triggerAction: 'all',
			    emptyText:'Nicht ?ndern',
			    selectOnFocus:true,
			    value:-1
			})
	      ]
	    });



		var progressPanel  =
	   	new Ext.Panel({
			title: 'Fortschrittsanzeige',
			region: 'south',
			collapsible: false,
			height: 60,
			items: [new Ext.ProgressBar({
		        text:'Klicken Sie auf Belege generieren...!',
		        id:'install_progress',
		        cls:'left-align'
		    })]
		});

	   myExt.window = new Ext.Window({
	        title: 'Belege verarbeiten',
	        width: 500,
	        id: 'window1234',
	        closeAction: 'hide',
	        height:400,
	        minWidth: 300,
	        minHeight: 400,
	        layout: 'border',
	        hidden:true,
	        plain:true,
	        bodyStyle:'padding:5px;',
	        buttonAlign:'center',
	        items: [windowPanel,windowForm,progressPanel],
	        buttons: [{
	            text: 'Belege generieren', handler: myExt.doPDF
	        },
	        {
	            text: 'Belege drucken', handler: myExt.doCombine
	        },
	        {
	            text: 'Schlie?en', handler: function (){
	            	Ext.getCmp('window1234').hide();
	            }
	        }]

	    });

	// Doc-Window -- sth -- 3.0.4

    var expander = new Ext.grid.RowExpander({
        /*tpl : new Ext.Template(
            '<p><b>Company:</b> {company}</p><br>',
            '<p><b>Summary:</b> {desc}</p>'
        ),*/
        listeners: {
        	'beforeexpand' : {fn: function(expander, record, body, rowIndex){

        		if(grids[record.data.id])
        		{
        			grids[record.data.id].store.load({params:{orderID:record.data.id}});
		            return;
		        }
			    var grid = new Ext.grid.EditorGridPanel({
			        store: new Ext.data.Store({
				        url: '<?php echo $_SERVER["SERVER_PORT"] == "80" ? "http" : "https" ?>://<?php echo $sCore->sCONFIG['sBASEPATH']?>/engine/backend/ajax/getOrderDetails.php',
				        baseParams: {orderID:record.data.id},
				        reader: new Ext.data.JsonReader({
				            root: 'articles',
				            totalProperty: 'count',
				            id: 'id',
				            fields: [
				                'id','articleordernumber','name','quantity','price','amount','status','tax', 'instock', 'articleID', 'status_description', 'orderID','priceCalc1SourcePrice','priceCalc1Supplier','priceCalc1SKU', 'templatechar', 'attr1',  'attr2'
				            ]
				        })
			    	}),
			        columns: [
			        	{header: "Art-Nr.", dataIndex: "articleordernumber", width:40, sortable: true},
			            {header: "Bezeichnung", dataIndex: 'name', width: 80, sortable: true},
			            {header: "Anzahl", dataIndex: 'quantity', width: 40, sortable: true},
			            //{header: "Bestand", dataIndex: 'instock', width: 30, sortable: true},
			            {header: "Status", dataIndex: 'status', width: 60, sortable: true,
				            editor: new Ext.form.ComboBox({
				            	typeAhead: true,
				            	forceSelection: true,
				            	triggerAction: 'all',
				            	store: new Ext.data.SimpleStore({
				            		fields: ['id', 'value'],
				            		data: [
				            			[0, 'Offen'],
					            		[1, 'In Bearbeitung'],
					            		[2, 'Storniert'],
					            		[3, 'Abgeschlossen']
					            	]
				            	}),
				            	displayField: 'value',
				            	valueField: 'id',
				            	lazyRender: true,
				            	mode:'local',
				            	selectOnFocus:true
				            }),
				            renderer: function(value, p, record){
				            	return record.data.status_description;
				            }
			            },
			            {header: "MwSt", dataIndex: 'tax', width: 40, sortable: true, align: 'right'},
			            {header: "Preis", dataIndex: 'price', width: 40, sortable: true, align: 'right', renderer:renderEuro},
			            {header: "Gesamt", dataIndex: 'amount', width: 40, sortable: true, align: 'right', renderer:renderEuro},
						{header: "Optionen", dataIndex: 'options', width: 40, sortable: true, renderer:renderOption}
			        ],
			        stripeRows: false,
			        trackMouseOver: false,
			        viewConfig: {
			            forceFit:true,
			            stripeRows: false
			        },
			        listeners : {
			        	"afteredit" : {fn: function(e){
			        		if (e.value instanceof Date) {
								var value = e.value.format('Y-m-d H:i:s');
							} else	{
								var value = e.value;
							}
							if(e.field=="status")
							{
								var record = e.grid.colModel.config[e.column].editor.store.getAt(value);
								e.record.set("status_description", record.data.value);
							}
							Ext.Ajax.request({
					           	url: '<?php echo $_SERVER["SERVER_PORT"] == "80" ? "http" : "https" ?>://<?php echo $sCore->sCONFIG['sBASEPATH']?>/engine/backend/ajax/updateOrderDetails.php',
					           	params: {
					           		id: e.record.id,
					           		field: e.field,
					           		value: value
					            },
					           	success:function(response,options){
					         		e.record.store.commitChanges();
					           	}
							});
			        	}, scope:this}
			        }
			    });
			    grids[record.data.id] = grid;
				grid.render(body);
				grid.store.load();
			    //body.innerHTML = "<p>test</p>";

        	}, scope:this},
        	'beforecollapse' : {fn: function(expander, record, body, rowIndex){
	        	//console.log(rowIndex);
        	}, scope:this}
        }
    });

    var cm = new Ext.grid.ColumnModel([
		{
    		header: "",
    		width: 20,
    		sortable: false,
    		locked:true,
    		renderer: function (v,p,r,rowIndex,i,ds){
    			if (r.data.pdf){
    				var pdf = '<a class="ico page_white_acrobat"></a>';
    			}else  {
    				var pdf = '';
    			}
    			return '<input type="checkbox" class="markedOrders" name="markedOrders" value="'+r.data.id+'" style="float:left;margin-right:3px" />'+pdf;
    		}
    	},
    	expander,
   		{
           id: 'customernumber',
           header: "<?php echo $sLang["orderlist"]["orders_time"] ?>",
           dataIndex: 'ordertimeFormated',
           width: 150,
    	   sortable: true
        },
        {
           id: 'regdate',
           header: "<?php echo $sLang["orderlist"]["orders_ordernumber"] ?>",
           dataIndex: 'ordernumber',
           width: 70,
    	   sortable: true
        },
        {
           header: "<?php echo $sLang["orderlist"]["orders_Amount"] ?>",
           dataIndex: 'invoice_amount',
           width: 35,
           align: 'right',
    	   sortable: true
        },
    	{
           id: 'transactionID',
           header: "<?php echo $sLang["orderlist"]["orders_Transaction"] ?>",
           dataIndex: 'transactionID',
           width: 80,
           editor: new Ext.form.TextField({readOnly:true}),
    	   sortable: true
        },
        {
           id: 'dispatch',
           header: "Versandart",
           dataIndex: 'dispatch',
           width: 80,
    	   sortable: true
        },
        {
           id: 'subshopID',
           header: "Shop",
           dataIndex: 'subshop',
           width: 80,
    	   sortable: true
        },
    	{
           id: 'orderstate',
           header: "<?php echo $sLang["orderlist"]["orders_Order_Status"] ?>",
           dataIndex: 'status',
           width: 100,
           renderer: renderStatus,
           editor: new Ext.form.ComboBox({
               typeAhead: true,
               forceSelection: true,
               triggerAction: 'all',
               store: statestore,
               width: 200,
               displayField: 'state',
               valueField: 'id',
               lazyRender: false,
               mode:'local',
               selectOnFocus:true
            }),
    	   sortable: true
        },
        {
           id: 'lastname',
           header: "<?php echo $sLang["orderlist"]["orders_paymentstatus"] ?>",
           dataIndex: 'cleared',
           width: 100,
           renderer: renderCleared,
           editor: new Ext.form.ComboBox({
               typeAhead: true,
               forceSelection: true,
               triggerAction: 'all',
               store: paystore,
               width: 200,
               displayField: 'state',
               valueField: 'id',
               lazyRender: false,
               mode:'local',
               selectOnFocus:true
            }),
    	   sortable: true
        },
        {
           header: "<?php echo $sLang["orderlist"]["orders_paymentdescription"] ?>",
           dataIndex: 'paymentDescription',
           width: 75,
    	   sortable: true
        },
        {
           header: "<?php echo $sLang["orderlist"]["orders_customer"] ?>",
           dataIndex: 'customer',
           width: 75,
    	   sortable: true
        },
        {
           header: "<?php echo $sLang["orderlist"]["orders_options"] ?>",
           dataIndex: 'options',
           width: 85,
           align: 'right',
           renderer: renderOptions
        }
        ]);
    cm.defaultSortable = true;


    function renderOptions(value, p, r){
		var id = r.data.id;
		var name = r.data.ordernumber + " " + r.data.customer;
		var ordernumber = r.data.ordernumber;
		var customer = r.data.customer;
		var orderAmount = r.data.invoice_amount;


		return String.format(
		'<a class="ico pencil" style="cursor:pointer" onclick="myExt.openClient({0},{1})"></a><a class="ico pencil_arrow" style="cursor:pointer" onclick="parent.loadSkeleton({2},false,{3})"></a><a class="ico delete" style="cursor:pointer" onclick="myExt.deleteOrder({4},{5},{6})"></a><a class="ico money_plus" style="cursor:pointer" title="Betrag einziehen" onclick="myExt.captureAmount({4},{5},{6},{7})"></a><a class="ico money_minus" style="cursor:pointer" title="Betrag gutschreiben" onclick="myExt.refundAmount({4},{5},{6})"></a>',id,"'"+name+"'","'orders'","{'id':"+r.data.id+"}",id,"'"+ordernumber+"'","'"+customer+"'","'"+orderAmount+"'"
		);
    }

	Ext.ux.IFrameComponent = Ext.extend(Ext.BoxComponent, {
	 onRender : function(ct, position){
	      this.el = ct.createChild({tag: 'iframe', id: 'framepanel'+this.id, frameBorder: 0, src: this.url});
	 }
	});
    var limitArray = [['25'],['50'],['100'],['250'],['500']];
	var limitStore = new Ext.data.SimpleStore({
        fields: ['limitArray'],
        data : limitArray
    });

    var pager = new Ext.PagingToolbar({
            pageSize: 25,
            store: store,
            displayInfo: true,
            displayMsg: '<?php echo $sLang["orderlist"]["orders_orders"] ?> {0} - {1} <?php echo $sLang["orderlist"]["orders_from"] ?> {2}',
            emptyMsg: "<?php echo $sLang["orderlist"]["orders_no_orders_found"] ?>",
            items:[
            '<?php echo $sLang["orderlist"]["orders_numbers_of_orders"] ?>',
            {
            	xtype: 'combo',
            	id: 'status',
            	fieldLabel: 'Last Name',
            	typeAhead: false,
            	title:'<?php echo $sLang["orderlist"]["orders_numbers_of_orders"] ?>',
            	forceSelection: false,
            	triggerAction: 'all',
            	store: limitStore,
            	displayField: 'limitArray',
            	lazyRender: false,
            	lazyInit: false,
            	mode:'local',
            	width: 120,
            	selectOnFocus:true,
            	listClass: 'x-combo-list-small',
            	listeners: {
	            	'change' : {fn: limitFilter, scope:this}
            	}
        	},
        	'-'
        	,
            '<?php echo $sLang["user"]["user_search"] ?> ',
            {
            	xtype: 'textfield',
            	id: 'search',
            	selectOnFocus: true,
            	width: 120,
            	listeners: {
	            	'render': {fn:function(ob){
	            		ob.el.on('keyup', searchFilter, this, {buffer:500});
	            	}, scope:this}
            	}
            },new Ext.Button  ( {
            	text: '<?php echo $sLang["orderlist"]["orders_update"] ?>',

                handler: myExt.reload
            }),
            new Ext.Button  ({
	            	text: 'Alle markieren',
	           		handler: myExt.markAll
            })
            ,new Ext.Button  ( {
            	text: 'Belege verarbeiten',
                handler: myExt.showProcess
            })
            ]
        });
    var AutoHeightGridView = Ext.extend(Ext.grid.GridView, {

                    onLayout: function () {
                        Ext.get(this.innerHd).setStyle("float", "none");
                        this.scroller.setStyle("overflow-x", "auto");
                    }

	});
    var grid = new Ext.grid.EditorGridPanel({
      	region:'center',
      	id: 'orderlist_grid',
        width:700,
        title:'<?php echo $sLang["orderlist"]["orders_Order_Summary"] ?>',
        store: store,
        cm: cm,
        plugins: expander,
        autoHeight:false,
        autoSizeColumns: true,
        trackMouseOver:true,
        //Ext Bug Using Hotfix "emptyFn"
        sm: new Ext.grid.RowSelectionModel({selectRow:Ext.emptyFn}),
        loadMask: true,
        stripeRows: true,
		view: new AutoHeightGridView(),
		bbar: pager,
     	listeners: {
     	'activate': function(){Ext.getCmp('leftPanel').expand();},
    	'rowclick' : {fn: function(grid,number,event){
	    		var gridField = grid.store.data.items[number].data;
	    		if (!gridField.pdf){
	    			storeDocuments.removeAll();

	    			return;
	    		}
				//console.log(gridField);
				var id = gridField.id;
				//   baseParams: {pagingID:storeid,type:'forExt3',id: 6},
				storeDocuments.load({params:{start:0,id:id, limit:25,type:'forExt3'}});
				return false;
    		}

		}
    }});
	//grid.store.on('load',grid.autoSizeColumns,grid);
   function limitFilter () {
	    var status = Ext.getCmp("status");
	    grid.store.baseParams["limit"] = status.getValue();
	    pager.pageSize = parseInt(status.getValue());
	    grid.store.lastOptions.params["start"] = 0;
	    grid.store.reload();
	}

	grid.addListener('afteredit', changeStatus);
	grid.addListener('beforeedit', function(grid){
		if(grid.field == "cleared")
		{
			if(grid.value == 0)
			{
				store.getAt(grid.row).set('cleared', 17);
			}
		}
	});

	function changeStatus(oGrid_Event) {
		//console.log(oGrid_Event);
		if (oGrid_Event.value instanceof Date) {
			var fieldValue = oGrid_Event.value.format('Y-m-d H:i:s');
		} else	{
			var fieldValue = oGrid_Event.value;
		}
        Ext.Ajax.request({
        	waitMsg: 'Saving changes...',
       		url: "<?php echo $_SERVER["SERVER_PORT"] == "80" ? "http" : "https" ?>://<?php echo $sCore->sCONFIG['sBASEPATH']?>/engine/backend/ajax/changeOrderState.php",
           	params: {
           		//task: "update",
           		//key: 'articleID',
           		artID: oGrid_Event.record.id,
           		field: oGrid_Event.field,
           		value: fieldValue,
           		originalValue: oGrid_Event.record.modified
            },
           	success:function(response,options){


           		if (oGrid_Event.field=="status"){
           			oGrid_Event.record.data.statusDescription = oGrid_Event.grid.store.stateArray[fieldValue];
           			var newState = oGrid_Event.grid.store.stateArray[fieldValue];
           			var payment = oGrid_Event.record.data.paymentTpl;
           			StageChangeMailRequest (oGrid_Event.record.id,fieldValue,payment,oGrid_Event.record.data.ordernumber);
           		}else if(oGrid_Event.field=="cleared"){
           			oGrid_Event.record.data.clearingDescription = oGrid_Event.grid.store.clearedArray[fieldValue];
           			var newState = oGrid_Event.grid.store.clearedArray[fieldValue];
           			StageChangeMailRequest (oGrid_Event.record.id,fieldValue,"","");
           		}else if (oGrid_Event.field=="trackingcode"&&fieldValue!="")
           		{
           			//var myAjax = new Ajax('OrderStateMail.php',{method: 'post', onComplete: function(){
					//		parent.Growl("<?php echo$sLang["orderlist"]["orders_mail_send"]?>");
					//}}).request("action=sendTrackingCodeMail&id="+oGrid_Event.record.data.id);
					StageChangeMailRequest (oGrid_Event.record.id,"TrackingCode","","");
           		}

           		//parent.Growl("<?php echo $sLang["orderlist"]["orders_The_status_of_the_order"] ?> "+oGrid_Event.record.data.ordernumber+" <?php echo $sLang["orderlist"]["orders_has_left"] ?> "+newState+" <?php echo $sLang["orderlist"]["orders_amended"] ?>");
           		//console.log(oGrid_Event.record);
           		store.commitChanges();
           		// Show window to send email notification about state change



           	}
    	});
	}


	function searchFilter () {
	    var search = Ext.getCmp("search");
	    store.baseParams["search"] = search.getValue();
	    store.lastOptions.params["start"] = 0;
	    store.reload();
	}

	store.load({params:{start:0, limit:25, filterPayment: "<?php echo $PAYONE_ID ?>"}});

	store.stateArray = statestoreArray;
	store.paymentArray = paymentArray;
	store.clearedArray = paystatesArray;

	store.on('load',function(x,y,z){
		// Reload store
		var data = x.reader.jsonData;
		// => Refresh statistics
    	var amountData = [
		    ['<?php echo $sLang["orderlist"]["orders_Turnover"] ?>',data.totalAmount],
		    ['<?php echo $sLang["orderlist"]["orders_Orders"] ?>',data.totalCount]
    	];

    	for (var item in data){
    		if (item.match(/payment/)){
    			var paymentID = item.replace(/payment/,"");
    			amountData[amountData.length] = ['Per '+x.paymentArray[paymentID],data[item]];
    		}
    		if (item.match(/status/)){
    			var stateID = item.replace(/status/,"");
    			amountData[amountData.length] = [x.stateArray[stateID],data[item]];
    		}
    	}




	   	storeAmount.loadData(amountData);
	});

	var checkboxgroup = {
		xtype: 'checkboxgroup',
		hideLabel: true,
		itemCls: 'x-check-group-alt',
		id: 'x1',
		columns: 1,
		items: [<?php
		$result = mysql_query("
			SELECT id, description FROM s_core_states WHERE `group`='state' AND id>=0 ORDER BY position ASC
		");
		$order_status = array();
		if($result&&mysql_num_rows($result))
		while ($status = mysql_fetch_assoc($result)){
			$order_status[$status["id"]] = $status["description"];
		}
		$a=0;
		foreach ($order_status as $id=>$value){

			if($a != 0 && $a < count($order_status)) echo ",";
			?>{boxLabel: '<?php echo$value?>', name: '<?php echo$id?>', checked: false}<?php
			$a++;
		}
		?>]
	};

	var checkboxgroup2 = {
		xtype: 'checkboxgroup',
		hideLabel: true,
		itemCls: 'x-check-group-alt',
		columns: 1,
		id: 'x2',
		items: [<?php
		$result = mysql_query("
			SELECT id, description FROM s_core_states WHERE `group`='payment' AND id>=0 ORDER BY position ASC
		");
		$order_status2 = array();
		if($result&&mysql_num_rows($result))
		while ($status = mysql_fetch_assoc($result)){
			$order_status2[$status["id"]] = $status["description"];
		}
		$a = 0;
		foreach ($order_status2 as $id=>$value){
			if($a != 0 && $a < count($order_status2)) echo ",";
			?>{boxLabel: '<?php echo$value?>', name: '<?php echo$id?>', checked: false}<?php
			$a++;
		}
		?>]
	};

	var checkboxgroup3 = {
		xtype: 'checkboxgroup',
		hideLabel: true,
		itemCls: 'x-check-group-alt',
		columns: 1,
		id: 'x3',
		style: 'overflow:hidden;',
		items: [<?php
		echo join (',', $zahlungs_methoden);
		?>]
	};

    var dr = new Ext.FormPanel({
      labelWidth: 80,
      frame: true,
      id: 'formpanel',
      layout:'form',
	  bodyStyle:'padding:5px 5px 0',
	  split:true,
	  collapsible: true,
      defaults: {width: 170},
      defaultType: 'datefield',
      items: [{
        fieldLabel: '<?php echo $sLang["orderlist"]["orders_from"] ?>',
        name: 'startdt',
        id: 'startdt',
        format: 'd.m.Y',
        value: '<?php echo date("d.m.Y",mktime(0,0,0,date("m"),date("d")-7,date("Y"))) ?>',
        endDateField: 'enddt' // id of the end date field
      },{
        fieldLabel: '<?php echo $sLang["orderlist"]["orders_until"] ?>',
        name: 'enddt',
        id: 'enddt',
        format: 'd.m.Y',
        value: '<?php echo date("d.m.Y") ?>',
        startDateField: 'startdt' // id of the start date field
      },
      /*
       new Ext.form.ComboBox({
      		fieldLabel: 'Status',
		    store: statestoreForm,
		    displayField:'state',
		    valueField:'id',
		    typeAhead: true,
		    mode: 'local',
		    id: 'filterstate',
		    triggerAction: 'all',
		    emptyText:'<?php echo $sLang["orderlist"]["orders_Please_select"] ?>',
		    selectOnFocus:true,
		    value:-1
       }),
       */
      new Ext.form.ComboBox({
      	fieldLabel: 'Status',
      	id: 'filterstate',
      	name:'state2',
      	hiddenName:'state2',
      	layout: 'form',
      	store:new Ext.data.SimpleStore({fields:[["id"],["name"]],data:[[]]}),
      	valueField:'id',
      	displayField:'name',
      	editable:true,
      	forceSelection : true,
      	shadow:false,
      	mode: 'local',
      	triggerAction:'all',
      	emptyText:'<?php echo $sLang["orderlist"]["orders_show_all"] ?>',
      	maxHeight: 200,
      	lazyInit: true,
      	tpl: '<tpl for="."></tpl>',
      	selectedClass:'',
      	onSelect:Ext.emptyFn,
      	//emptyText:'<?php echo $sLang["orderlist"]["orders_show_all"] ?>',
      	//value:-1,
      	listeners: {
      	'render': {fn:function(combobox){
      		//combobox.store.add(new Ext.data.Record({id: -1,name: '<?php echo $sLang["orderlist"]["orders_show_all"] ?>'},-1));
      		//combobox.setValue(-1);
      	}, scope:this},
      	'expand': {fn:function(combobox){

      		Ext.destroy(Ext.getCmp('checkboxgroupform1'));

			var checkboxgroupform1 = new Ext.FormPanel({
				id: 'checkboxgroupform1',
				items: checkboxgroup,
				style: "font-size:12px"
			});
      		checkboxgroupform1.render(combobox.innerList);

      	}, scope:this},
      	'collapse': {fn:function(combobox){
      		var values = Ext.getCmp('checkboxgroupform1').getForm().getValues();
      		var id = "";
      		var text = "";
      		var count = 0;
      		for (var itemID in checkboxgroup.items)
      		{
      			if(values[checkboxgroup.items[itemID].name]=='on')
      				checkboxgroup.items[itemID].checked = true;
      			else
      				checkboxgroup.items[itemID].checked = false;
      		}
      		for (var statusID in values)
      		{
			    if(count) id += ",";
			    if(count) text += ", ";
   				id += statusID;
      			text += statestoreArray[statusID];
      			count++;
			}
			if(count!=0&&count!=statestoreArray.length)
			{
	      		combobox.store.add(new Ext.data.Record({id: id,name: text},id));
	      		combobox.setValue(id);
			}
			else
			{
				combobox.setValue(null);
			}
      	}, scope:this}
      	}
      }),
     new Ext.form.ComboBox({
      	fieldLabel: '<?php echo $sLang["orderlist"]["orders_Number_status"] ?>',
      	id: 'filtercleared',
      	name:'state',
      	hiddenName:'state',
      	layout: 'form',
      	store:new Ext.data.SimpleStore({fields:[["id"],["name"]],data:[[]]}),
      	valueField:'id',
      	displayField:'name',
      	editable:true,
      	forceSelection : true,
      	shadow:false,
      	mode: 'local',
      	triggerAction:'all',
      	emptyText:'<?php echo $sLang["orderlist"]["orders_show_all"] ?>',
      	maxHeight: 200,
      	lazyInit: true,
      	tpl: '<tpl for="."></tpl>',
      	selectedClass:'',
      	onSelect:Ext.emptyFn,
      	listeners: {
      	'render': {fn:function(combobox){
      		//combobox.store.add(new Ext.data.Record({id: -1,name: '<?php echo $sLang["orderlist"]["orders_show_all"] ?>'},-1));
      		//combobox.setValue(-1);
      	}, scope:this},
      	'expand': {fn:function(combobox){
      		Ext.destroy(Ext.getCmp('checkboxgroupform2'));
			var checkboxgroupform2 = new Ext.FormPanel({
				id: 'checkboxgroupform2',
				items: checkboxgroup2
			});
      		checkboxgroupform2.render(combobox.innerList);
      	}, scope:this},
      	'collapse': {fn:function(combobox){
      		var values = Ext.getCmp('checkboxgroupform2').getForm().getValues();
      		var id = "";
      		var text = "";
      		var count = 0;
      		for (var itemID in checkboxgroup2.items)
      		{
      			if(values[checkboxgroup2.items[itemID].name]=='on')
      				checkboxgroup2.items[itemID].checked = true;
      			else
      				checkboxgroup2.items[itemID].checked = false;
      		}
      		for (var statusID in values)
      		{
			    if(count) id += ",";
			    if(count) text += ", ";
   				id += statusID;
      			text += paystatesArray[statusID];
      			count++;
			}
			if(count!=0&&count!=<?php echo (int)count($order_status2);?>)
			{
	      		combobox.store.add(new Ext.data.Record({id: id,name: text},id));
	      		combobox.setValue(id);
			}
			else
			{
				combobox.setValue(null);
			}
      	}, scope:this}
      	}
      }),
      /*
		new Ext.form.ComboBox({
      		fieldLabel: '<?php echo $sLang["orderlist"]["orders_payment"] ?>',
		    store: paymentstore,
		    displayField:'state',
		    valueField:'id',
		    typeAhead: true,
		    mode: 'local',
		    id: 'filterpayment',
		    triggerAction: 'all',
		    emptyText:'<?php echo $sLang["orderlist"]["orders_Please_select"] ?>',
		    selectOnFocus:true,
		    value:-1
		}),
		*/
      new Ext.form.ComboBox({
      	fieldLabel: 'Zahlungsart',
      	id: 'filterpayment',
      	name:'state3',
      	hiddenName:'state3',
      	layout: 'form',
      	store:new Ext.data.SimpleStore({fields:[["id"],["name"]],data:[[]]}),
      	valueField:'id',
      	displayField:'name',
      	editable:false,
		disabled:true,
      	forceSelection : true,
      	shadow:false,
      	mode: 'local',
      	triggerAction:'all',
      	emptyText:'<?php echo $sLang["orderlist"]["orders_show_all"] ?>',
				value: 'PAYONE',
      	maxHeight: 200,
      	lazyInit: true,
      	tpl: '<tpl for="."></tpl>',
      	selectedClass:'',
      	onSelect:Ext.emptyFn,
      	listeners: {
      	'render': {fn:function(combobox){
      		//combobox.store.add(new Ext.data.Record({id: -1,name: '<?php echo $sLang["orderlist"]["orders_show_all"] ?>'},-1));
      		//combobox.setValue(-1);
      	}, scope:this},
      	'expand': {fn:function(combobox){

      		Ext.destroy(Ext.getCmp('checkboxgroupform3'));

			var checkboxgroupform3 = new Ext.FormPanel({
				id: 'checkboxgroupform3',
				items: checkboxgroup3
			});


      		checkboxgroupform3.render(combobox.innerList);

      	}, scope:this},
      	'collapse': {fn:function(combobox){
      		var values = Ext.getCmp('checkboxgroupform3').getForm().getValues();
      		var id = "";
      		var text = "";
      		var count = 0;
      		for (var itemID in checkboxgroup3.items)
      		{
      			if(values[checkboxgroup3.items[itemID].name]=='on')
      				checkboxgroup3.items[itemID].checked = true;
      			else
      				checkboxgroup3.items[itemID].checked = false;
      		}
      		for (var statusID in values)
      		{
			    if(count) id += ",";
			    if(count) text += ", ";
   				id += statusID;
      			text += paymentArray[statusID];
      			count++;
			}
			if(count!=0&&count!=<?php echo (int)count($order_status3);?>)
			{
	      		combobox.store.add(new Ext.data.Record({id: id,name: text},id));
	      		combobox.setValue(id);
			}
			else
			{
				combobox.setValue(null);
			}
      	}, scope:this}
      	}
      }),// -- sth 3.0.4 --
       new Ext.form.ComboBox({
      	fieldLabel: 'Kundengruppe',
      	id: 'filtergroup',
      	name:'group',
      	hiddenName:'group',
      	layout: 'form',
      	store: groupstore,
      	valueField:'id',
      	displayField:'state',
      	editable:true,
      	forceSelection : true,
      	shadow:false,
      	mode: 'local',
      	triggerAction:'all',
      	emptyText:'Alle anzeigen',
      	maxHeight: 200
      }),// -- sth 3.0.4 --
       new Ext.form.ComboBox({
      	fieldLabel: 'Subshop',
      	id: 'filtershop',
      	name:'shop',
      	hiddenName:'shop',
      	layout: 'form',
      	store: shopstore,
      	valueField:'id',
      	displayField:'state',
      	editable:true,
      	forceSelection : true,
      	shadow:false,
      	mode: 'local',
      	triggerAction:'all',
      	emptyText:'Alle anzeigen',
      	maxHeight: 200
      }),// -- sth 3.0.4 --
       new Ext.form.ComboBox({
      	fieldLabel: 'Versandart',
      	id: 'filterdispatch',
      	name:'dispatch',
      	hiddenName:'dispatch',
      	layout: 'form',
      	store: dispatchstore,
      	valueField:'id',
      	displayField:'state',
      	editable:true,
      	forceSelection : true,
      	shadow:false,
      	mode: 'local',
      	triggerAction:'all',
      	emptyText:'Alle anzeigen',
      	maxHeight: 200
      })
      ,
		new Ext.Button  ( {
	    	text: '<?php echo $sLang["orderlist"]["orders_filters"] ?>',
	        handler: filterGrid
    	})
      ]
    });

    /*
    xtype: 'combo',
        	id: 'status',
        	fieldLabel: 'Last Name',
        	typeAhead: false,
        	title:'Anzahl Kunden',
        	forceSelection: false,
        	triggerAction: 'all',
        	store: limitStore,
        	displayField: 'limitArray',
        	lazyRender: false,
        	lazyInit: false,
        	mode:'local',
        	width: 120,
        	selectOnFocus:true,
        	listClass: 'x-combo-list-small',
        	listeners: {
            	'change' : {fn: limitFilter, scope:this}
        	}
    */
    function filterGrid(e,f,p){
		/*
		Filter Grid
		*/
		var startDate = Ext.getCmp("startdt");
	    startDate = startDate.getValue();
	    startDate = startDate.dateFormat("Y-m-d");

	    var endDate = Ext.getCmp("enddt");
	    endDate = endDate.getValue();
	    endDate = endDate.dateFormat("Y-m-d");

	    var state = Ext.getCmp("filterstate");
	    state = state.getValue();

	    var statePayment = Ext.getCmp("filtercleared");
	    statePayment = statePayment.getValue();

	    var payment = Ext.getCmp("filterpayment");
	    payment = payment.getValue();

	    // sth - 3.0.4
	    var group = Ext.getCmp("filtergroup");
	    group = group.getValue();

	    var shop = Ext.getCmp("filtershop");
	    shop = shop.getValue();

	    var dispatch = Ext.getCmp("filterdispatch");
	    dispatch = dispatch.getValue();
	    // --

	    // Reload Grid
	    store.baseParams["startDate"] = startDate;
	    store.baseParams["endDate"] = endDate;

	    store.baseParams["filterState"] = state;
	    store.baseParams["filterCleared"] = statePayment;
	    // store.baseParams["filterPayment"] = payment;

	    // sth - 3.0.4
        store.baseParams["filterGroup"] = group;
        store.baseParams["filterShop"] = shop;
        store.baseParams["filterDispatch"] = dispatch;

	    store.lastOptions.params["start"] = 0;
	    store.reload();

    }
    var storeAmount = new Ext.data.SimpleStore({
        fields: [
           {name: 'description'},
           {name: 'value', type: 'int'}
        ]
    });
    var amountData = [
    ['<?php echo $sLang["orderlist"]["orders_Turnover"] ?>',5000],
    ['<?php echo $sLang["orderlist"]["orders_Orders"] ?>',50],
    ['<?php echo $sLang["orderlist"]["orders_new_customer"] ?>',50],
    ['<?php echo $sLang["orderlist"]["orders_Visitors"]?>',500],
    ['<?php echo $sLang["orderlist"]["orders_Impressions"] ?>',5000]
    ];
    storeAmount.loadData(amountData);

	var statisticGrid = new Ext.grid.GridPanel({
        store: storeAmount,
        columns: [
            {id:'company',header: "<?php echo $sLang["orderlist"]["orders_title"] ?>", width: 160, sortable: true, dataIndex: 'description', hidden: false},
            {header: "<?php echo $sLang["orderlist"]["orders_Worth"] ?>", width: 75, sortable: true, dataIndex: 'value', hidden: false, align: 'right'}
        ],
        stripeRows: true,
		autoScroll: true,
        autoExpandColumn: 'company',
        height:450,
        title:'<?php echo $sLang["orderlist"]["orders_Statistics"] ?>',
        viewConfig: {
            forceFit:true
        }
    });
    var cmDocuments = new Ext.grid.ColumnModel([
		{
           id: 'cm1',
           header: "Datum",
           dataIndex: 'datum',
           width: 80
        },
        {
           id: 'cm2',
           header: "Name",
           dataIndex: 'beleg',
           width: 130,
           renderer: function(value,p,r){

           		if (r.data.hash){
    				var id = r.data.hash;
    			}else {
    				var id = r.data.id;
    			}
    			if (id){
    				var url = "../orders/openPDF.php?pdf="+id;
    				value = '<a href="'+url+'" target="_blank">'+value+'</a>';
    				return value;
    			}else {
    				return value;
    			}
           }
        }
    ]);


    storeDocuments = new Ext.data.Store({
        url: '<?php echo $_SERVER["SERVER_PORT"] == "80" ? "http" : "https" ?>://<?php echo $sCore->sCONFIG['sBASEPATH']?>/engine/backend/ajax/documents.php',
        baseParams: {pagingID:storeid,type:'forExt3'},
        // create reader that reads the Topic records
        reader: new Ext.data.JsonReader({
            root: 'documents',
            totalProperty: 'count',
            id: 'id',
            fields: [
                'id','datum','beleg','amount','hash'
            ]
        }),
        // turn on remote sorting
        remoteSort: true
	});
    storeDocuments.setDefaultSort('ordertime', 'desc');


  var testPanel  =
   	new Ext.Panel({
   		id:'leftPanel2',
		split:true,
		minSize: 250,
		frame: true,
		title: '<?php echo $sLang["orderlist"]["orders_filter"] ?>',

		collapsible: true,
		margins:'0 0 0 0',
		items:[dr,
    	new Ext.grid.GridPanel({
	      	id: 'newTest',
	        title:'Belege',
	        store: storeDocuments,
	        frame: true,
	        cm: cmDocuments,
	        autoSizeColumns: false,
	        monitorWindowResize: false,
	        trackMouseOver:true,
	        height: 150,
	        autoWidth: false,
	        sm: new Ext.grid.RowSelectionModel({selectRow:Ext.emptyFn}),
	        loadMask: true,
	        stripeRows: true,
	        viewConfig: {
	            forceFit:true,
	            stripeRows: true,
	            getRowClass : function(record, rowIndex, p, store){
	              //return 'red-row';
	            }
	        },
	        listeners: {
	        	'rowdblclick' : {fn: function(grid,number,event){

	        	}
	        }}
    	})/*,{
			id:'captureAmount',
			anchor:'50%',
			xtype:'textfield',
			style: {
				float:'left',
				margin:'5 10 0 0'
			}
		},{
			xtype:'button',
			anchor:'50%',
			text:'Betrag einziehen',
			style: {
				margin:'5 0 0 0'
			},
			listeners:{
				'click': function(){
					var rowsSelected = grid.getSelectionModel().getSelected();
					console.log(rowsSelected);
				}
			}
		},{
			id:'refundAmount',
			xtype:'textfield',
			style: {
				float:'left',
				margin:'5 10 0 0'
			}
		},{
			xtype:'button',
			text:'Betrag gutschreiben',
			style: {
				margin:'5 0 0 0'
			},
			listeners:{
	            	'click' : {fn: myExt.refundAmount, scope:this}
			}
		}*/
	   ]
	});

  //storeDocuments.load({params:{start:0, limit:25}});

  var leftPanel =
   	new Ext.TabPanel({
   		id:'leftPanel',
		split:true,
		minSize: 290,
		frame: true,
		width: 290,
		collapsible: true,
		region: 'west',
		margins:'0 0 0 0',
		activeTab:0,
		items:[testPanel,statisticGrid]
	});

	/*
	,
    	new Ext.grid.EditorGridPanel({
	      	id: 'newTest',
	        title:'<?php echo $sLang["orderlist"]["orders_Order_Summary"] ?>',
	        store: store,
	        cm: cm,
	        autoSizeColumns: true,
	        trackMouseOver:false,
	        sm: new Ext.grid.RowSelectionModel({selectRow:Ext.emptyFn}),
	        loadMask: true,
	        stripeRows: true,
	        viewConfig: {
	            forceFit:true,
	            stripeRows: true,
	            getRowClass : function(record, rowIndex, p, store){
	              //return 'red-row';
	            }
	        }
    	})
	*/

   myTab = new Ext.TabPanel({
            region:'center',
            deferredRender:false,
            activeTab:0,
            closeable:true,
            items:[grid]
   });

   var viewport = new Ext.Viewport({
        layout:'border',
        items:[
            leftPanel,myTab
         ]
    });



}};
}();
    Ext.onReady(function(){
    	myExt.init();
    	$('body').setStyle('top',0);
    	$('body').setStyle('left',0);
    });
	</script>
</head>
<body id="body">

<div id="mail" style="z-index:1001;padding:10px;left: 50%; top: 50%; height: 250px; width: 400px; margin-top: -125px; margin-left: -200px; display: none;background-color:#CCC;border: 1px solid;position:absolute;">
<form enctype="multipart/form-data" method="post" id="form" name="form" action="#">
	<label style="width:80px;float:left;"><?php echo $sLang["orderlist"]["orders_Subject"] ?></label>
	<input style="width:300px;" type="text" id="subject" name="subject" value="">
	<br />
	<label style="width:80px;float:left;"><?php echo $sLang["orderlist"]["orders_Recipient"] ?></label>
	<input style="width:300px;" type="text" id="email" name="email" value="">
	<textarea id="content" name="content" style="height: 200px; width: 400px;"></textarea>
	<input type="hidden" id="state" name="state" value="">
	<input type="hidden" id="id" name="id" value="">
	<input type="hidden" id="action" name="action" value="sendMail">
	<input type="hidden" id="frommail" name="frommail" value="">
	<input type="hidden" id="fromname" name="fromname" value="">
	<a href="#" id="submit" class="ico accept" style="float:right; cursor: pointer;"></a>
	<a  class="ico cross" onclick="StageChangeMailClose();" style="float:right; cursor: pointer;"></a>
</form>
</div>

<script>
function StageChangeMail ()
{
	var overlay = new Element('div',{
		'id': 'overlay',
		'styles': {
			'cursor': 'pointer',
			'position': 'absolute',
			'top': '0px',
			'height': window.getScrollHeight(),
			'width': window.getScrollWidth(),
			'background-color': '#000000',
			'z-index': '1000'
		}
	}).setOpacity(0.7).injectInside(document.body);
	window.addEvent('scroll',function(){
		StageChangeMailResize ();
	});
	window.addEvent('resize',function(){
		StageChangeMailResize ();
	});
	overlay.addEvent('click',function(){
		StageChangeMailClose();
	});
}
function StageChangeMailRequest (orderID,newState,payment,ordernumber)
{
	new Ajax('<?php echo $_SERVER["SERVER_PORT"] == "80" ? "http" : "https" ?>://<?php echo $sCore->sCONFIG['sBASEPATH']?>/backend/OrderState/read?id='+orderID+'&status='+newState, {method: 'get',onComplete: function(mail){
		mail = mail.trim();
		if(mail!='FAIL'&&mail!=''&&mail!=null&&mail)
		{
			mail = Json.evaluate(mail);

			$('mail').setStyle('display','block');
			StageChangeMail ();
			$('content').value = mail.content;

			$('subject').value = mail.subject;
			$('email').value = mail.email;
			$('frommail').value = mail.frommail;
			$('fromname').value = mail.fromname;
			$('id').value = orderID;
			$('state').value = newState;
		}
		// STH 3.0.4 Hanseatic Anpassung

		if (newState==7 && payment == "hanseatic.tpl"){
			new Ajax('<?php echo $_SERVER["SERVER_PORT"] == "80" ? "http" : "https" ?>://<?php echo $sCore->sCONFIG['sBASEPATH']?>/engine/connectors/hanseatic/deliver.php?oid='+ordernumber, {method: 'get',onComplete: function(mail){
				alert("Bestellstatus wurde an Hanseatic ?bermittelt");
			}}).request();
		}
	}}).request();
}
function StageChangeMailClose ()
{
	window.removeEvent('click');
	window.removeEvent('resize');
	$('overlay').remove();
	$('content').value = '';
	$('mail').setStyle('display','none');
}
function StageChangeMailResize ()
{
	var h = window.getScrollHeight()+'px';
	var w = window.getScrollWidth()+'px';
	$('overlay').setStyles({height: h, width: w});
}

window.addEvent('domready',function(){
	$('submit').addEvent('click',function(e){
		e = new Event(e);
		var myAjax = new Ajax('<?php echo $_SERVER["SERVER_PORT"] == "80" ? "http" : "https" ?>://<?php echo $sCore->sCONFIG['sBASEPATH']?>/backend/OrderState/send',{method: 'post', onComplete: function(json){
				alert("<?php echo $sLang["orderlist"]["orders_mail_send"]?>");
		}}).request($('form').toQueryString());
		StageChangeMailClose();
		e.stop();
	});
});
</script>

</body>
</html>