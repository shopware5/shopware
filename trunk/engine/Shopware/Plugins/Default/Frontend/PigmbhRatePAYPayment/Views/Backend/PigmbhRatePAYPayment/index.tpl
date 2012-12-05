{extends file="backend/index/parent.tpl"}
{block name="backend_index_css" append}
<!-- Common CSS -->
<link href="/templates/_default/backend/_resources/resources/css/icon-set.css" type="text/css" rel="stylesheet">
<link href="{link file='engine/backend/css/modules.css'}" rel="stylesheet" type="text/css" />
<link href="{link file='engine/Shopware/Plugins/Default/Frontend/PigmbhRatePAYPayment/css/styles.css'}" rel="stylesheet" type="text/css" />
<style type="text/css">
    [class*=sprite] {
        width : auto !important;
    }
</style>
{/block}

{block name="backend_index_body_inline"}
<form id="previewForm" name="previewForm" action="" target="_blank" method="post">
    <input type="hidden" name="temp" value="1">
</form>

<div id="table"></div>

<script>

    var pi_RatePAY_counter=0;
    var retourepi_RatePAY_counter=0;
    var stornopi_RatePAY_counter=0;
    var historypi_RatePAY_counter=0;
    var logpi_RatePAY_counter=0;
    var articlepi_RatePAY_counter=0;
    var invoicepi_RatePAY_counter=0 ;
    var rateDetailpi_RatePAY_counter=0;
    //Create store for Grid-RatePAY-orders
    function createMystore(){
        var MyStore =  new Ext.data.Store({
            url: '{url action=getOrders}',
            reader: new Ext.data.JsonReader({
                root: 'items',
                totalProperty: 'total'
            }, [
                { name: 'checkbox'},
                { name: 'RatePAYid'},
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
                { name: 'zahlstatus_kurz'},
                { name: 'zahlart'},
                { name: 'userid'},
                { name: 'kunde'},{ name: 'lastname'},
                { name: 'options_work'},
                { name: 'options_delete'},
                { name: 'options_RatePAY'}
            ])
        });
        MyStore.load();
        return MyStore;
    }
    //Create columns for Grid-RatePAY-orders
    function createMyColums(){
        var MyColumn = new Ext.grid.ColumnModel([
            { dataIndex: 'RatePAYid', header: 'Nr.', sortable: true, width: 25 },
            { dataIndex: 'bestellzeit', header: 'Bestellzeit', sortable: true, width: 120 },
            { dataIndex: 'bestellnr', header: 'Bestellnr.', sortable: true, width: 60 },
            { dataIndex: 'transaktion', header: 'Transaktions ID', sortable: true, width: 110 },
            { dataIndex: 'betrag', header: 'Betrag', sortable: true, renderer: renderMoney, width: 70 },
            { dataIndex: 'versand', header: 'Versandart',	sortable: false, width: 120	},
            { dataIndex: 'sprache', header: 'Sprache', sortable: false, width: 60 },
            { dataIndex: 'bestellstatus', header: 'Bestellstatus', sortable: true, width: 120 },
            { dataIndex: 'zahlstatus', header: 'Zahlstatus', sortable: true, width: 200 },
            { dataIndex: 'kunde', header: 'Kunde', sortable: true, width: 120 },
            { dataIndex: 'zahlart', header: 'Zahlart', sortable: true, width: 85 },
            { dataIndex: 'options_RatePAY', header: 'Optionen', renderer:rendermyoptions,  width: 60 },
        ]);
        return MyColumn;
    }
    //render options for columns of Grid-RatePAY-orders
    function rendermyoptions(value, p, r){
        var options_work = r.data.options_RatePAY;
        var orderid = r.data.id;
        var ordernumber = r.data.bestellnr;
        var zahlstatus_kurz = r.data.zahlstatus_kurz;
        zahlstatus_kurz = zahlstatus_kurz.replace(/\s/g,"");
        zahlstatus_kurz = zahlstatus_kurz.replace( /\"/g, "'");
        if(zahlstatus_kurz=="<spanclass='ratepaystate'style='color:red'>ZahlungvonRatePAYnichtakzeptiert</span>"){
             zahlstatus_kurz = true;
        } else{
             zahlstatus_kurz = false;
        }
        if( r.data.bestellstatus_kurz=='Offen' || r.data.bestellstatus_kurz=='Komplett storniert' || r.data.bestellstatus_kurz=='Komplett ausgeliefert' ||  r.data.bestellstatus_kurz=='Komplett retourniert'){
            return String.format(options_work+"<a class='ico delete' title='Bestellung Nr. "+ordernumber+" l&ouml;schen' style='cursor:pointer;font-size:12px' onclick='deleteOrder("+orderid+","+ordernumber+",\""+r.data.bestellstatus_kurz+"\",\""+zahlstatus_kurz+"\")'></a>");
        }
        else{
            return String.format(options_work);
        }
    }
    //Create store for Grid-RatePAY-orders-articles
    function createMyArticlestore(bestellnr){
        var bestellnr=bestellnr;
        var MyStore =  new Ext.data.Store({
            restful:true,
            baseParams: { myordernumber:bestellnr},
            url: '{url module=backend controller=RatepayBackend action=getArticles}',
            autoLoad:true,
            reader: new Ext.data.JsonReader({
                root: 'items',
                totalProperty: 'total'
            }, [
                { name: 'nr'},
                { name: 'ordernumber'},
                { name: 'artikel_id'},
                { name: 'bestell_nr'},
                { name: 'anzahl'},
                { name: 'name'},
                { name: 'einzelpreis'},
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
    Ext.ns('myRatePAYExt','RatePAYgrid','myGrid','myTab','simple','RatePAYhistory');
    //Create Grid with RatePAY orders
    function createMyRatePAYExt(store,columns,place){
        var store=store;
        var columns=columns;
        var place=place;
        var myRatePAYExt =new Ext.extend(Ext.grid.GridPanel,
        {
            id: 'myRatePAY',
            region: 'center',
            ds: store,
            cm: columns,
            renderTo:place,
            frame:true,
            stripeRows:true,
            height: 370,
            searchFilter: function(){
                if(Ext.getCmp('search').isVisible()==false){
                    bestellvar=Ext.getCmp('bestellcombo').getValue();
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
                myRatePAYExt.superclass.initComponent.call(this);
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
                                    ['Komplett ausgeliefert', '<span class="green"\>Komplett ausgeliefert</span>'],
                                    ['Komplett storniert', '<span class="red">Komplett storniert</span>'],
                                    ['Komplett retourniert', '<span class="red">Komplett retourniert</span>']
                                ]
                            }),
                            valueField: 'myId',
                            displayField: 'displayText2',
                            listeners:
                                {
                                'select': function(combo, record, index) {
                                    text=record.data['myId'];
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

        var myGrid = new myRatePAYExt;
        Ext.getCmp('suchenach').setValue(1);
        return myGrid;
    }
    //Create Article Grid
    function createMyArticlesRatePAYExt(mystore,place,bestellnr,orderid,customer){
        var columns = new Ext.grid.ColumnModel([
            { dataIndex: 'nr', header: 'Nr.', sortable: true, width: 40},
            { dataIndex: 'bestell_nr', header: 'Bestellnr', sortable: true, width: 70},
            { dataIndex: 'name', header: 'Name.', id:'name', sortable: true, width: 150 },
            { dataIndex: 'einzelpreis', header: 'Preis', sortable: true,renderer: renderMoney, width: 55 },
            { dataIndex: 'anzahl', header: 'Anzahl',sortable: true, width: 50},
            { dataIndex: 'offen', header: 'Offen', width: 50 },
            { dataIndex: 'geliefert', header: 'Versendet', width: 60 },
            { dataIndex: 'storniert', header: 'Storniert', sortable: true, width: 65 },
            { dataIndex: 'retourniert', header: 'Retourniert', sortable: true, width: 65 },
            { dataIndex: 'stock', header: 'Lagerbestand', sortable: false, width: 40 },
            { dataIndex: 'bestellstatus', header: 'Bestellstatus',sortable: true, width: 120 },
            { dataIndex: 'options_delete', header: 'L&ouml;schen', sortable: false, width: 70, renderer:renderarticleOptions }
        ]);
        var mystore=mystore;
        var columns=columns;
        var place=place;
        var bestellnr=bestellnr;
        var mypi_RatePAY_counter=1;
        var myArticleRatePAYExt =new Ext.extend(Ext.grid.EditorGridPanel,
        {
            id: 'myArticleExt'+articlepi_RatePAY_counter,
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
                myArticleRatePAYExt.superclass.initComponent.call(this);
            },
            initTbar: function(){
                this.tbar = [
                    {
                        text:'Gutschein hinzuf&uuml;gen',
                        tooltip:'Neuen Gutschein zur Bestellung hinzuf&uuml;gen',
                        handler : function(a,b,c){
                            Ext.Msg.prompt('Gutschein hinzuf&uuml;gen', 'Bitte geben Sie den Betrag ein', function(btn, text){
                                if (btn == 'ok'){
                                    loadingwindow();
                                    text=String(text);
                                    text=text.replace(",",".");
                                    text=parseFloat(text);
                                    if(text>0){
                                        text=text*(-1);
                                    }
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
                                    mypi_RatePAY_counter++;
                                    var recId = bestellnr;  // provide unique id
                                    var p = new mystore.recordType(Article, recId); // create new record
                                    Ext.Ajax.request({
                                        url: '{url module=backend controller=RatepayBackend action=addVoucher}',
                                        success: function(response, opts) {
                                            var obj = Ext.decode(response.responseText);
                                            Ext.getCmp('myloadingwindow').destroy();
                                            if(obj.returnValue==false){
                                                Ext.Msg.alert('RatePAYfehler', 'Der Gutschein konnte nicht hinzugef&uuml;gt werden.');
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
                                        params:{ myordernumber:bestellnr, price:text, articlenumber:'voucher'+randomnumber+text, articleid:'pi_v_'+randomnumber}
                                    })
                                }
                            });
                        },
                        iconCls:'sprite-plus-circle-frame'
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
                            enableKeyEvents : true,
                            id: 'search',
                            listeners:{
                                specialkey:function(f,o){
                                    if(o.getKey()==13){
                                        myArticleGrid.searchFilter();
                                    }
                                }
                            }
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
        var myArticleGrid = new myArticleRatePAYExt;
        Ext.reg('RatePAYArticlegrid', myArticleGrid);
        return myArticleGrid;
    }
    statscounter=0;
    statspi_RatePAY_counter=0;
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
            {
                title: 'Statistiken',
                id:'mytabnr2',
                html: '<div id="myStatsTab"></div>',
                height:290,
                autoScroll : true,
                items: new Ext.TabPanel({
                    activeTab: 0,
                    autoScroll : true,
                    id:'statsInnerTabs',
                    autoTabs : true,
                    deferredRender:false,
                    resizeTabs : true,
                    layoutOnTabChange: true,
                    items: [
                        {
                            title: 'Von RatePAY akzeptiert/abgelehnt',
                            html: '<div id="mystatsdivnr1"></div>',
                            height:340,
                            autoScroll : true,
                            listeners:{
                                'afterrender': function(g, index, ev){
                                    statspanel();
                                }
                            }
                        },
                        {
                            title: 'Bestellstatus',
                            html: '<div id="mystatsdivnr2"></div>',
                            height:340,
                            autoScroll : true,
                            listeners:{
                                'afterrender': function(g, index, ev){
                                    statszahlpanel();
                                }
                            }
                        },
                        {
                            title: 'Rechnungen nach Betr&auml;gen sortiert',
                            html: '<div id="mystatsdivnr3"></div>',
                            height:340,
                            autoScroll : true,
                            listeners:{
                                'afterrender': function(g, index, ev){
                                    kohlepanel();
                                }
                            }

                        },
                        {
                            title: 'Rechnungen nach Bestellnummern sortiert',
                            html: '<div id="mystatsdivnr4"></div>',
                            height:340,
                            autoScroll : true,
                            listeners:{
                                'afterrender': function(g, index, ev){
                                    kohlepanel_ordered();
                                }
                            }
                        },
                        {
                            title: 'Ums&auml;tze nach Kunden sortiert',
                            html: '<div id="mystatsdivnr5"></div>',
                            height:340,
                            autoScroll : true,
                            listeners:{
                                'afterrender': function(g, index, ev){
                                    Umsatz_Kunde();
                                }
                            }

                        },
                        {
                            title: 'Ums&auml;tze der letzten 12 Monate',
                            html: '<div id="mystatsdivnr6"></div>',
                            height:340,
                            autoScroll : true,
                            listeners:{
                                'afterrender': function(g, index, ev){
                                    Umsatz_Monat();
                                }
                            }

                        }

                    ]
                })
            },
            {
                title: 'Logging',
                id:'mytabnr3',
                html: '<div id="myLogsTab"></div>',
                height:290,
                autoScroll : true,
                listeners:{
                    'afterrender': function(g, index, ev){
                        alllogspanel();
                    }
                }

            },
                        {
                title: 'Ratenzahlungskonfiguration',
                id:'mytabnr4',
                html: '<div id="myRateTab"></div>',
                height:290,
                autoScroll : true,
                listeners:{
                    'afterrender': function(g, index, ev){
                        getRateConfig();
                    }
                }

            },
        ]
    });
    //All Logs panel
    function alllogspanel(){
        var logstore =  new Ext.data.Store({
            url: '{url action=getLog}',
            autoLoad:true,
            baseParams: { alllogs:true },
            reader: new Ext.data.JsonReader({
                root: 'items',
                totalProperty: 'total'
            }, [
                { name: 'id'},
                { name: 'date'},
                { name: 'order_number'},
                { name: 'transaction_id'},
                { name: 'payment_method'},
                { name: 'payment_type'},
                { name: 'payment_subtype'},
                { name: 'result'},
                { name: 'request'},
                { name: 'response'},
                { name: 'result_code'},
                { name: 'response_reason'},
                { name: 'customer'}
            ])
        });
        var logcolumns = new Ext.grid.ColumnModel([
            { dataIndex: 'id', header: 'ID', width: 40, sortable: true },
            { dataIndex: 'date', header: 'DATE', sortable: true, width: 110 },
            { dataIndex: 'order_number', header: 'ORDER ID', sortable: true, width: 60 },
            { dataIndex: 'transaction_id', header: 'TRANSACTION ID', sortable: true, width: 110 },
            { dataIndex: 'customer', header: 'CUSTOMER', sortable: true, width: 85 },
            { dataIndex: 'payment_method', header: 'PAYMENT METHOD', sortable: true, width: 60 },
            { dataIndex: 'payment_type', header: 'OPERATION TYPE', sortable: true, width: 140 },
            { dataIndex: 'payment_subtype', header: 'OPERATION SUBTYPE', sortable: true, width: 100 },
            { dataIndex: 'result', header: 'RATEPAY RESULT', sortable: true, width: 160 },
            { dataIndex: 'request', header: 'REQUEST', sortable: true, width: 140, renderer:logrequestrenderer },
            { dataIndex: 'response', header: 'RESPONSE.', sortable: true, width: 140,renderer:logresponserenderer },
            { dataIndex: 'result_code', header: 'RATEPAY RESULT CODE', sortable: true, width: 85 },
            { dataIndex: 'response_reason', header: 'RATEPAY RESULT REASON', sortable: true, width: 85 }

        ]);
        var myRatePAYKompleteLogExt =new Ext.extend(Ext.grid.GridPanel,
        {
            ds: logstore,
            cm: logcolumns,
            renderTo:'myLogsTab',
            frame:true,
            height:370,
            searchFilter: function(){
                logstore.baseParams["search"] = Ext.getCmp('search').getValue();
                logstore.reload();
            },
            initComponent: function() {
                this.initBbar();
                myRatePAYKompleteLogExt.superclass.initComponent.call(this);
            },
            initBbar:function() {
                this.bbar=new Ext.PagingToolbar({
                    store: logstore,
                    displayInfo: true,
                    pageSize: 50,
                    prependButtons: true
                });
                this.tbar=
                    [
                    '<div style="margin-left:650px">Bestellnr., Type oder Subtype suchen</div>',
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
                    }),

                ]
            }
        });
        var myKompleteLog = new myRatePAYKompleteLogExt;
        Ext.reg('RatePAYlog', myKompleteLog);
        return myKompleteLog;
    }
    //Create Store for stats
    function createMyStatsstore(bestellnr){
        var bestellnr=bestellnr;
        var MystatsStore =  new Ext.data.Store({
            restful:true,
            baseParams: { myordernumber:bestellnr},
            url: '{url module=backend controller=RatepayBackend action= getStatsOrder}',
            autoLoad:true,
            reader: new Ext.data.JsonReader({
                root: 'items',
                totalProperty: 'total'
            }, [
                { name: 'bestellstatus'},
                { name: 'total'},
            ])
        });
        return MystatsStore;
    }
    //Create Store for stats
    function createMyStatszahlstore(bestellnr){
        var bestellnr=bestellnr;
        var MystatsStore =  new Ext.data.Store({
            restful:true,
            baseParams: { myordernumber:bestellnr},
            url: '{url module=backend controller=RatepayBackend action= getStatsOrderZahl}',
            autoLoad:true,
            reader: new Ext.data.JsonReader({
                root: 'items',
                totalProperty: 'total'
            }, [
                { name: 'bestellstatus'},
                { name: 'total'},
            ])
        });
        return MystatsStore;
    }
    //Create Store for stats
    function createMyRechnungsStatsstore(bestellnr){
        var bestellnr=bestellnr;
        var MystatsStore =  new Ext.data.Store({
            restful:true,
            baseParams: { myordernumber:bestellnr},
            url: '{url module=backend controller=RatepayBackend action= getStatsOrderInvoice}',
            autoLoad:true,
            reader: new Ext.data.JsonReader({
                root: 'items',
                totalProperty: 'mytotal'
            }, [
                { name: 'invoice_amount'},
                { name: 'ordernumber'},
                { name: 'total'},
            ])
        });
        return MystatsStore;
    }
    //Create Store for stats
    function createMyRechnungsStatsstoreordered(bestellnr){
        var bestellnr=bestellnr;
        var MystatsStore =  new Ext.data.Store({
            restful:true,
            baseParams: { myordernumber:bestellnr},
            url: '{url module=backend controller=RatepayBackend action= getStatsOrderInvoiceOrdered}',
            autoLoad:true,
            reader: new Ext.data.JsonReader({
                root: 'items',
                totalProperty: 'mytotal'
            }, [
                { name: 'invoice_amount'},
                { name: 'ordernumber'},
                { name: 'total'},
            ])
        });
        return MystatsStore;
    }
    //Stats
    function statspanel(){
        var store=createMyStatsstore();
        var statstab=new Ext.Panel({
            renderTo: 'mystatsdivnr1',
            height: 330,
            header:false,
            width: 1000,
            id:'statspanel',
            title: 'Zahlstatus',
            items: {
                store: store,
                xtype: 'piechart',
                dataField: 'total',
                categoryField: 'bestellstatus',
                //extra styles get applied to the chart defaults
                extraStyle:
                    {
                    legend:
                        {
                        display: 'bottom',
                        padding: 5,
                        font:
                            {
                            family: 'Tahoma',
                            size: 13
                        }
                    }
                }
            }
        });
        return statstab;
    }
    //Stats
    function statszahlpanel(){
        var store=createMyStatszahlstore();
        var statszahltab=new Ext.Panel({
            renderTo: 'mystatsdivnr2',
            height: 330,
            width: 1000,
            header:false,
            id:'statszahlpanel',
            title: 'Bestellstatus',
            items: {
                store: store,
                xtype: 'piechart',
                dataField: 'total',
                categoryField: 'bestellstatus',
                //extra styles get applied to the chart defaults
                extraStyle:
                    {
                    legend:
                        {
                        display: 'bottom',
                        padding: 5,
                        font:
                            {
                            family: 'Tahoma',
                            size: 13
                        }
                    }
                }
            }
        });
        return statszahltab;
    }
    //Stats income
    function kohlepanel(){
        var store=createMyRechnungsStatsstore();
        var kohle= new Ext.Panel({
            title: 'Rechnungen nach Betr&auml;gen sortiert',
            renderTo: 'mystatsdivnr3',
            width: 1000,
            height:330,
            header:false,
            layout:'fit',
            items: {
                xtype: 'linechart',
                store: store,
                xField: 'ordernumber',
                yField: 'total',
                tipRenderer : function(chart, record){
                    return 'Bestellnummer '+record.data.ordernumber + ' hat einen Rechnungswert von ' +  Ext.util.Format.number(record.data.total, '0.000,00/i')+' Euro';
                }
            }
        });
        return kohle;
    }
    //Stats income ordered
    function kohlepanel_ordered(){
        var store=createMyRechnungsStatsstoreordered();
        var kohle_ordered= new Ext.Panel({
            title: 'Rechnungen nach Bestellnummern sortiert',
            renderTo: 'mystatsdivnr4',
            width: 1000,
            header:false,
            height:330,
            layout:'fit',
            items: {
                xtype: 'linechart',
                store: store,
                xField: 'ordernumber',
                yField: 'total',
                tipRenderer : function(chart, record){
                    return 'Bestellnummer '+record.data.ordernumber + ' hat einen Rechnungswert von ' +  Ext.util.Format.number(record.data.total, '0.000,00/i')+' Euro';
                }
            }
        });
        return kohle_ordered;
    }
    //Store customer income
    function createUmsatzKundeStore(){
        var bestellnr=bestellnr;
        var MystatsStore =  new Ext.data.Store({
            restful:true,
            baseParams: { myordernumber:bestellnr},
            url: '{url module=backend controller=RatepayBackend action= getStatsUmsatzKunde}',
            autoLoad:true,
            reader: new Ext.data.JsonReader({
                root: 'items',
                totalProperty: 'mytotal'
            }, [
                { name: 'invoice_amount'},
                { name: 'customer'},
                { name: 'total'},
            ])
        });
        return MystatsStore;
    }
    //Stats customer income
    function Umsatz_Kunde(){
        var store=createUmsatzKundeStore();
        var kohle_ordered= new Ext.Panel({
            title: 'Ums&auml;tze nach Kunden sortiert',
            renderTo: 'mystatsdivnr5',
            header:false,
            width: 1000,
            height:330,
            layout:'fit',
            items: {
                xtype: 'linechart',
                store: store,
                xField: 'customer',
                yField: 'total',
                tipRenderer : function(chart, record){
                    return 'Kunde '+record.data.customer + ' hat einen Umsatz von ' +  Ext.util.Format.number(record.data.invoice_amount, '0.000,00/i')+' Euro gemacht';
                }
            }
        });
        return kohle_ordered;
    }
    //Store monthly income
    function createUmsatzMonatStore(){
        var bestellnr=bestellnr;
        var MystatsStore =  new Ext.data.Store({
            restful:true,
            baseParams: { myordernumber:bestellnr},
            url: '{url module=backend controller=RatepayBackend action= getStatsUmsatzMonat}',
            autoLoad:true,
            reader: new Ext.data.JsonReader({
                root: 'items',
                totalProperty: 'mytotal'
            }, [
                { name: 'invoice_amount'},
                { name: 'month'},
                { name: 'total'},
            ])
        });
        return MystatsStore;
    }
    //Stats monthly income
    function Umsatz_Monat(){
        var store=createUmsatzMonatStore();
        var kohle_ordered= new Ext.Panel({
            title: 'Ums&auml;tze der letzten 12 Monate',
            renderTo: 'mystatsdivnr6',
            width: 1000,
            height:330,
            header:false,
            layout:'fit',
            items: {
                xtype: 'linechart',
                store: store,
                xField: 'month',
                yField: 'total',
                tipRenderer : function(chart, record){
                    return 'Im '+record.data.month + ' wurden ' +  Ext.util.Format.number(record.data.invoice_amount, '0.000,00/i')+' Euro erzielt';
                }
            }
        });
        return kohle_ordered;
    }
    createMyRatePAYExt(createMystore(),createMyColums(),'mytabdiv');
    // Open window with RatePAY options
    function orderwindow(orderid,bestellnr,lastname){
        retourepi_RatePAY_counter=0;
        stornopi_RatePAY_counter=0;
        historypi_RatePAY_counter=0;
        logpi_RatePAY_counter=0;
        articlepi_RatePAY_counter=0;
        invoicepi_RatePAY_counter=0;
        var win;
        var orderid=orderid;
        var bestellnr=bestellnr;
        var lastname=lastname;
        var mytest= new Array();
        var mytester= new Array();
        var mytesternext= new Array();
        var mytesternextagain= new Array();
        var myhistory= new Array();
        var mylog= new Array();
        if(!win){
            win = new Ext.Window({
                width:870,
                id: 'myorderwindow'+pi_RatePAY_counter,
                title: 'RatePAY Payment  -  Bestellung Nr. '+bestellnr,
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
                            title: 'Artikel&uuml;bersicht',
                            html: '<div id="artikeldiv'+pi_RatePAY_counter+'"></div>',
                            height:290,
                            autoScroll : true,
                            listeners:{
                                'afterrender': function(g, index, ev){
                                    createMyArticlesRatePAYExt(createMyArticlestore(bestellnr),'artikeldiv'+pi_RatePAY_counter,bestellnr,orderid,lastname);
                                    articlepi_RatePAY_counter++;
                                },
                                'activate': function(g, index, ev){
                                    if(articlepi_RatePAY_counter>0){

                                        tabwahl3=g.body.dom.children[0].id;
                                        mytesternext[articlepi_RatePAY_counter] = createMyArticlesRatePAYExt(createMyArticlestore(bestellnr),tabwahl3,bestellnr,orderid,lastname);
                                        mynextnewpi_RatePAY_counter=articlepi_RatePAY_counter-1;
                                        if(articlepi_RatePAY_counter==0){

                                        }
                                        else{
                                            document.getElementById('myArticleExt'+mynextnewpi_RatePAY_counter).style.display = "none";
                                        }
                                        articlepi_RatePAY_counter++;
                                    }
                                }
                            }
                        },

                        {
                            title: 'Lieferung/Stornierung',
                            html: '<div id="stornodiv'+pi_RatePAY_counter+'"></div>',
                            height:290,
                            autoScroll : true,
                            listeners:{
                                'activate': function(g, index, ev){
                                    tabwahl2=g.body.dom.children[0].id;
                                    mytester[stornopi_RatePAY_counter] = getstorno(tabwahl2,bestellnr);
                                    mynewpi_RatePAY_counter=stornopi_RatePAY_counter-1;
                                    if(stornopi_RatePAY_counter==0){

                                    }
                                    else{
                                        document.getElementById('myStornoExt'+mynewpi_RatePAY_counter).style.display = "none";
                                    }
                                    stornopi_RatePAY_counter++;
                                }
                            }
                        },
                        {
                            title: 'Retoure',
                            html: '<div id="reservierdiv'+pi_RatePAY_counter+'"></div>',
                            height:290,
                            autoScroll : true,
                            listeners:{
                                'activate': function(g, index, ev){
                                    tabwahl=g.body.dom.children[0].id;
                                    mytest[retourepi_RatePAY_counter] = getretoure(tabwahl,bestellnr);
                                    newpi_RatePAY_counter=retourepi_RatePAY_counter-1;
                                    if(retourepi_RatePAY_counter==0){

                                    }
                                    else{
                                        document.getElementById('myRetoureExt'+newpi_RatePAY_counter).style.display = "none";
                                    }
                                    retourepi_RatePAY_counter++;
                                }
                            }

                        },
                        {
                            title: 'Historie',
                            html: '<div id="historiediv'+pi_RatePAY_counter+'"></div>',
                            height:290,
                            autoScroll : true,
                            listeners:{
                                'activate': function(g, index, ev){
                                    tabwahl3=g.body.dom.children[0].id;
                                    myhistory[historypi_RatePAY_counter] = gethistory(tabwahl3,bestellnr);
                                    newpi_RatePAY_counter2=historypi_RatePAY_counter-1;
                                    if(historypi_RatePAY_counter==0){

                                    }
                                    else{
                                        document.getElementById('myHistoryExt'+newpi_RatePAY_counter2).style.display = "none";
                                    }
                                    historypi_RatePAY_counter++;
                                }
                            }

                        },
                        {
                            title: 'Log',
                            html: '<div id="logdiv'+pi_RatePAY_counter+'"></div>',
                            height:290,
                            autoScroll : true,
                            listeners:{
                                'activate': function(g, index, ev){
                                    tabwahl4=g.body.dom.children[0].id;
                                    mylog[logpi_RatePAY_counter] = getlog(tabwahl4,bestellnr);
                                    newpi_RatePAY_counter3=logpi_RatePAY_counter-1;
                                    if(logpi_RatePAY_counter==0){

                                    }
                                    else{
                                        document.getElementById('myLogExt'+newpi_RatePAY_counter3).style.display = "none";
                                    }
                                    logpi_RatePAY_counter++;
                                }
                            }

                        }

                    ]
                })
            });
        }
        win.show(this);
        win.on('beforedestroy',function(){
            Ext.getCmp('myRatePAY').store.reload();

        });
        pi_RatePAY_counter++;
    }
    //send/storno grid
    function getstorno(place,orderid){
        var orderid=orderid;
        var dsstore =  new Ext.data.Store({
            baseParams: { myordernumber:orderid},
            url: '{url module=backend controller=RatepayBackend action=getSendAndCancelArticles}',
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
        var selectionpi_RatePAY_counter=0;
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
                            mycell = myStorno.getSelectionModel().getSelectedCell();
                            myrow=mycell[0];
                            anzahl2 = dsstore.getAt(myrow);
                            anzahl=anzahl2.data.offen;
                            if(g.getValue()>anzahl){
                                g.setValue(anzahl);
                                Ext.Msg.alert('Fehler', 'Sie k&ouml;nnen nicht mehr Artikel versenden als noch offen sind');
                            }
                            selectionpi_RatePAY_counter++;
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
        var myRatePAYStornoExt =new Ext.extend(Ext.grid.EditorGridPanel,
        {
            ds: dsstore,
            cm: cmcolumns,
            renderTo:place,
            frame:true,
            id:'myStornoExt'+stornopi_RatePAY_counter,
            stripeRows:true,
            height:290,
            searchFilter: function(){
                dsstore.baseParams["search"] = Ext.getCmp('search').getValue();
                dsstore.reload();
            },
            clicksToEdit: 1,
            initComponent: function() {
                this.initTbar();
                myRatePAYStornoExt.superclass.initComponent.call(this);
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
                                        url: '{url module=backend controller=RatepayBackend action=sendArticles}',
                                        success: function(response, opts) {
                                            var obj = Ext.decode(response.responseText);
                                            Ext.getCmp('myloadingwindow').destroy();
                                            if(obj.returnValue!=true){
                                                Ext.Msg.alert('RatePAYfehler', 'Fehler beim Versenden. Mehr Informationen finden Sie in den Logs');
                                            }
                                            else{
                                                dsstore.reload();
                                                Ext.Msg.alert('Status','Erfolgreich versendet. Der Bestellstatus der versendeten Artikel wurde ge&auml;ndert.');
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
                        tooltip:'Stornieren sie Die ausgew&auml;hlten Artikel. Der Betrag wird von der Bestellung abgezogen.',
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
                                Ext.Msg.confirm('Warnung', 'M&ouml;chten Sie wirklich alle noch offenen Artikel stornieren?', function(btn){
                                    if (btn == 'yes'){
                                        loadingwindow();
                                        Ext.Ajax.request({
                                            url: '{url module=backend controller=RatepayBackend action=cancelArticles}',
                                            success: function(response, opts) {
                                                var obj = Ext.decode(response.responseText);
                                                Ext.getCmp('myloadingwindow').destroy();
                                                if(obj.returnValue.error==true){
                                                    Ext.Msg.alert('RatePAYfehler', obj.returnValue.errormessage);
                                                }
                                                else{
                                                    dsstore.reload();
                                                    if(obj.komplett==true){
                                                        Ext.Msg.alert('Erfolg', 'Alle Artikel wurden storniert.');
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
                                            url: '{url module=backend controller=RatepayBackend action=cancelArticles}',
                                            success: function(response, opts) {
                                                var obj = Ext.decode(response.responseText);
                                                Ext.getCmp('myloadingwindow').destroy();
                                                if(obj.returnValue.error==true){
                                                    Ext.Msg.alert('RatePAYfehler', obj.returnValue.errormessage);
                                                }
                                                else{
                                                    dsstore.reload();
                                                    if(obj.komplett==true){
                                                        Ext.Msg.alert('Erfolg', 'Alle Artikel wurden storniert.'); 									  }
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
                        iconCls:'delete'
                    },
                    '-',
                    '<div>Name/Bestellnr. suchen: </div>',
                    {
                        xtype: 'textfield',
                        enableKeyEvents : true,
                        id: 'search',
                        listeners:{
                            specialkey:function(f,o){
                                if(o.getKey()==13){
                                    myStorno.searchFilter();
                                }
                            }
                        }
                    },
                    new Ext.Button (
                    {
                        iconCls:'magnifier',
                        handler: function(){
                            this.searchFilter();
                        },
                        scope:this
                    })
                ];
            }
        });
        var myStorno = new myRatePAYStornoExt;
        Ext.reg('RatePAYhistory', myStorno);
        return myStorno;
    }
    //Render articleoptions
    function renderarticleOptions(value, p, r){
        var articlenumber = r.data.bestell_nr;
        var orderid = r.data.orderid;
        var ordernumber = r.data.ordernumber;
        var anzahl= r.data.anzahl;
        var bestellstatus_kurz= r.data.bestellstatus_kurz;
        var customer = r.data.customer;
        var orderfixid = r.data.orderfixid;
        if(bestellstatus_kurz !='Offen'){
            return String.format('&nbsp;');
        }
        else{
            return String.format(
            '<a class="ico delete" style="cursor:pointer;font-size:12px; height:15px;" onclick="deleteArticles(\''+articlenumber+'\','+ordernumber+','+orderid+',\''+customer+'\','+orderfixid+',\''+bestellstatus_kurz+'\','+anzahl+')">&nbsp;&nbsp;&nbsp;</a>');
        }
    }
    //delete articles
    function deleteArticles(articlenumber,ordernumber,orderid,customer,orderfixid,bestellstatus,anzahl){
        if(bestellstatus !='Offen'){
            Ext.Msg.alert('Fehler', 'Sie k&ouml;nnen keine Artikel l&ouml;schen die schon bearbeitet wurden');
        }
        else {
            Ext.Msg.confirm('Artikel L&ouml;schen', 'Artikel wirklich aus der Bestellung entfernen?', function(btn){
                if (btn == 'yes'){
                    loadingwindow();
                    Ext.Ajax.request({
                        url: '{url module=backend controller=RatepayBackend action=cancelArticles}',
                        success: function(response, opts) {
                            var obj = Ext.decode(response.responseText);
                            Ext.getCmp('myloadingwindow').destroy();
                            if(obj.returnValue.error==true){
                                Ext.Msg.alert('RatePAYfehler', obj.returnValue.errormessage);
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
                                Ext.getCmp('myorderwindow'+(pi_RatePAY_counter-1)).destroy();
                                orderwindow(orderid,ordernumber,customer);
                                Ext.Msg.alert('Status', 'Folgender Artikel wurde erfolgreich aus der Bestellung entfernt: <br /><center style="font-weight:bold">'+obj.articlename+'</center>');
                            }
                        },
                        failure: function(response, opts) {
                            Ext.getCmp('myloadingwindow').destroy();
                            Ext.Msg.alert('Status', 'Fehler beim entfernen des Artikels');
                        },
                        params: { myordernumber:ordernumber, articlenr:articlenumber, anzahl:anzahl }
                    });
                }
            });
        }
    }
    //Retoure grid
    function getretoure(place,orderid){
        var orderid=orderid;
        var dsstore2 =  new Ext.data.Store({
            baseParams: { myordernumber:orderid},
            url: '{url module=backend controller=RatepayBackend action=getReturnArticles}',
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
                { name: 'retourniert'},
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
                            myrow=mycell[0];
                            var anzahl2 = dsstore2.getAt(myrow);
                            anzahl=anzahl2.data.geliefert;
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
            { dataIndex: 'retourniert', header: 'Retourniert', width: 80 },
        ]);

        var myRatePAYRetoureExt =new Ext.extend(Ext.grid.EditorGridPanel,
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
            id:'myRetoureExt'+retourepi_RatePAY_counter,
            height:290,
            clicksToEdit: 1,
            initComponent: function() {
                this.initTbar();
                myRatePAYRetoureExt.superclass.initComponent.call(this);
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
                            mystorepi_RatePAY_counter=dsstore2.getCount()
                            var j=0;
                            for(i=0;i<mystorepi_RatePAY_counter;i++){
                                storeindex = dsstore2.getAt(i);
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
                                        url: '{url module=backend controller=RatepayBackend action=returnArticles}',
                                        success: function(response, opts) {
                                            var obj = Ext.decode(response.responseText);
                                            Ext.getCmp('myloadingwindow').destroy();
                                            if(obj.items.error==false){
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
                    '<div class="RatePAY_backend_margin_left">Artikelname oder Bestellnr suchen</div>',
                    '-',
                    {
                        xtype: 'textfield',
                        enableKeyEvents : true,
                        id: 'search',
                        listeners:{
                            specialkey:function(f,o){
                                if(o.getKey()==13){
                                    myRetoure.searchFilter();
                                }
                            }
                        }
                    },
                    new Ext.Button (
                    {
                        iconCls:'magnifier',
                        text: 'Suche starten',
                        handler: function(){
                            this.searchFilter();
                        },
                        scope:this
                    }),
                ];
            }
        });
        var myRetoure = new myRatePAYRetoureExt;
        Ext.reg('myRetoure', myRetoure);
        return myRetoure;
    }
    //get Log for order
    function getlog(place,orderid){
        var orderid=orderid;
        var dsstore =  new Ext.data.Store({
            url: '{url action=getLog}',
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
                { name: 'order_number'},
                { name: 'transaction_id'},
                { name: 'payment_method'},
                { name: 'payment_type'},
                { name: 'payment_subtype'},
                { name: 'result'},
                { name: 'request'},
                { name: 'response'},
                { name: 'result_code'},
                { name: 'customer'},
                { name: 'response_reason'}
            ])
        });
        var cmcolumns = new Ext.grid.ColumnModel([
            { dataIndex: 'id', header: 'ID', width: 40 },
            { dataIndex: 'date', header: 'DATE', sortable: true, width: 110 },
            { dataIndex: 'order_number', header: 'ORDER ID', sortable: true, width: 60 },
            { dataIndex: 'customer', header: 'CUSTOMER', sortable: true, width: 85 },
            { dataIndex: 'transaction_id', header: 'TRANSACTION ID', sortable: true, width: 60 },
            { dataIndex: 'payment_method', header: 'PAYMENT METHOD', sortable: true, width: 60 },
            { dataIndex: 'payment_type', header: 'OPERATION TYPE', sortable: true, width: 120 },
            { dataIndex: 'payment_subtype', header: 'OPERATION SUBTYPE', sortable: true, width: 60 },
            { dataIndex: 'result', header: 'RATEPAY RESULT', sortable: true, width: 135},
            { dataIndex: 'request', header: 'REQUEST', sortable: true, width: 65,renderer:logrequestrenderer },
            { dataIndex: 'response', header: '	RESPONSE', sortable: true, width: 65,renderer:logresponserenderer  },
            { dataIndex: 'result_code', header: 'RATEPAY RESULT CODE', sortable: true, width: 50 },
            { dataIndex: 'response_reason', header: 'RATEPAY RESULT REASON', sortable: true, width: 85 }

        ]);
        var myRatePAYLogExt =new Ext.extend(Ext.grid.GridPanel,
        {
            ds: dsstore,
            cm: cmcolumns,
            renderTo:place,
            frame:true,
            id:'myLogExt'+logpi_RatePAY_counter,
            height:290,
            initComponent: function() {
                myRatePAYLogExt.superclass.initComponent.call(this);
            }
        });
        var myLog = new myRatePAYLogExt;
        Ext.reg('RatePAYlog', myLog);
        return myLog;
    }


    //get rate Config
    function getRateConfig(){
        var dsstore =  new Ext.data.Store({
            url: '{url action=getRateConfigRequest}',
            autoLoad:true,
            reader: new Ext.data.JsonReader({
                root: 'items',
                totalProperty: 'total'
            }, [
                { name: 'text'},
                { name: 'value'},
            ])
        });
        var cmcolumns = new Ext.grid.ColumnModel([
            { dataIndex: 'text', width: 250 },
            { dataIndex: 'value', sortable: true, width: 400},
        ]);
        var rateDetailExt =new Ext.extend(Ext.grid.GridPanel,
        {
            ds: dsstore,
            cm: cmcolumns,
            renderTo: 'myRateTab',
            frame:true,
            id:'rateDetail'+rateDetailpi_RatePAY_counter,
            height:370,
            initComponent: function() {
                rateDetailExt.superclass.initComponent.call(this);
            }
        });
        var rateDetail = new rateDetailExt;
        Ext.reg('RatePAYlog', rateDetail);
        return rateDetail;
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
        var myRatePAYHistoryExt =new Ext.extend(Ext.grid.GridPanel,
        {
            ds: dsstore,
            cm: cmcolumns,
            renderTo:place,
            frame:true,
            id:'myHistoryExt'+historypi_RatePAY_counter,
            height:290,
            initComponent: function() {
                myRatePAYHistoryExt.superclass.initComponent.call(this);
            }
        });
        var myHistory = new myRatePAYHistoryExt;
        Ext.reg('RatePAYhistory', myHistory);
        return myHistory;
    }
    //render currency
    function renderMoney(value,p,record){
        wert = value;
        return wert + ' &euro;';
    };
      //render currency
    function renderInvoiceMoney(value,p,record){
        wert = value;
        if(wert !="Die Rechnung ist aktuell"){
            return wert + ' &euro;';
        }
        else{
            return "<span class=\"green\">Die Rechnung ist aktuell</span>";
        }
    };

    //delete order(s)
    function deleteOrder(orderId,ordernumber,bestellstatus,zahlstatus){
        Ext.Msg.confirm('Bestellung L&ouml;schen', 'Wollen Sie die Bestellung wirklich l&ouml;schen?', function(btn){
            if (btn == 'yes'){
                loadingwindow();
                Ext.Ajax.request({
                    url: '{url module=backend controller=RatepayBackend action=deleteOrder}',
                    success: function(response, opts) {
                        Ext.getCmp('myloadingwindow').destroy();
                        var obj = Ext.decode(response.responseText);
                        Ext.Msg.alert('Status', obj.returnValue);
                        if(obj.errorValue!=true){
                            Ext.getCmp('myRatePAY').store.reload();
                        }
                    },
                    failure: function(response, opts) {
                        Ext.getCmp('myloadingwindow').destroy();
                        Ext.Msg.alert('Status', 'Fehler beim l&ouml;schen der Bestellung');
                    },
                    params: { orderId:orderId , ordernumber:ordernumber, bestellstatus:bestellstatus,zahlstatus:zahlstatus}
                });
            }
        });
    };
    function pdfpreview(ordernumber){
        document.getElementById('previewForm').action="../engine/backend/order/openPDF.php?pdf="+ordernumber+"";
        document.getElementById('previewForm').submit();
    }
    function logresponserenderer(value, p, r){
        newresponse = String(r.data.response);
        newresponse = newresponse.replace(/\s/g,"&nbsp;");
        return String.format(
        '<a title="Response anschauen" onclick=\"openlog(\''+newresponse+'\');\" style="cursor:pointer; text-decoration:underline">Response</a>'
    );
    }
    function logrequestrenderer(value, p, r){
        newrequest = String(r.data.request);
        newrequest = newrequest.replace(/\s/g,"&nbsp;");
        return String.format(
        '<a title="Request anschauen" onclick=\"openlog(\''+newrequest+'\');\" style="cursor:pointer; text-decoration:underline">Request</a>'
    );
    }
    function openlog(value){
        newrequest = value.replace(/> </g,"&gt;<br />&lt;");
        newrequest = newrequest.replace(/></g,"&gt;<br />&lt;");
        newrequest = newrequest.replace(/</g,"&lt;");
        newrequest = newrequest.replace(/>/g,"&gt;");
        newrequest = newrequest.replace(/&lt;br \/&gt;/gi,"<br />");
        logwin = new Ext.Window({

            width:870,
            id: 'mylogwindow',
            title: 'RatePAY-Logging',
            height:350,
            autoScroll : true,
            modal:true,
            frame:true,
            closeAction:'close',
            html:newrequest
        });
        logwin.show(this);
    }
    //Render Options for Invoice Grid
    function renderOptions(value, p, r){
        var invoice = r.data.invoice_hash;
        var ordernumber = r.data.order_number;
        if(r.data.invoice_amount_new != "Die Rechnung ist aktuell" && r.data.invoice_amount_new != "0,00"  ){
            return String.format(
                    '<a class="ico sprite-printer" title="Rechnung anschauen/speichern" onclick=\"pdfpreview(\''+invoice+'\')\" style="cursor:pointer"></a><a class="ico arrow_circle_225" onclick="getNewInvoice('+ordernumber+',\''+invoice+'\',0)" title="Rechnung neu erstellen(retournierte und stornierte Artikel werden abgezogen)" style="cursor:pointer"></a>'
                );
        } else{
            return String.format(
                '<a class="ico sprite-printer" title="Rechnung anschauen/speichern" onclick=\"pdfpreview(\''+invoice+'\')\" style="cursor:pointer"></a>'
             );
        }
    }

    //Render Options for Invoice Grid
    function renderStornoOptions(value, p, r){
        var invoice = r.data.stornoHash;
        return String.format(
            '<a class="ico sprite-printer" title="Rechnung anschauen/speichern" onclick=\"pdfpreview(\''+invoice+'\')\" style="cursor:pointer"></a>'
        );

    }

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
                html:'<div class="pi_ratepay_loading"></div>'
            })
        }
        win2.show(this);
        win2.on('beforedestroy',function(){
            Ext.getCmp('myRatePAY').store.reload();
        });
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