{extends file="backend/index/parent.tpl"}
{block name="backend_index_css" append}
	<!-- Common CSS -->
	<link href="{link file='engine/backend/css/icons4.css'}"  rel="stylesheet" type="text/css" />
	<link href="{link file='engine/backend/css/modules.css'}" rel="stylesheet" type="text/css" />
{/block}
{block name='backend_index_body_inline'}
<style>
/** General styling classes */
html, body, fieldset { background: #fff }
.bold { font-weight: 700 }
p { margin: 0 0 1.6em }
.nomargin { margin: 0 }

/** Fieldset */
fieldset {
	font: normal 12px/1.6 "Tahoma", "Geneva", "Arial", "Helvetica", sans-serif;
	padding: 18px;
	margin: -25px 0 15px;
	background: #fff url("{link file='backend/b2bessentials/_resources/images/bg_fieldset-header.png'}") repeat-x 0 -1px;
}
em.label {
	display: inline-block;
	font-weight: 700;
	width: 50px;
}

/** List styling */
ul li {
	background: url("{link file='backend/b2bessentials/_resources/images/li_tick.jpg'}") no-repeat left center;
	line-height: 20px;
	float: none;
	clear: both;
	padding: 0 0 0 24px !important;
}

/** Logo */
.logo .inner {
	background: url("{link file='backend/b2bessentials/_resources/images/logo_shopware-blend.png'}") no-repeat;
	padding: 0 0 0 125px;
}
.logo .inner .commstore {
	background: url("{link file='backend/b2bessentials/_resources/images/articlecategoriespermissions.jpg'}") no-repeat right center;
	padding-right: 470px;
	min-height: 212px;
}

/** Contact Btn */
.contact-btn, .contact-btn:link, .contact-btn:active {
	/** Prevent user to select the text */
	-webkit-user-select: none;
	-moz-user-select: none;
	user-select: none;

	display: inline-block;
	background: url("{link file='images/btn_contact.png'}") no-repeat;
	color: #fff;
	position: relative;
	text-shadow: 1px 1px 0 #3d7e1d;
	text-decoration: none;
	height: 33px; line-height: 33px;
	font-size: 14px;
	padding: 0 12px 4px 42px;
}
.contact-btn strong { font-weight: 700 }
.contact-btn:active { top: 1px }
fieldset a,
fieldset a:link,
fieldset a:active { color: #0077A9; font-weight: 700; text-decoration: none }
fieldset a:hover { text-decoration: underline }
 
</style>
<fieldset class="logo">
	<legend>Hinweis!</legend>
	
	<div class="inner">
		<div class="commstore">
			<p class="bold">
				Hinweise zu dieser Funktion:
			</p>
			<p>
				<ul class="list">
					<li>Das Freischalten von Artikeln für bestimmte Kundengruppen erledigen Sie bequem und einfach direkt in der Stammdaten-Konfiguration der Artikel</li>
					<li>Die Konfiguration auf Kategorie-Ebene wird in den Kategorie-Details unter Artikel -> Kategorien durchgeführt</li>
					<li><a href="http://wiki.shopware.de/Wie-kann-ich-Kategorien-und-Artikel-fuer-bestimmte-Kundengruppen-ausgeben-lassen%253F_detail_593.html" target="_blank">Weitere Informationen im Wiki</a></li>
				</ul>
			</p>
		</div>
	</div>
</fieldset>


{/block}