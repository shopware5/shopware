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
	padding: 0 0 0 125px;
}
.logo .inner .commstore {
	background: url("{link file='images/commstore_mid_trans.png'}") no-repeat right center;
	padding-right: 270px;
	min-height: 212px;
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
		<div class="commstore">
			<p class="bold">
				Sie benötigen mehr Funktionen oder eine Anpassung?
			</p>
			
			
			<p>
				Sie konnten Ihre Wunsch-Funktion bisher nicht finden? Vermutlich liegt dies daran, dass im Demoshop nicht alle Funktionen freigeschaltet sind. Setzen Sie sich ganz einfach mit unserem Vertrieb in Verbindung. 
	
	
	Shopware hat über 150 Plugins und Erweiterungen, die Sie in unserem Community Store finden. Wenn Ihre gesuchte Funktionalität auch dort nicht zu finden ist, besteht die Möglichkeit der Individualprogrammierung durch die shopware AG oder einen unserer über 200 zertifizierten Partner.
			</p>
		</div>
	</div>
</fieldset>

<fieldset class="list">
	<legend>Plugins / Anpassungsmöglichkeiten!</legend>
	
	<ul>
		<li><a href="http://store.shopware.de" target="_blank">Zum Community-Store mit über 150 Erweiterungen</a></li>
		<li><a href="http://www.shopware.de/partner/ueberblick/" target="_blank">Zur Übersicht der Shopware-Partner</a></li>
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
		<em class="label">eMail:</em> info@shopware.de
	</p>
	
	<p>
		<a href="http://www.shopware.de/kontakt" name="contact-btn" class="contact-btn" target="_blank">
			Zum <strong>Kontaktformular</strong>
		</a>
	</p>

</strong>
</fieldset>

{/block}