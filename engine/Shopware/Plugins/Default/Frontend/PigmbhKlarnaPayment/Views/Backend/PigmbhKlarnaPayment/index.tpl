{extends file="backend/index/parent.tpl"}
{block name="backend_index_css" append}
<!-- Common CSS -->
<link href="{link file='engine/backend/css/icons4.css'}"  rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css" href="{link file='/templates/_default/backend/_resources/resources/css/icon-set.css'}" />
<link href="{link file='engine/backend/css/modules.css'}" rel="stylesheet" type="text/css" />
<link href="{link file='engine/Shopware/Plugins/Default/Frontend/PigmbhKlarnaPayment/css/klarnastyles.css'}" rel="stylesheet" type="text/css" />

<style type="text/css">
    [class*=sprite] {
        width : auto !important;
    }
</style>
{/block}

{block name="backend_index_body_inline"}
<form name="deleteOneForm" method="post" action="{url action=deleteOrder controller=PiPaymentKlarnaBackend}">
    <input type="hidden" id="deleteOne" name="deleteOne" value="10" />
    <input type="hidden" id="ordernumber" name="ordernumber" value="10" />
</form>

<form id="previewForm" name="previewForm" action="" target="_blank" method="post">
    <input type="hidden" name="temp" value="1">
</form>
<div id="table"></div>
<script>

    var piKlarnaCounter = 0;
    var piKlarnaRetoureCounter = 0;
    var stornopi_klarna_counter=0;
    var historypi_klarna_counter=0;
    var articlepi_klarna_counter=0;
    var invoicepi_klarna_counter=0;
    //Create store for Grid-Klarna-orders
    function createMystore(){
        var MyStore =  new Ext.data.Store({
            url: '{url action=getOrders}',
            reader: new Ext.data.JsonReader({
                root: 'items',
                totalProperty: 'total'
            }, [
                { name: 'checkbox'},
                { name: 'klarnaid'},
                { name: 'id'},
                { name: 'bestellzeit'},
                { name: 'bestellnr'},
                { name: 'betrag'},
                { name: 'transaktion'},
                { name: 'versand'},
                { name: 'sprache'},
                { name: 'bestellstatus'},
                { name: 'bestellstatus_kurz'},
                { name: 'zahlstatus'},
                { name: 'zahlart'},
                { name: 'userid'},
                { name: 'kunde'},{ name: 'lastname'},
                { name: 'options_work'},
                { name: 'options_delete'},
                { name: 'options_klarna'}
            ])
        });
        MyStore.load();
        return MyStore;
    }
    //Create columns for Grid-Klarna-orders
    function createMyColums(){
        var MyColumn = new Ext.grid.ColumnModel([
            { dataIndex: 'klarnaid', header: 'Nr.', sortable: true, width: 25 },
            { dataIndex: 'bestellzeit', header: 'Bestellzeit', sortable: true, width: 120 },
            { dataIndex: 'bestellnr', header: 'Bestellnr.', sortable: true, width: 60 },
            { dataIndex: 'transaktion', header: 'Transaktions ID', sortable: true, width: 90 },
            { dataIndex: 'betrag', header: 'Betrag', sortable: true, renderer: renderMoney, width: 70 },
            { dataIndex: 'versand', header: 'Versandart',	sortable: false, width: 100	},
            { dataIndex: 'sprache', header: 'Shopname', sortable: false, width: 60 },
            { dataIndex: 'bestellstatus', header: 'Bestellstatus', sortable: true, width: 120 },
            { dataIndex: 'zahlstatus', header: 'Zahlstatus', sortable: true, width: 200 },
            { dataIndex: 'zahlart', header: 'Zahlart', sortable: true, width: 100 },
            { dataIndex: 'kunde', header: 'Kunde', sortable: true, width: 100 },
            { dataIndex: 'options_klarna', header: 'Bearbeiten',  width: 120 },
            { dataIndex: 'options_delete', header: 'L&ouml;schen', renderer:rendermyoptions, sortable: true, width: 55 }
        ]);
        return MyColumn;
    }
    //render options for columns of Grid-Klarna-orders
    function rendermyoptions(value, p, r){
        var orderid = r.data.id;
        var ordernumber = r.data.bestellnr;
        if( r.data.bestellstatus_kurz=='Offen' || r.data.bestellstatus_kurz=='<span style="color:red">Komplett storniert</span>' || r.data.bestellstatus_kurz=='Komplett ausgeliefert' ||  r.data.bestellstatus_kurz=='<span style=\"color:red\">Komplett retourniert</span>'){
            return String.format("<a class=\"ico delete\" title=\"Bestellung "+ordernumber+" l&ouml;schen\" style=\"cursor:pointer;font-size:12px\" onclick=\"deleteOrder("+orderid+","+ordernumber+")\"></a>");
        }
    }
    //Create store for Grid-Klarna-orders-articles
    function createMyArticlestore(bestellnr){
        var bestellnr=bestellnr;
        var MyStore =  new Ext.data.Store({
            restful:true,
            baseParams: { myordernumber:bestellnr},
            url: '{url module=backend controller=PiPaymentKlarnaBackend action=getArticles}',
            autoLoad:true,
            reader: new Ext.data.JsonReader({
                root: 'items',
                totalProperty: 'total'
            }, [
                { name: 'ordernumber'},
                { name: 'artikel_id'},
                { name: 'bestell_nr'},
                { name: 'anzahl'},
                { name: 'name'},
                { name: 'einzelpreis'},
                { name: 'bezahlstatus'},
                { name: 'bestellstatus'},
                { name: 'bestellstatus_kurz'},
                { name: 'storniert'},
                { name: 'geliefert'},
                { name: 'offen'},
                { name: 'retourniert'},
                { name: 'stock'},
                { name: 'options_delete'},
                { name: 'customer'},
                { name: 'orderfixid'},
                { name: 'orderid'}
            ])
        });
        return MyStore;
    }
    Ext.ns('myKlarnaExt','klarnagrid','myGrid','myTab','simple','klarnahistory');
    //Create Grid with Klarna orders
    function createMyKlarnaExt(store,columns,place){
        var store=store;
        var columns=columns;
        var place=place;
        var myKlarnaExt =new Ext.extend(Ext.grid.GridPanel,
        {
            id: 'myKlarna',
            region: 'center',
            ds: store,
            cm: columns,
            renderTo:place,
            frame:true,
            stripeRows:true,
            height: 401,
            searchFilter: function(){
                if(Ext.getCmp('search').isVisible()==false){
                    var bestellvar=Ext.getCmp('bestellcombo').getValue();
                    if(bestellvar=='Alle')bestellvar="";
                    store.baseParams["search"] = bestellvar;
                }
                else{
                    store.baseParams["search"] = Ext.getCmp('search').getValue();
                }
                store.baseParams["suchenach"] = Ext.getCmp('suchenach').getValue();
                store.baseParams["nurbezahlt"] = Ext.getCmp('nurbezahlt').getValue();
                store.reload();
            },
            initComponent: function() {
                this.initTbar();
                this.on('dblclick',function(e){
                    var row = this.selModel.getSelected();
                    var orderid= row.data.id;
                    var bestellnr= row.data.bestellnr;
                    var customer= row.data.kunde;
                    orderwindow(orderid,bestellnr,customer);
                });
                myKlarnaExt.superclass.initComponent.call(this);
            },

            // inline toolbars
            initTbar: function(){
                this.bbar = new Ext.PagingToolbar({
                    pageSize: 10,
                    store: store,
                    displayInfo: true,
                    emptyMsg: "Keine Rechnungen vorhanden"
                    ,items: [
                        '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
                        'suche nach:',
                        new Ext.form.ComboBox({
                            typeAhead: true,
                            triggerAction: 'all',
                            lazyRender:true,
                            mode: 'local',
                            editable:false,
                            id: 'suchenach',
                            listeners: {
                                'select': function(g, index, ev){
                                    if(g.getValue()==4){
                                        Ext.getCmp('search').hide();
                                        Ext.getCmp('bestellcombo').show();
                                        Ext.getCmp('bestellcombo').setValue('Alle');
                                    }
                                    else{
                                        Ext.getCmp('bestellcombo').hide();
                                        Ext.getCmp('search').show();
                                    }
                                }
                            },
                            store: new Ext.data.ArrayStore({
                                id: 0,
                                fields: [
                                    'myId',
                                    'displayText'
                                ],
                                data: [[1, 'Alle'], [2, 'Bestellnr'], [3, 'Transaktions ID'], [4, 'Bestellstatus'], [5, 'Kunde']]
                            }),
                            valueField: 'myId',
                            displayField: 'displayText'
                        }),
                        '&nbsp;',
                        new Ext.form.ComboBox({
                            typeAhead: false,
                            triggerAction: 'all',
                            lazyRender:false,
                            name: 'MyCombo',
                            mode: 'local',
                            hidden : true,
                            editable: false,
                            id: 'bestellcombo',
                            store: new Ext.data.ArrayStore({
                                id: 0,
                                fields: [
                                    'myId' ,
                                    'displayText2'
                                ],
                                data: [
                                    ['Alle', 'Alle'],
                                    ['Offen', 'Offen'],
                                    ['Teilweise ausgeliefert', '<span class="orange">Teilweise ausgeliefert</span>'],
                                    ['Komplett ausgeliefert', '<span class="green">Komplett ausgeliefert</span>'],
                                    ['Komplett storniert', '<span class="red">Komplett storniert</span>'],
                                    ['Komplett retourniert', '<span class="red">Komplett retourniert</span>']
                                ]
                            }),
                            valueField: 'myId',
                            displayField: 'displayText2',
                            listeners:
                                {
                                'select': function(combo, record, index) {
                                    var text=record.data['myId'];
                                    record.data['displayText2']=record.data['myId'];
                                    Ext.form.ComboBox.superclass.setValue.call(this, text);
                                }
                            }
                        }),
                        {
                            xtype: 'textfield',
                            id: 'search',
                            enableKeyEvents : true,
                            listeners:{
                                specialkey:function(f,o){
                                    if(o.getKey()==13){
                                        myGrid.searchFilter();
                                    }
                                }
                            }
                        },
                        '&nbsp;',
                        new Ext.form.Checkbox({
                            typeAhead: true,
                            triggerAction: 'all',
                            lazyRender:true,
                            mode: 'local',
                            label: 'Zahlung akzeptiert',
                            id: 'nurbezahlt'
                        }),
                        '<span class="green">Zahlung akzeptiert</span>',
                        new Ext.Button (
                        {
                            iconCls:'sprite-magnifier',
                            text: 'Suche starten',
                            handler: function(){
                                this.searchFilter();
                            },
                            scope:this
                        })
                    ]
                })
            }
        });

        var myGrid = new myKlarnaExt;
        Ext.getCmp('suchenach').setValue(1);
        return myGrid;
    }
    //Create Article Grid
    function createMyArticlesKlarnaExt(mystore,place,bestellnr,orderid,customer){
        var mytextfield = new Ext.form.TextField({
            allowBlank: false,
            enableKeyEvents : true,
            listeners: {
                'change': function(g, index, ev){
                    loadingwindow();
                    Ext.Ajax.request({
                        url: '{url module=backend controller=PiPaymentKlarnaBackend action=addArticle}',
                        success: function(response, opts) {
                            Ext.getCmp('myloadingwindow').destroy();
                            var obj = Ext.decode(response.responseText);
                            if(obj.k_return.error==true){
                                Ext.Msg.alert('Klarnafehler', obj.k_return.errormessage);
                            }
                            else{
                                if(obj.articlename!="" && obj.articlename!=null){
                                    Ext.Msg.alert('Status', 'Folgender Artikel wurde erfolgreich zur Bestellung hinzugef&uuml;gt: <br /><center style="font-weight:bold">'+obj.articlename+'</center>');
                                    mystore.reload();
                                }
                                else{
                                    Ext.Msg.alert('Status', 'Der Artikel konnte nicht hinzugef&uuml;gt werden, da kein Artikel mit der Bestellnummer <b>\"'+obj.nofound+'\"</b> existiert.');
                                    mystore.reload();
                                }
                            }
                        },
                        failure: function(response, opts) {
                            Ext.getCmp('myloadingwindow').destroy();
                            Ext.Msg.alert('Status', 'Fehler beim hinzuf&uuml;gen des Artikels');
                        },
                        params: { myordernumber:bestellnr, myarticlenumber:index }
                    });
                },
                'keyup': function(g, index, ev){
                    var row = myArticleGrid.getSelectionModel().getSelectedCell();
                    var anzahl2 = mystore.getAt(row[0]);
                    var bestellnr=anzahl2.data.bestell_nr;
                    if(bestellnr!=''){
                        g.disable();
                    }
                }
            }
        })
        var columns = new Ext.grid.ColumnModel([
            { dataIndex: 'bestell_nr', header: 'Bestellnr', sortable: true, width: 55, editor:mytextfield},
            { dataIndex: 'name', header: 'Name.', id:'name', sortable: true, width: 90 },
            { dataIndex: 'einzelpreis', header: 'Preis', sortable: true,renderer: renderMoney, width: 55 },
            { dataIndex: 'anzahl', header: 'Anzahl', id:'bestellt', sortable: true, width: 50,
                editor: new Ext.form.NumberField({
                    allowBlank: false,
                    allowDecimals: false,
                    allowNegative: false,
                    maxValue: 100000,
                    minValue: 1,
                    listeners: {
                        'valid': function(g, index, ev){
                            var row = myArticleGrid.getSelectionModel().getSelectedCell();
                            var anzahl2 = mystore.getAt(row[0]);
                            var bestellstatus=anzahl2.data.bestellstatus;
                            var storniert=anzahl2.data.storniert;
                            var retourniert=anzahl2.data.retourniert;
                            if(g.getValue()<storniert){
                                g.setValue(storniert);
                                Ext.Msg.alert('Fehler', 'Sie k&ouml;nnen die Anzahl nicht niedriger setzen, als Artikel storniert sind');
                            }
                            if(g.getValue()<retourniert){
                                g.setValue(retourniert);
                                Ext.Msg.alert('Fehler', 'Sie k&ouml;nnen die Anzahl nicht niedriger setzen, als Artikel retourniert sind');
                            }
                            if(bestellstatus=='<span style="color:green">Komplett ausgeliefert</span>'){
                                this.disable();
                                Ext.Msg.alert('Fehler', 'Sie k&ouml;nnen die Anzahl des Artikels nicht mehr &auml;ndern wenn dieser komplett ausgeliefert ist.<br />Sie haben die M&ouml;glichkeit neue Artikel zur Bestellung hinzuzuf&uuml;gen, so lange diese nicht komplett ausgeliefert ist.');
                            }
                            else if(bestellstatus=='<span style="color:red">Komplett retourniert</span>'){
                                this.disable();
                                Ext.Msg.alert('Fehler', 'Sie k&ouml;nnen die Anzahl des Artikels nicht mehr &auml;ndern wenn dieser komplett retourniert ist.<br />Sie haben die M&ouml;glichkeit neue Artikel zur Bestellung hinzuzuf&uuml;gen, so lange diese nicht komplett ausgeliefert ist.');
                            }
                            else if(bestellstatus=='<span style="color:red">Komplett storniert</span>'){
                                this.disable();
                                Ext.Msg.alert('Fehler', 'Sie k&ouml;nnen die Anzahl des Artikels nicht mehr &auml;ndern wenn dieser komplett storniert ist.<br />Sie haben die M&ouml;glichkeit neue Artikel zur Bestellung hinzuzuf&uuml;gen, so lange diese nicht komplett ausgeliefert ist.');
                            }
                            else if(bestellstatus=='<span style="color:orange">Teilweise ausgeliefert</span>'){
                                this.disable();
                                Ext.Msg.alert('Fehler',  'Sie k&ouml;nnen die Anzahl des Artikels nicht mehr &auml;ndern wenn dieser schon zum Teil ausgeliefert ist.<br />Sie haben die M&ouml;glichkeit neue Artikel zur Bestellung hinzuzuf&uuml;gen, so lange diese nicht komplett aktiviert ist.');
                            }
                            var name=anzahl2.data.name;
                            if(name=='Zahlartenaufschlag'){
                                Ext.Msg.alert('Fehler', 'Sie k&ouml;nnen die Anzahl des Zahlartenaufschlags nicht &auml;ndern');
                            }
                            else if(name=='Versandkosten'){
                                Ext.Msg.alert('Fehler',  'Sie k&ouml;nnen die Anzahl der Versandkosten nicht &auml;ndern');
                            }
                            else if(name=='Gutschein-intern'){
                                Ext.Msg.alert('Fehler',  'Sie k&ouml;nnen die Anzahl des Gutscheins nicht &auml;ndern. F&uuml;gen sie einen neuen hinzu');
                            }
                        }
                    }
                })
            },
            { dataIndex: 'offen', header: 'Offen', width: 35 },
            { dataIndex: 'geliefert', header: 'Versendet', width: 60 },
            { dataIndex: 'storniert', header: 'Storniert', sortable: true, width: 50 },
            { dataIndex: 'retourniert', header: 'Retourniert', sortable: true, width: 65 },
            { dataIndex: 'stock', header: 'Lagerbestand', sortable: false, width: 40 },
            { dataIndex: 'bestellstatus', header: 'Bestellstatus',sortable: true, width: 120 },
            { dataIndex: 'bezahlstatus', header: 'Status', sortable: true, width: 160 },
            { dataIndex: 'options_delete', header: 'L&ouml;schen', sortable: false, width: 50, renderer:renderarticleOptions }
        ]);
        var mystore=mystore;
        var columns=columns;
        var place=place;
        var bestellnr=bestellnr;
        var mypi_klarna_counter=1;
        var anzahlen = new Array();
        var myArticleKlarnaExt =new Ext.extend(Ext.grid.EditorGridPanel,
        {
            id: 'myArticleExt'+articlepi_klarna_counter,
            region: 'center',
            ds: mystore,
            cm: columns,
            renderTo:place,
            frame:true,
            stripeRows:true,
            height:290,
            searchFilter: function(){
                mystore.baseParams["search"] = Ext.getCmp('search').getValue();
                mystore.reload();
            },
            clicksToEdit: 1,
            initComponent: function() {
                this.initTbar();
                myArticleKlarnaExt.superclass.initComponent.call(this);
            },
            initTbar: function(){
                this.tbar = [
                    {
                        text:'Speichern',
                        tooltip : 'Alle Anzahl&auml;nderungen speichern',
                        handler: function(a,b) {
                            loadingwindow();
                            var articlenr = mystore.collect('bestell_nr', true, true );
                            var newarticlenr ='';
                            var newanzahl ='';
                            for(i=0;i<articlenr.length;i++){
                                var anzahl2 = mystore.getAt(i);
                                anzahlen[i]=anzahl2.data.anzahl;
                                if(i==articlenr.length-1){
                                    newanzahl+=anzahlen[i];
                                    newarticlenr+=articlenr[i];
                                }
                                else{
                                    newarticlenr+=articlenr[i]+';';
                                    newanzahl+=anzahlen[i]+';';
                                }
                            }
                            Ext.Ajax.request({
                                url: '{url module=backend controller=PiPaymentKlarnaBackend action=updateArticleQuantity}',
                                success: function(response, opts) {
                                    Ext.getCmp('myloadingwindow').destroy();
                                    var obj = Ext.decode(response.responseText);
                                    if(obj.k_return.error==true){
                                        Ext.Msg.alert('Klarnafehler', obj.k_return.errormessage);
                                        mystore.reload();
                                    }
                                    else{
                                        Ext.Msg.alert('Status', 'Speichern erfolgreich. Neuer Rechnungsbetrag: '+obj.articlename+' Euro');
                                        mystore.reload();
                                    }
                                },
                                failure: function(response, opts) {
                                    Ext.getCmp('myloadingwindow').destroy();
                                    Ext.Msg.alert('Status', 'Fehler beim Speichern');
                                },
                                params:{ myordernumber:bestellnr, articleid:newarticlenr, quantity:newanzahl}
                            });
                        },
                        iconCls:'sprite-folder--plus'
                    },
                    '-',
                    {
                        text:'Artikel hinzuf&uuml;gen',
                        tooltip:'Neuen Artikel zur Bestellung hinzuf&uuml;gen',
                        handler : function(a,b,c){
                            var alleversendet = mystore.collect('bestellstatus', false, false );
                            if(alleversendet.length==1 && alleversendet =='<span style="color:green">Komplett ausgeliefert</span>'){
                                Ext.Msg.alert('Fehler', 'Sie k&ouml;nnen keine Artikel mehr hinzuf&uuml;gen wenn die Bestellung komplett ausgeliefert wurde');
                            }
                            else if(alleversendet.length==1 && alleversendet =='<span style="color:red">Komplett storniert</span>'){
                                Ext.Msg.alert('Fehler', 'Sie k&ouml;nnen keine Artikel mehr hinzuf&uuml;gen wenn die Bestellung komplett storniert wurde');
                            }
                            else if(alleversendet.length==1 && alleversendet =='<span style="color:red">Komplett retourniert</span>'){
                                Ext.Msg.alert('Fehler', 'Sie k&ouml;nnen keine Artikel mehr hinzuf&uuml;gen wenn die Bestellung komplett retourniert wurde');
                            }
                            else{
                                var Article = {
                                    ordernumber: bestellnr,
                                    articlenumber: '&larr;&nbsp;&larr;&nbsp;&larr;&nbsp;&larr;&nbsp;&larr;',
                                    bestell_nr: '',
                                    articleid: '',
                                    name: 'Artikel ID eingeben',
                                    price: 0,
                                    quantity: 1,
                                    bezahlstatus: 'Offen',
                                    bestellstatus: 'Offen',
                                    shippedgroup: 0,
                                    options_delete:'<a class="ico delete" style="cursor:pointer;font-size:12px" onclick="deleteArticles(\"\")">&nbsp;&nbsp;&nbsp;	</a>'
                                };
                                var recId = bestellnr; // provide unique id
                                var p = new mystore.recordType(Article, recId); // create new record
                                mystore.insert(0, p); // insert a new record into the store (also see add)
                                myArticleGrid.startEditing(0,0);
                            }
                        },
                        iconCls:'sprite-plus-circle-frame',
                        width : 150
                    },
                    '-',
                    {
                        text:'Gutschein hinzuf&uuml;gen',
                        tooltip:'Neuen Gutschein zur Bestellung hinzuf&uuml;gen',
                        handler : function(a,b,c){
                            var alleversendet = mystore.collect('bestellstatus', false, false );
                            if(alleversendet.length==1 && alleversendet =='<span style="color:green">Komplett ausgeliefert</span>'){
                                Ext.Msg.alert('Fehler', 'Sie k&ouml;nnen keine Gutscheine mehr hinzuf&uuml;gen wenn die Bestellung komplett ausgeliefert wurde');
                            }
                            else if(alleversendet.length==1 && alleversendet =='<span style="color:red">Komplett storniert</span>'){
                                Ext.Msg.alert('Fehler', 'Sie k&ouml;nnen keine Gutscheine mehr hinzuf&uuml;gen wenn die Bestellung komplett storniert wurde');
                            }
                            else if(alleversendet.length==1 && alleversendet =='<span style="color:red">Komplett retourniert</span>'){
                                Ext.Msg.alert('Fehler', 'Sie k&ouml;nnen keine Gutscheine mehr hinzuf&uuml;gen wenn die Bestellung komplett retourniert wurde');
                            }
                            else{
                                Ext.Msg.prompt('Gutschein hinzuf&uuml;gen', 'Bitte geben Sie den Betrag ein', function(btn, text){
                                    if (btn == 'ok'){
                                        loadingwindow();
                                        text=String(text);
                                        text=text.replace(",",".");
                                        text=parseFloat(text);
                                        if(text<0){
                                            text=text*(-1);
                                        }
                                        text=parseFloat(text*(-1));
                                        var randomnumber=Math.floor(Math.random()*10000);
                                        var Article = {
                                            artikel_id: 'pi_v_'+randomnumber,
                                            bestell_nr:'voucher'+randomnumber+text,
                                            name: 'Gutschein-intern',
                                            einzelpreis: text,
                                            anzahl: 1,
                                            bezahlstatus: 'Reserviert',
                                            bestellstatus: 'Offen',
                                            storniert: '0',
                                            geliefert: '0',
                                            retourniert: '0',
                                            stock: 100,
                                            options_delete:'<a class="ico delete" style="cursor:pointer;font-size:12px" onclick="deleteArticles\(\"\"\)">&nbsp;&nbsp;&nbsp;	</a>'
                                        };
                                        mypi_klarna_counter++;
                                        var recId = bestellnr; // provide unique id
                                        var p = new mystore.recordType(Article, recId); // create new record
                                        Ext.Ajax.request({
                                            url: '{url module=backend controller=PiPaymentKlarnaBackend action=addVoucher}',
                                            success: function(response, opts) {
                                                Ext.getCmp('myloadingwindow').destroy();
                                                var obj = Ext.decode(response.responseText);
                                                if(obj.k_return.error==true){
                                                    Ext.Msg.alert('Klarnafehler', obj.k_return.errormessage);
                                                    mystore.reload();
                                                }
                                                else{
                                                    mystore.insert(0, p); // insert a new record into the store (also see add)
                                                    mystore.reload();
                                                    Ext.Msg.alert('Status', 'Gutschein erfolgreich hinzugef&uuml;gt');
                                                }
                                            },
                                            failure: function(response, opts) {
                                                Ext.getCmp('myloadingwindow').destroy();
                                                Ext.Msg.alert('Status', 'Fehler beim beim hinzuf&uuml;gen des Gutscheins');
                                            },
                                            params:{ myordernumber:bestellnr, price:text, articlenumber:'voucher'+randomnumber+text, articleid:'pi_v_'+randomnumber }
                                        });
                                    }
                                });
                            }
                        },
                        iconCls:'sprite-plus-circle-frame',
                        width : 150
                    }

                ];
                this.bbar = new Ext.PagingToolbar({
                    pageSize: 8,
                    store: mystore,
                    displayInfo: true,
                    emptyMsg: "Keine Artikel vorhanden"
                    ,items: [
                        '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
                        'Artikelname oder Bestellnr suchen',
                        '-',
                        {
                            xtype: 'textfield',
                            id: 'search'
                        },
                        new Ext.Button (
                        {
                            iconCls:'magnifier',
                            text: 'Suche starten',
                            handler: function(){
                                this.searchFilter();
                            },
                            scope:this
                        })
                    ]
                })
            }
        });
        var myArticleGrid = new myArticleKlarnaExt;
        Ext.reg('klarnaArticlegrid', myArticleGrid);
        return myArticleGrid;
    }
    var statspi_klarna_counter=0;
    //Create Maintabs
    var myTabs = new Ext.TabPanel({
        region: 'center',
        renderTo: 'table',
        activeTab: 0,
        items: [
            {
                title: 'Bestell&uuml;bersicht',
                id:'mytabnr1',
                html: '<div id="mytabdiv"></div>',
                height:290,
                autoScroll : true
            },
        ]
    });
    createMyKlarnaExt(createMystore(),createMyColums(),'mytabdiv');
    // Open window with Klarna options
    function orderwindow(orderid,bestellnr,lastname){
        var piKlarnaRetoureCounter=0;
        var stornopi_klarna_counter=0;
        var historypi_klarna_counter=0;
        var articlepi_klarna_counter=0;
        var invoicepi_klarna_counter=0;
        var win;
        var orderid=orderid;
        var bestellnr=bestellnr;
        var lastname=lastname;
        var mytest= new Array();
        var mytester= new Array();
        var mytesternext= new Array();
        var mytesternextagain= new Array();
        var myhistory= new Array();
        if(!win){
            win = new Ext.Window({
                width:870,
                id: 'myorderwindow'+piKlarnaCounter,
                title: 'Klarna Payment  -  Bestellung Nr. '+bestellnr,
                height:350,
                modal:true,
                frame:true,
                closeAction:'close',
                items: new Ext.TabPanel({
                    activeTab: 0,
                    autoScroll : true,
                    id:'orderInnerTabs',
                    autoTabs : true,
                    deferredRender:false,
                    resizeTabs : true,
                    layoutOnTabChange: true,
                    items: [
                        {
                            title: 'Artikel',
                            html: '<div id="artikeldiv'+piKlarnaCounter+'"></div>',
                            height:290,
                            autoScroll : true,
                            listeners:{
                                'afterrender': function(g, index, ev){
                                    createMyArticlesKlarnaExt(createMyArticlestore(bestellnr),'artikeldiv'+piKlarnaCounter,bestellnr,orderid,lastname);
                                    articlepi_klarna_counter++;
                                },
                                'activate': function(g, index, ev){
                                    if(articlepi_klarna_counter>0){

                                        var tabwahl3=g.body.dom.children[0].id;
                                        mytesternext[articlepi_klarna_counter] = createMyArticlesKlarnaExt(createMyArticlestore(bestellnr),tabwahl3,bestellnr,orderid,lastname);
                                        var mynextnewpi_klarna_counter=articlepi_klarna_counter-1;
                                        if(articlepi_klarna_counter==0){

                                        }
                                        else{
                                            document.getElementById('myArticleExt'+mynextnewpi_klarna_counter).style.display = "none";
                                        }
                                        articlepi_klarna_counter++;
                                    }
                                }
                            }
                        },

                        {
                            title: 'Lieferung/Stornierung',
                            html: '<div id="stornodiv'+piKlarnaCounter+'"></div>',
                            height:290,
                            autoScroll : true,
                            listeners:{
                                'activate': function(g, index, ev){
                                    var tabwahl2=g.body.dom.children[0].id;
                                    mytester[stornopi_klarna_counter] = getstorno(tabwahl2,bestellnr);
                                    var mynewpi_klarna_counter=stornopi_klarna_counter-1;
                                    if(stornopi_klarna_counter==0){

                                    }
                                    else{
                                        document.getElementById('myStornoExt'+mynewpi_klarna_counter).style.display = "none";
                                    }
                                    stornopi_klarna_counter++;
                                }
                            }
                        },
                        {
                            title: 'Retoure',
                            html: '<div id="reservierdiv'+piKlarnaCounter+'"></div>',
                            height:290,
                            autoScroll : true,
                            listeners:{
                                'activate': function(g, index, ev){
                                    var tabwahl=g.body.dom.children[0].id;
                                    mytest[piKlarnaRetoureCounter] = getretoure(tabwahl,bestellnr);
                                    var newpi_klarna_counter23=piKlarnaRetoureCounter-1;
                                    if(piKlarnaRetoureCounter==0){

                                    }
                                    else{
                                        document.getElementById('myRetoureExt'+newpi_klarna_counter23).style.display = "none";
                                    }
                                    piKlarnaRetoureCounter++;
                                }
                            }
                        },
                        {
                            title: 'Belege',
                            html: '<div id="belegdiv'+piKlarnaCounter+'"></div>',
                            height:290,
                            autoScroll : true,
                            listeners:{
                                'activate': function(g, index, ev){
                                    var tabwahlthis=g.body.dom.children[0].id;
                                    mytesternextagain[invoicepi_klarna_counter] = getinvoices(tabwahlthis,bestellnr);
                                    var newpi_klarna_counternext=invoicepi_klarna_counter-1;
                                    if(invoicepi_klarna_counter==0){

                                    }
                                    else{
                                        document.getElementById('myInvoiceExt'+newpi_klarna_counternext).style.display = "none";
                                    }
                                    invoicepi_klarna_counter++;
                                }
                            }
                        },
                        {
                            title: 'Historie',
                            html: '<div id="historiediv'+piKlarnaCounter+'"></div>',
                            height:290,
                            autoScroll : true,
                            listeners:{
                                'activate': function(g, index, ev){
                                    var tabwahl3=g.body.dom.children[0].id;
                                    myhistory[historypi_klarna_counter] = gethistory(tabwahl3,bestellnr);
                                    var newpi_klarna_counter2=historypi_klarna_counter-1;
                                    if(historypi_klarna_counter==0){

                                    }
                                    else{
                                        document.getElementById('myHistoryExt'+newpi_klarna_counter2).style.display = "none";
                                    }
                                    historypi_klarna_counter++;
                                }
                            }

                        }

                    ]
                })
            });
        }
        win.show(this);
        win.on('beforedestroy',function(){
            Ext.getCmp('myKlarna').store.reload();

        });
        piKlarnaCounter++;
    }
    //send/storno grid
    function getstorno(place,orderid){
        var orderid=orderid;
        var dsstore =  new Ext.data.Store({
            baseParams: { myordernumber:orderid},
            url: '{url module=backend controller=PiPaymentKlarnaBackend action=getSendAndCancelArticles}',
            autoLoad:true,
            reader: new Ext.data.JsonReader({
                root: 'items',
                totalProperty: 'total'
            }, [
                { name: 'ordernumber'},
                { name: 'artikel_id'},
                { name: 'bestell_nr'},
                { name: 'name'},
                { name: 'anzahl'},
                { name: 'einzelpreis'},
                { name: 'gesamtpreis'},
                { name: 'bestellt'},
                { name: 'offen'},
                { name: 'geliefert'},
                { name: 'storniert'},
                { name: 'retourniert'},
                { name: 'accepted', id: 'accepted'},
            ])
        });
        var selectionpi_klarna_counter=0;
        var cmcolumns = new Ext.grid.ColumnModel([
            { dataIndex: 'anzahl', header: 'Anzahl', width: 40, id: 'articleid' ,
                editor: new Ext.form.NumberField({
                    allowBlank: false,
                    allowDecimals: false,
                    allowNegative: false,
                    enableKeyEvents : true,
                    minValue: 0	,
                    listeners: {
                        'keyup': function(g, index, ev){
                            var mycell = myStorno.getSelectionModel().getSelectedCell();
                            var myrow=mycell[0];
                            var anzahl2 = dsstore.getAt(myrow);
                            var anzahl=anzahl2.data.offen;
                            if(g.getValue()>anzahl){
                                g.setValue(anzahl);
                                Ext.Msg.alert('Fehler', 'Sie k&ouml;nnen nicht mehr Artikel versenden als noch offen sind');
                            }
                            selectionpi_klarna_counter++;
                        }
                    }
                })
            },
            { dataIndex: 'bestell_nr', header: 'Bestellnr.', sortable: true, width: 100},
            { dataIndex: 'name', header: 'Name', sortable: true, width: 250 },
            { dataIndex: 'einzelpreis',renderer: renderMoney, header: 'Einzelpreis', width: 70 },
            { dataIndex: 'gesamtpreis',renderer: renderMoney, header: 'Gesamtpreis', sortable: true, width: 75 },
            { dataIndex: 'bestellt', header: 'Bestellt', width: 55 },
            { dataIndex: 'offen', header: 'Offen', width: 40 },
            { dataIndex: 'geliefert', header: 'Versendet', sortable: true, width: 60 },
            { dataIndex: 'storniert', header: 'Storniert', sortable: true, width: 65 },
            { dataIndex: 'retourniert', header: 'Retourniert', sortable: true, width: 65 },
        ]);
        var myKlarnaStornoExt =new Ext.extend(Ext.grid.EditorGridPanel,
        {
            ds: dsstore,
            cm: cmcolumns,
            renderTo:place,
            frame:true,
            id:'myStornoExt'+stornopi_klarna_counter,
            stripeRows:true,
            height:290,
            searchFilter: function(){
                dsstore.baseParams["search"] = Ext.getCmp('search').getValue();
                dsstore.reload();
            },
            clicksToEdit: 1,
            initComponent: function() {
                this.initTbar();
                myKlarnaStornoExt.superclass.initComponent.call(this);
            },
            initTbar: function(){
                this.tbar = [
                    {
                        text:'Auswahl versenden',
                        tooltip:'Versenden Sie die Auswahl und aktivieren Sie so die Rechnung',
                        handler: function(btn, ev) {
                            var alle=0;
                            var anzahl = dsstore.collect('anzahl', true, true );
                            var articlenr = dsstore.collect('bestell_nr', true, true );
                            var bestellt = dsstore.collect('bestellt', true, true );
                            for(i=0;i<dsstore.getCount();i++){
                                if(anzahl[i]==bestellt[i]){
                                    alle+=1;
                                }
                                var anzahl2 = dsstore.getAt(i);
                                anzahl[i]=anzahl2.data.anzahl;
                            }
                            var newarticlenr ='';
                            var newanzahl ='';
                            for(i=0;i<articlenr.length;i++){
                                if(i==articlenr.length-1){
                                    newanzahl+=anzahl[i];
                                    newarticlenr+=articlenr[i];
                                }
                                else{
                                    newarticlenr+=articlenr[i]+';';
                                    newanzahl+=anzahl[i]+';';
                                }
                            }

                            Ext.Msg.confirm('Artikel Versenden', 'Artikel wirklich versenden?', function(btn){
                                if (btn == 'yes'){
                                    loadingwindow();
                                    Ext.Ajax.request({
                                        url: '{url module=backend controller=PiPaymentKlarnaBackend action=sendArticles}',
                                        success: function(response, opts) {
                                            Ext.getCmp('myloadingwindow').destroy();
                                            var obj = Ext.decode(response.responseText);
                                            if(obj.k_return.error==true){
                                                if(!obj.k_return.errormessage) obj.k_return.errormessage="Unbekannter Fehler";
                                                Ext.Msg.alert('Klarnafehler', obj.k_return.errormessage);
                                            }
                                            else{
                                                dsstore.reload();
                                                Ext.Msg.alert('Status','Erfolgreich versendet. Der Bestellstatus der versendeten Artikel wurde ge&auml;ndert. Die Rechnung wurde erstellt');
                                            }
                                        },
                                        failure: function(response, opts) {
                                            Ext.getCmp('myloadingwindow').destroy();
                                            Ext.Msg.alert('Status', 'Fehler beim versenden der Artikels');
                                        },
                                        params: { articlenr:newarticlenr , anzahl:newanzahl, myordernumber:orderid, send:'true'}
                                    });
                                }
                            });

                        },
                        iconCls:'sprite-plus-circle-frame'
                    },
                    '-',
                    {
                        text:'Auswahl stornieren',
                        tooltip:'Stornieren sie Die ausgew&auml;hlten Artikel. Der Betrag wird von der Reservierung abgezogen.',
                        handler: function(btn, ev) {
                            var alle=0;
                            var alleartikel=0;
                            var anzahl = dsstore.collect('anzahl', true, true );
                            var articlenr = dsstore.collect('bestell_nr', true, true );
                            var bestellt = dsstore.collect('bestellt', true, true );
                            var offen = dsstore.collect('offen', true, true );
                            for(i=0;i<dsstore.getCount();i++){
                                if(anzahl[i]==bestellt[i]){
                                    alle+=1;
                                }
                                if(anzahl[i]==offen[i]){
                                    alleartikel+=1;
                                }
                                var anzahl2 = dsstore.getAt(i);
                                anzahl[i]=anzahl2.data.anzahl;
                            }
                            var newarticlenr ='';
                            var newanzahl ='';
                            for(i=0;i<articlenr.length;i++){
                                if(i==articlenr.length-1){
                                    newanzahl+=anzahl[i];
                                    newarticlenr+=articlenr[i];
                                }
                                else{
                                    newarticlenr+=articlenr[i]+';';
                                    newanzahl+=anzahl[i]+';';
                                }
                            }
                            if(alleartikel==dsstore.getCount()){
                                Ext.Msg.confirm('Warnung', 'Wenn Sie alle noch offenen Artikel stornieren wird die Reservierung bei Klarna gel&ouml;scht und kann somit nicht mehr bearbeitet werden. Sind Sie sicher das Sie das m&ouml;chten?', function(btn){
                                    if (btn == 'yes'){
                                        loadingwindow();
                                        Ext.Ajax.request({
                                            url: '{url module=backend controller=PiPaymentKlarnaBackend action=cancelArticles}',
                                            success: function(response, opts) {
                                                Ext.getCmp('myloadingwindow').destroy();
                                                var obj = Ext.decode(response.responseText);
                                                if(obj.k_return.error==true){
                                                    Ext.Msg.alert('Klarnafehler', obj.k_return.errormessage);
                                                }
                                                else{
                                                    dsstore.reload();
                                                    if(obj.komplett==true){
                                                        Ext.Msg.alert('Reservierung gel&ouml;scht', 'Da alle Artikel storniert wurden, wurde die Reservierung bei Klarna gel&ouml;scht');
                                                    }
                                                    else{
                                                        Ext.Msg.alert('Status', 'Die Artikel wurden erfolgreich storniert');
                                                    }
                                                }
                                            },
                                            failure: function(response, opts) {
                                                Ext.getCmp('myloadingwindow').destroy();
                                                Ext.Msg.alert('Status', 'Fehler beim stornieren der Artikel');
                                            },
                                            params: { articlenr:newarticlenr , anzahl:newanzahl, myordernumber:orderid, storno:'true'}
                                        });
                                    }
                                });
                            }
                            else{
                                Ext.Msg.confirm('Artikel stornieren', 'Artikel wirklich stornieren?', function(btn){
                                    if (btn == 'yes'){
                                        loadingwindow();
                                        Ext.Ajax.request({
                                            url: '{url module=backend controller=PiPaymentKlarnaBackend action=cancelArticles}',
                                            success: function(response, opts) {
                                                Ext.getCmp('myloadingwindow').destroy();
                                                var obj = Ext.decode(response.responseText);
                                                if(obj.k_return.error==true){
                                                    Ext.Msg.alert('Klarnafehler', obj.k_return.errormessage);
                                                }
                                                else{
                                                    dsstore.reload();
                                                    if(obj.komplett==true){
                                                        Ext.Msg.alert('Reservierung gel&ouml;scht', 'Da alle Artikel storniert wurden, wurde die Reservierung bei Klarna gel&ouml;scht');
                                                    }
                                                    else{
                                                        Ext.Msg.alert('Status', 'Die Artikel wurden erfolgreich storniert');
                                                    }
                                                }
                                            },
                                            failure: function(response, opts) {
                                                Ext.getCmp('myloadingwindow').destroy();
                                                Ext.Msg.alert('Status', 'Fehler beim stornieren der Artikel');
                                            },
                                            params: { articlenr:newarticlenr , anzahl:newanzahl, myordernumber:orderid, storno:'true'}
                                        });
                                    }
                                });
                            }
                        },
                        iconCls:'sprite-minus-circle-frame'
                    },
                    '<div style="margin-left:170px">Artikelname oder Bestellnr suchen</div>',
                    '-',
                    {
                        xtype: 'textfield',
                        id: 'search'
                    },
                    new Ext.Button (
                    {
                        iconCls:'sprite-magnifier',
                        text: 'Suche starten',
                        handler: function(){
                            this.searchFilter();
                        },
                        scope:this
                    })
                ];
            }
        });
        var myStorno = new myKlarnaStornoExt;
        Ext.reg('klarnahistory', myStorno);
        return myStorno;
    }
    //Invoices Grid
    function getinvoices(place,orderid){
        var orderid=orderid;
        var dsstore3 =  new Ext.data.Store({
            baseParams: { myordernumber:orderid},
            id: 'dsstore3',
            url: '{url module=backend controller=PiPaymentKlarnaBackend action=getInvoices}',
            autoLoad:true,
            reader: new Ext.data.JsonReader({
                root: 'items',
                totalProperty: 'total'
            }, [
                { name: 'id'},
                { name: 'date'},
                { name: 'method'},
                { name: 'order_number'},
                { name: 'invoice_number'},
                { name: 'invoice_amount'},
                { name: 'open'},
                { name: 'pi_klarna_liveserver'}
            ])
        });
        var invoicecolumns = new Ext.grid.ColumnModel([
            { dataIndex: 'id', header: 'ID', width: 30 },
            { dataIndex: 'date', header: 'Erstellungsdatum.', sortable: true, width: 120},
            { dataIndex: 'method', header: 'Rechnungsstatus', sortable: true, width: 180 },
            { dataIndex: 'invoice_number', header: 'Rechnungsnummer', sortable: true, width: 150 },
            { dataIndex: 'invoice_amount', header: 'Rechnungsbetrag',renderer:renderMoney, sortable: true, width: 150 },
            { dataIndex: 'order_number',header: 'Optionen', sortable: true, width: 150,renderer: renderOptions },
        ]);
        var myKlarnaInvoiceExt =new Ext.extend(Ext.grid.EditorGridPanel,
        {
            ds: dsstore3,
            cm: invoicecolumns,
            renderTo:place,
            frame:true,
            stripeRows:true,
            id:'myInvoiceExt'+invoicepi_klarna_counter,
            height:290,
            clicksToEdit: 1,
            initComponent: function() {
                myKlarnaInvoiceExt.superclass.initComponent.call(this);
            }
        });
        var myInvoice = new myKlarnaInvoiceExt;
        Ext.reg('myInvoice', myInvoice);
        return myInvoice;
    }
    //Delete Invoice
    function deleteInvoice(ordernumber,invoice){
        Ext.Msg.confirm('Warnung', 'Sind Sie sicher das sie die Rechnung l&ouml;schen wollen? Sie haben weiterhin die M&ouml;glichkeit diese im internen Klarnabereich zu bearbeiten.', function(btn){
            if (btn == 'yes'){
                loadingwindow();
                Ext.Ajax.request({
                    url: '{url module=backend controller=PiPaymentKlarnaBackend action=deleteInvoice}',
                    success: function(response, opts) {
                        Ext.getCmp('myloadingwindow').destroy();
                        var obj = Ext.decode(response.responseText);
                        Ext.Msg.alert('Status', 'Die Rechnung wurde erfolgreich gel&ouml;scht. Sobald Sie den Reiter aktualisieren wird die &auml;nderungen sichtbar.');
                    },
                    failure: function(response, opts) {
                        Ext.getCmp('myloadingwindow').destroy();
                        Ext.Msg.alert('Status', 'Fehler beim l&ouml;schen der Rechnung');
                    },
                    params: { ordernumber:ordernumber, invoice:invoice }
                });
            }
        });
    }
    //Render articleoptions
    function renderarticleOptions(value, p, r){
        var articlenumber = r.data.bestell_nr;
        var orderid = r.data.orderid;
        var ordernumber = r.data.ordernumber;
        var bestellstatus_kurz= r.data.bestellstatus_kurz;
        var customer = r.data.customer;
        var orderfixid = r.data.orderfixid;
        if(bestellstatus_kurz !='Offen'){
            return String.format('&nbsp;');
        }
        else{
            return String.format(
            '<a class="ico delete" style="cursor:pointer;font-size:12px; height:15px;" onclick="deleteArticles(\''+articlenumber+'\','+ordernumber+','+orderid+',\''+customer+'\','+orderfixid+',\''+bestellstatus_kurz+'\')">&nbsp;&nbsp;&nbsp;</a>');
        }
    }
    //delete articles
    function deleteArticles(articlenumber,ordernumber,orderid,customer,orderfixid,bestellstatus){
        if(bestellstatus !='Offen'){
            Ext.Msg.alert('Fehler', 'Sie k&ouml;nnen keine Artikel l&ouml;schen die schon bearbeitet wurden');
        }
        else {
            Ext.Msg.confirm('Artikel L&ouml;schen', 'Artikel wirklich aus der Bestellung entfernen?', function(btn){
                if (btn == 'yes'){
                    loadingwindow();
                    Ext.Ajax.request({
                        url: '{url module=backend controller=PiPaymentKlarnaBackend action=deleteArticle}',
                        success: function(response, opts) {
                            Ext.getCmp('myloadingwindow').destroy();
                            var obj = Ext.decode(response.responseText);
                            if(obj.k_return.error==true){
                                Ext.Msg.alert('Klarnafehler', obj.k_return.errormessage);
                            }
                            else{
                                Ext.Ajax.request({
                                    url: '../engine/backend/ajax/orderes.php',
                                    params: 	{
                                        id: articlenumber,
                                        s_order_details_id:orderfixid,
                                        action:'deletePositionForExt'
                                    },
                                    success: function(){
                                    }
                                });
                                Ext.getCmp('myorderwindow'+(piKlarnaCounter-1)).destroy();
                                orderwindow(orderid,ordernumber,customer);
                                Ext.Msg.alert('Status', 'Folgender Artikel wurde erfolgreich aus der Bestellung entfernt: <br /><center style="font-weight:bold">'+obj.articlename+'</center>');
                            }
                        },
                        failure: function(response, opts) {
                            Ext.getCmp('myloadingwindow').destroy();
                            Ext.Msg.alert('Status', 'Fehler beim entfernen des Artikels');
                        },
                        params: { myordernumber:ordernumber, myarticlenumber:articlenumber }
                    });
                }
            });
        }
    }
    //Send Invoice per Mail
    function sendInvoiceMail(ordernumber,invoice){
        Ext.Msg.confirm('Status', 'Klarna anweisen, die Rechnung als Mail an den Kunden zu schicken?', function(btn){
            if (btn == 'yes'){
                loadingwindow();
                Ext.Ajax.request({
                    url: '{url module=backend controller=PiPaymentKlarnaBackend action=sendInvoiceMail}',
                    success: function(response, opts) {
                        Ext.getCmp('myloadingwindow').destroy();
                        Ext.Msg.alert('Status', 'Die E-mail wurde erfolgreich an den Kunden geschickt');
                    },
                    failure: function(response, opts) {
                        Ext.getCmp('myloadingwindow').destroy();
                        Ext.Msg.alert('Status', 'Fehler beim senden der Anfrage');
                    },
                    params: { ordernumber:ordernumber, invoice:invoice }
                });
            }
        });
    }
    //Send Invoice per Post
    function sendInvoicePost(ordernumber,invoice){
        Ext.Msg.confirm('Warnung', 'Klarna stellt Ihnen den Rechnungsversand per Post in Rechnung. Sind Sie wirklich sicher das sie die Rechnung per Post schicken wollen?', function(btn){
            if (btn == 'yes'){
                loadingwindow();
                Ext.Msg.confirm('Best&auml;tigung', 'Bitte best&auml;tigen Sie nochmals das Ihnen bewusst ist, das mit dieser Funktion Kosten auf Sie zu kommen. Rechnung wirklich per Post verschicken?', function(btn2){
                    if (btn2 == 'yes'){
                        Ext.Ajax.request({
                            url: '{url module=backend controller=PiPaymentKlarnaBackend action=sendInvoicePost}',
                            success: function(response, opts) {
                                Ext.getCmp('myloadingwindow').destroy();
                                var obj = Ext.decode(response.responseText);
                                Ext.Msg.alert('Status', 'Die Anfrage wurde erfolgreich an Klarna geschickt');
                            },
                            failure: function(response, opts) {
                                Ext.getCmp('myloadingwindow').destroy();
                                Ext.Msg.alert('Status', 'Fehler beim senden der Anfrage');
                            },
                            params: { ordernumber:ordernumber, invoice:invoice }
                        });
                    }
                });
            }
        });
    }

    //Retoure grid
    function getretoure(place,orderid){
        var orderid=orderid;
        var dsstore2 =  new Ext.data.Store({
            baseParams: { myordernumber:orderid},
            url: '{url module=backend controller=PiPaymentKlarnaBackend action=getReturnArticles}',
            autoLoad:true,
            reader: new Ext.data.JsonReader({
                root: 'items',
                totalProperty: 'total'
            }, [
                { name: 'order_number'},
                { name: 'artikel_id'},
                { name: 'bestell_nr'},
                { name: 'name'},
                { name: 'anzahl'},
                { name: 'geliefert'},
                { name: 'einzelpreis'},
                { name: 'gesamtpreis'},
                { name: 'invoice_number'}
            ])
        });
        var cmcolumns = new Ext.grid.ColumnModel([
            { dataIndex: 'anzahl', header: 'Anzahl', width: 45, id: 'articleid' ,
                editor: new Ext.form.NumberField({
                    allowBlank: false,
                    allowDecimals: false,
                    allowNegative: false,
                    enableKeyEvents : true,
                    minValue: 0	,
                    listeners: {
                        'keyup': function(g, index, ev){
                            var mycell = myRetoure.getSelectionModel().getSelectedCell()
                            var myrow=mycell[0];
                            var anzahl2 = dsstore2.getAt(myrow);
                            var anzahl=anzahl2.data.geliefert;
                            if(g.getValue()>anzahl){
                                g.setValue(anzahl);
                                Ext.Msg.alert('Fehler', 'Sie k&ouml;nnen nicht mehr mehr Artikel retournieren als versendet sind');
                            }

                        }

                    }
                })
            },
            { dataIndex: 'bestell_nr', header: 'Bestellnr.', sortable: true, width: 120},
            { dataIndex: 'name', header: 'Name', sortable: true, width: 180 },
            { dataIndex: 'einzelpreis',renderer: renderMoney, header: 'Einzelpreis', width: 70 },
            { dataIndex: 'gesamtpreis',renderer: renderMoney, header: 'Gesamtpreis', width: 78 },
            { dataIndex: 'geliefert', header: 'Ausgeliefert', width: 80 },
            { dataIndex: 'invoice_number', header: 'Rechnungsnummer', sortable: true, width: 120 }
        ]);

        var myKlarnaRetoureExt =new Ext.extend(Ext.grid.EditorGridPanel,
        {
            ds: dsstore2,
            cm: cmcolumns,
            renderTo:place,
            frame:true,
            stripeRows:true,
            searchFilter: function(){
                dsstore2.baseParams["search"] = Ext.getCmp('search').getValue();
                dsstore2.reload();
            },
            id:'myRetoureExt'+piKlarnaRetoureCounter,
            height:290,
            clicksToEdit: 1,
            initComponent: function() {
                this.initTbar();
                myKlarnaRetoureExt.superclass.initComponent.call(this);
            },
            initTbar: function(){
                this.tbar = [
                    {
                        tooltip: 'Die Auswahl retournieren. Der Betrag wird von der Rechnung abgezogen.',
                        text:'Auswahl retournieren',
                        handler: function(btn, ev) {
                            var rechnungsnr=new Array();
                            var articlenr=new Array();
                            var anzahl=new Array();
                            var mystorepi_klarna_counter=dsstore2.getCount()
                            var j=0;
                            for(i=0;i<mystorepi_klarna_counter;i++){
                                var storeindex = dsstore2.getAt(i);
                                if(storeindex.data.anzahl>0){
                                    rechnungsnr[j] = storeindex.data.invoice_number;
                                    anzahl[j]=storeindex.data.anzahl;
                                    articlenr[j] = storeindex.data.bestell_nr;
                                    j++;
                                }
                            }
                            var newarticlenr ='';
                            var newanzahl ='';
                            var newrechnungsnr ='';
                            for(i=0;i<articlenr.length;i++){
                                if(i==articlenr.length-1){
                                    if(anzahl[i]!=0){
                                        newanzahl+=anzahl[i];
                                        newarticlenr+=articlenr[i];
                                        newrechnungsnr+=rechnungsnr[i];
                                    }
                                }
                                else{
                                    if(anzahl[i]!=0){
                                        newarticlenr+=articlenr[i]+';';
                                        newanzahl+=anzahl[i]+';';
                                        newrechnungsnr+=rechnungsnr[i]+';';
                                    }
                                }
                            }
                            Ext.Msg.confirm('Artikel retournieren', 'Artikel wirklich retournieren?', function(btn){
                                if (btn == 'yes'){
                                    loadingwindow();
                                    Ext.Ajax.request({
                                        url: '{url module=backend controller=PiPaymentKlarnaBackend action=returnArticles}',
                                        success: function(response, opts) {
                                            Ext.getCmp('myloadingwindow').destroy();
                                            var obj = Ext.decode(response.responseText);
                                            if(obj.items.error==true){
                                                Ext.Msg.alert('Status', obj.items.errormessage);
                                                dsstore2.reload();
                                            }
                                            else{
                                                Ext.Msg.alert('Status', 'Die Artikel wurden erfolgreich retourniert');
                                                dsstore2.reload();
                                            }
                                        },
                                        failure: function(response, opts) {
                                            Ext.getCmp('myloadingwindow').destroy();
                                            Ext.Msg.alert('Status', 'Fehler beim retournieren der Artikel');
                                            dsstore2.reload();
                                        },
                                        params: { articlenr:newarticlenr , anzahl:newanzahl,rechnungsnr:newrechnungsnr, myordernumber:orderid }
                                    });
                                }
                            });

                        },
                        iconCls:'sprite-plus-circle-frame'
                    },
                    '<div class="Klarna_backend_margin_left">Rechnungsnummer suchen</div>',
                    '-',
                    {
                        xtype: 'textfield',
                        id: 'search'
                    },
                    new Ext.Button (
                    {
                        iconCls:'sprite-magnifier',
                        text: 'Suche starten',
                        handler: function(){
                            this.searchFilter();
                        },
                        scope:this
                    }),
                ];
            }
        });
        var myRetoure = new myKlarnaRetoureExt;
        Ext.reg('myRetoure', myRetoure);
        return myRetoure;
    }

    //get History for order
    function gethistory(place,orderid){
        var orderid=orderid;
        var dsstore =  new Ext.data.Store({
            url: '{url action=getHistory}',
            autoLoad:true,
            sortInfo: {
                field: 'date',
                direction: 'ASC' // or 'DESC' (case sensitive for local sorting)
            },
            baseParams: { myordernumber:orderid },
            reader: new Ext.data.JsonReader({
                root: 'items',
                totalProperty: 'total'
            }, [
                { name: 'id'},
                { name: 'date'},
                { name: 'event'},
                { name: 'name'},
                { name: 'bestellnr'},
                { name: 'anzahl'}
            ])
        });
        var cmcolumns = new Ext.grid.ColumnModel([
            { dataIndex: 'id', header: 'ID', width: 50 },
            { dataIndex: 'date', header: 'Datum', sortable: true, width: 130 },
            { dataIndex: 'event', header: 'Event', sortable: true, width: 250 },
            { dataIndex: 'name', header: 'Name', sortable: true, width: 150 },
            { dataIndex: 'bestellnr', header: 'Bestellnr.', sortable: true, width: 120 },
            { dataIndex: 'anzahl', header: 'Anzahl', sortable: true, width: 100 },
        ]);
        var myKlarnaHistoryExt =new Ext.extend(Ext.grid.GridPanel,
        {
            ds: dsstore,
            cm: cmcolumns,
            renderTo:place,
            frame:true,
            id:'myHistoryExt'+historypi_klarna_counter,
            height:290,
            initComponent: function() {
                myKlarnaHistoryExt.superclass.initComponent.call(this);
            }
        });
        var myHistory = new myKlarnaHistoryExt;
        Ext.reg('klarnahistory', myHistory);
        return myHistory;
    }
    //render currency
    function renderMoney(value,p,record){
        var wert = value;
        return wert + ' &euro;';
    };

    //loading window
    function loadingwindow(){
	var win2;
	if(!win2){
            win2 = new Ext.Window({
                id: 'myloadingwindow',
                title: 'Vorgang wird durchgef&uuml;hrt...',
                height:100,
                width:200,
                modal:true,
                frame:true,
                closable :false,
                html:'<div class="Klarna_loading">&nbsp;</div>'
            })
	}
	win2.show(this);
	win2.on('beforedestroy',function(){
		Ext.getCmp('myKlarna').store.reload();
	});
    }


    //delete order(s)
    function deleteOrder(orderId,ordernumber){
        Ext.Msg.confirm('Bestellung L&ouml;schen', 'Wollen Sie die Bestellung mit der Bestellnr. '+ordernumber+' wirklich l&ouml;schen?', function(btn){
            if (btn == 'yes'){
                document.deleteOneForm.deleteOne.value=orderId;
                document.deleteOneForm.ordernumber.value=ordernumber;
                document.deleteOneForm.submit();
            }
        });
    };
    function pdfpreview(ordernumber){
        document.previewForm.action="{url module=backend controller=PiPaymentKlarnaBackend action=showPdf}?pdf="+ordernumber;
        document.previewForm.submit();
    }
    //Render Options for Invoice Grid
    function renderOptions(value, p, r){
        var invoice = r.data.invoice_number;
        var method = r.data.method;
        var ordernumber = r.data.order_number;
        if(method!="<span style='color:red'>Retoure Rechnung</span>"){
            return String.format(
            '<a style="height: 16px; width: 16px !important; display: inline-block;" class="sprite-printer" title="Rechnung anschauen/speichern" onclick=\"pdfpreview(\''+invoice+'\')\" style="cursor:pointer">&nbsp;</a>' +
                '<a  style="height: 16px; width: 16px !important; display: inline-block;" class="sprite-envelope--arrow" onclick="sendInvoiceMail('+ordernumber+','+invoice+')" title="Rechnung per E-mail an den Kunden schicken." style="cursor:pointer">&nbsp;</a>' +
                '<a  style="height: 16px; width: 16px !important; display: inline-block;" class="sprite-home--arrow" onclick="sendInvoicePost('+ordernumber+','+invoice+')" title="Rechnung per Post an den Kunden schicken." style="cursor:pointer">&nbsp;</a>' +
                '<a  style="height: 16px; width: 16px !important; display: inline-block;" class="sprite-minus-circle-frame" onclick="deleteInvoice('+ordernumber+','+invoice+')" title="Rechnung l&ouml;schen" style="cursor:pointer">&nbsp;</a>'
        );
        }
        else{
            invoice=String(invoice+'_retoure');
            return String.format(
            '<a style="height: 16px; width: 16px !important; display: inline-block;"  class="sprite-printer" title="Rechnung anschauen/speichern" onclick=\"pdfpreview(\''+invoice+'\')\" style="cursor:pointer"></a>' +
                '<a style="height: 16px; width: 16px !important; display: inline-block;" class="sprite-envelope--arrow" onclick="sendInvoiceMail('+ordernumber+','+invoice+')" title="Rechnung per E-mail an den Kunden schicken." style="cursor:pointer"></a>' +
                '<a style="height: 16px; width: 16px !important; display: inline-block;" class="sprite-home--arrow" onclick="sendInvoicePost('+ordernumber+','+invoice+')" title="Rechnung per Post an den Kunden schicken." style="cursor:pointer"></a>' +
                '<a style="height: 16px; width: 16px !important; display: inline-block;" class="sprite-minus-circle-frame" onclick="deleteInvoice('+ordernumber+','+invoice+')" title="Rechnung l&ouml;schen" style="cursor:pointer"></a>'
        );
        }
    }

    //Create Viewport
    (function(){
        View = Ext.extend(Ext.Viewport, {
            layout: 'border',
            id: 'testid',
            renderTo: 'table',
            initComponent: function() {
                this.items = [myTabs];
                View.superclass.initComponent.call(this);
            }
        });
        myTab.View = View;
    })();;
    //go
    Ext.onReady(function(){
        Ext.QuickTips.init();
        myTab = new myTab.View();
    });
</script>
{/block}