{extends file="backend/index/parent.tpl"}
{block name="backend_index_css" append}
	<!-- Common CSS -->
	<link href="{link file='engine/backend/css/icons4.css'}"  rel="stylesheet" type="text/css" />
	<link href="{link file='engine/backend/css/modules.css'}" rel="stylesheet" type="text/css" />
{/block}
{block name='backend_index_body_inline'}
<style>
/** General styling classes */
.bold { font-weight: 700 }
p { margin: 0 0 1.6em }
.nomargin { margin: 0 }

/** Fieldset */
fieldset {
	font: normal 12px/1.6 "Tahoma", "Geneva", "Arial", "Helvetica", sans-serif;
	padding: 18px;
	margin: -25px 0 15px;
}
em.label {
	display: inline-block;
	font-weight: 700;
	width: 50px;
}

/** List styling */
.list ul li {
	background: url("{link file='images/list-bullet.png'}") no-repeat left center;
	height: 20px; line-height: 20px;
	float: none;
	clear: both;
	padding: 0 0 0 24px;
}

/** Logo */
.logo .inner {
	background: url("{link file='images/logo_shopware-blend.png'}") no-repeat;
	min-height: 125px;
	padding: 0 0 0 125px;
}

/** Contact Btn */
.contact-btn {
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
		<p class="bold">
			Diese Funktion ist in der öffentlichen Demo von Shopware nicht verfügbar!
		</p>
		
		<p>
			Fordern Sie jetzt kostenlos Ihre personalisierte und individuelle Demoversion an. 
		</p>
		
		<p>




Alternativ können Sie auch zum Nulltarif unsere Shopware Community Edition herunterladen und auf Ihrem Server installieren. Oder Sie entscheiden sich
für das komfortable Profihost Installationspaket, das Sie mit einer vorinstallierten Shopware 30 Tage lang kostenlos und
unverbindlich nutzen können. 



		</p>
	</div>
</fieldset>

<fieldset class="list">
	<legend>Weitere Test-Möglichkeiten!</legend>
	
	<ul>
		<li><a href="http://www.shopware.de/demo?sCategory=164" target="_blank">Eigene, persönliche Demo-Version beantragen</a></li>
		<li><a href="http://wiki.shopware.de/Downloads_cat_448.html" target="_blank">Zur Downloadseite Community-Version</a></li>
		<li><a href="http://wiki.shopware.de/Shopware-3.5.4-XAMPP-Package-mit-Demodaten--aktuellen-Shopware-Plugins_detail_687.html" target="_blank">Fertiges Xampp Paket für Windows</a></li>
		<li><a href="http://wiki.shopware.de/Shopware-3.5-VMware-Image_detail_562.html" target="_blank">Fertiges VMware Image</a></li>
		<li><a href="https://www.profihost.com/shopware-demohosting-kostenlos" target="_blank">30 Tage kostenlos bei Profihost testen (Shopware vorinstalliert)</a></li>
	</ul>
</fieldset>

<fieldset>
	<legend>Sie haben Fragen?</legend>
	<p class="bold">
		Nehmen Sie gerne jederzeit Kontakt mit unserem Vertrieb auf:
	</p>
</strong>
	<p class="nomargin">
		<em class="label">Telefon:</em> +49 (0) 2555 92885-0
	</p>
	
	<p>
		<em class="label">eMail:</em> <a href="mailto:info@shopware.de">info@shopware.de</a>
	</p>
	
	<p>
		<a href="http://www.shopware.de/kontakt" name="contact-btn" class="contact-btn">
			Zum <strong>Kontaktformular</strong>
		</a>
	</p>

</strong>
</fieldset>

{/block}