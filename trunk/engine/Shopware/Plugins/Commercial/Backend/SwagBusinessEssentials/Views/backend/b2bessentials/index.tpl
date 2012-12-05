{extends file="backend/ext_js/index.tpl"}

{block name="backend_index_css" append}
<style type="text/css">

/*	=GRID PANEL STYLING
	-------------------------------------------- */
.action_icon {
	cursor: pointer;
	margin-right: 4px;
}
.x-grid-checkheader {
	height: 14px;
	background-image: url('{link file='backend/b2bessentials/_resources/images/unchecked.gif'}');
	background-position: 50% -2px;
	background-repeat: no-repeat;
	background-color: transparent;
}

.x-grid-checkheader-checked {
	background-image: url('{link file='backend/b2bessentials/_resources/images/checked.gif'}');
}
.x-grid-checkheader-editor .x-form-cb-wrap {
	text-align: center;
}

/*	=TAB PANEL STYLE
	based on ExtJS 4's silver style
	-------------------------------------------- */
.x-tab { border-color: #B5B5B5 }
.x-tab-default-top {
	border-bottom: 1px solid #d0d0d0 !important;
	box-shadow: 0 1px 0 0 white inset, -1px 0 0 0 white inset, 1px 0 0 0 white inset;
	background: #EAEAEA;
	background-image: -moz-linear-gradient(center top , #DCDCDC, #EAEAEA);
	background-image: -webkit-linear-gradient(center top , #DCDCDC, #EAEAEA);
}
.x-tab-default-top .x-tab-inner { color: #6f6f6f; }
.x-tab-default-top-active .x-tab-inner { color: #333; }
.x-tab-default-top-active { border-bottom-color: #eaeaea !important }
.x-tab-top-active {
	background-color: #eaeaea;
	background-image: -moz-linear-gradient(center top , #FFFFFF, #EAEAEA);
	background-image: -webkit-linear-gradient(center top , #FFFFFF, #EAEAEA);
}
.x-tab-active button { color: #3E677D; }
.x-tab-bar {

	border-bottom-color: #888;
	background-color: #D2D2D2;
}
.x-tab-bar-body { border-color: #d0d0d0 }
.x-panel-body-default { border-top-color: #888 }
.x-tab-bar-strip-default, .x-tab-bar-strip-default-plain {
	background-color: #EAEAEA;
	border-color: #D0D0D0;
}
.x-tab-close-btn { background: url("{link file='backend/b2bessentials/_resources/images/tab-default-close.gif'}") no-repeat }
.x-box-scroller-left .x-toolbar-scroll-left, .x-box-scroller-left .x-tabbar-scroll-left {
	background: url("{link file='backend/b2bessentials/_resources/images/scroll-left.gif'}")
}
.x-box-scroller-right .x-toolbar-scroll-right, .x-box-scroller-right .x-tabbar-scroll-right {
	background: url("{link file='backend/b2bessentials/_resources/images/scroll-right.gif'}")
}

/*	=TREE PANEL STYLE
	-------------------------------------------- */
.x-tree-panel .x-grid-row .x-grid-cell-inner { height: 26px; line-height: 24px }
.x-gecko .x-tree-panel .x-grid-row .x-grid-cell-inner { line-height: 24px; }
.x-grid-row { line-height: 29px }
.x-tree-elbow,
.x-tree-elbow-end { width: 10px }

.x-tree-icon { margin-right: 10px }
.locked { background-image: url('{link file='backend/b2bessentials/_resources/images/icn_lock-closed.png'}') }
.unlocked { background-image: url('{link file='backend/b2bessentials/_resources/images/icn_lock-open.png'}') }

/*	=START SECTION
	-------------------------------------------- */
.start-page .x-panel-body { background: url("{link file='backend/b2bessentials/_resources/images/backend-screen.png'}") no-repeat right 30px }
.start-page .teaser {
	background: url("{link file='backend/b2bessentials/_resources/images/logo_shopware-small.png'}") no-repeat left top;
	padding: 65px 0 0;
}
.start-page .feature-list .x-panel-body { background: transparent }
.start-page .feature-list .x-panel-body li {
	background: url("{link file='backend/b2bessentials/_resources/images/li_tick.jpg'}") no-repeat left top;
	padding-left: 24px;
	line-height: 19px;
	font-weight: 700;
	color: #666;
	margin: 0 0 12px;
}
.start-page .feature-list .x-panel-body li li {
	background: transparent;
	font-weight: 500;
	padding-left: 0;
	margin: 0 0 0 15px;
	list-style: square;
}
/*	=PRIVATE SHOPPING SECTION
	-------------------------------------------- */
.x-mask-msg { padding-top: 2px !important; top: 48% !important }

/*	=TOPBAR PANEL
	contains the licence check and a help link
	-------------------------------------------- */
.topbarPnl {
	font-weight: 700;
	font-size: 14px;
	padding: 8px 0 0 10px;
	background: #666;
	cursor: pointer;
}
.topbarPnl .x-panel-body-default {
	background: #666
}
.topbarPnl a {
	display: block;
	color: #fff;
	height: 100%;
	width: 100%;
	background: url("{link file='backend/b2bessentials/_resources/images/icn_help.png'}") no-repeat right top;
	text-decoration: none;
}
.topbarPnl a:hover { text-decoration: underline }
    .x-box-inner {
        overflow:visible;
    }
</style>
{/block}



{block name="backend_index_javascript" append}
<script type="text/javascript" src="{link file='backend/b2bessentials/_resources/javascript/CheckColumn.js'}" charset="utf-8"></script>
<script type="text/javascript" src="{link file='backend/b2bessentials/_resources/javascript/iFrame.js'}" charset="utf-8"></script>
<script type="text/javascript">

    {if !$sLicenceCheck}
        alert('License check for module "SwagBusinessEssentials" has failed.');
    {/if}

	Ext.define('Shopware.B2B', {
			extend: 'Ext.container.Viewport',
			initComponent: function(){

				this.featureList = new Ext.XTemplate(
					'<ul>',
						'<li>',
							'Konfiguration von eigenen Template-Variablen',
							'<ul>',
								'<li>Diese Variablen können z.B. genutzt werden um bestimmte Shopbereiche kundengruppenabhängig auszublenden</li>',
							'</ul>',
						'</li>',
						'<li>',
							'Private Shopping Modul',
							'<ul>',
								'<li>Eigene Haupt-Templates je Kundengruppe möglich</li>',
								'<li>Vor-geschaltete Login-Seite mit oder ohne Registrierungsmöglichkeit</li>',
								'<li>Allgemeine Info-Seite die vor Login angezeigt wird (Keine Shop-Funktion ohne Login)</li>',
							'</ul>',
						'</li>',
						'<li>',
							'Registrierungs- und Freischalt-Funktionen',
							'<ul>',
								'<li>Komfortable Freischaltmöglichkeit für Kunden im Backend (z.B. Händler-Freischaltung)</li>',
							'</ul>',
						'</li>',
						'<li>',
							'Artikel und Kategorien nach Kundengruppe',
							'<ul>',
								'<li>Freigeben / Sperren von bestimmten Artikeln oder ganzen Kategorien für verschiedene Kundengruppen</li>',
							'</ul>',
						'</li>',
					'</ul>'
				);

				this.tree = Ext.create('Shopware.B2B.TreeMenu');
				this.tabPanel = Ext.create('Ext.tab.Panel',{
					region: 'center',
					closeable:true,
					items:
					[

					Ext.widget('panel', {
						title: 'Start!',
						width: 500,
						bodyPadding: '20 400 20 20',
						autoScroll: true,
						cls: 'start-page',
						items: [{
							xtype: 'component',
							cls: 'teaser',
							html: '<p> <strong style="font-weight:bold">"Business Essentials" - Alles, was man für´s Business braucht.</strong><br /><br />"Business Essentials" stellt Ihnen zahlreiche Module bereit, die Ihren Shop perfekt auf die Bedürfnisse von Geschäftskunden ausrichten. Ob nun die Registrierung verschiedener Kundengruppen, Private Shopping oder kundenindividuelle Preise  - Business Essentials bietet Ihnen alle Möglichkeiten, Ihren Shop den individuellen Bedürfnissen verschiedener Gruppen und Einzelkunden entsprechend anzupassen. Verschaffen Sie sich einen Überblick und wählen Sie die einzelnen Funktionen links aus der Liste der verfügbaren Module. Wichtig: Sollten Sie Business Essentials in der Free-Version verwenden, stehen Ihnen nicht alle Funktionen zur Verfügung! Folgende weitere Funktionen bietet Ihnen die Vollversion:<p>',
							style: 'margin-bottom: 20px;'
						}, {
							xtpye: 'container',
							border: false,
							cls: 'feature-list',
							tpl: this.featureList,

							/** ... we need the attribute to say extjs that the provided template needs to be rendered */
							data: [],
						}, {
							xtype: 'container',
							margin: '15 0 0 0',
							style: 'text-align:center',
							items: [{
								xtype: 'button',
								cls: 'helpBtn',
								scale: 'large',
								text: 'Hilfe erhalten',
								margin: '0 5 0 0',
								handler: function(){
									window.open('http://wiki.shopware.de/Modul-Business-Essentials_detail_703.html');
								}
							}]
						}]
					})

					]
				});

				this.tree.tabPanel = this.tabPanel;

				Ext.apply(this, {
					title: 'Test',
					layout: {
						type: 'border',
						padding: '0 0 11 0'
					},
					items: [this.tree,this.tabPanel]
				});
				this.callParent(arguments);
			}
	});
	Ext.onReady(function(){
		Ext.create('Shopware.B2B');
	});
</script>
{/block}
