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
.browser {
	background: url("{link file='images/icn_browser-logos.png'}") no-repeat right top;
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
</style>
<!--[if IE 8]>
<style type="text/css">
body { padding: 0; }
fieldset { margin: 10px; padding: 20px; }
fieldset legend { padding: 0 5px; margin: 0; }
fieldset.title {
	border-top: 0 none;
}
fieldset.title legend {
	margin-top: -18px;
	margin-left: -6px;
	margin-bottom: 15px;
}
fieldset  legend {
	margin-bottom: 15px;
}
fieldset.browser {
	background-position: right 60px;
}
fieldset.logo {
	margin-bottom: -20px;
}
</style>
<![endif]-->
<fieldset class="logo title">
	<legend>Hinweis!</legend>
	
	<div class="inner">
		<p class="bold">
			Das Shopware Backend ist mit Ihrem Browser nicht kompatibel.
		</p>
		
		<p>
			Installieren Sie zum Testen einen der unten genannten Browser.
		</p>
		
		<p>
			Mit der kommenden Version 4.0 wird sich das Shopware Backend auch mit dem Internet-Explorer bedienen lassen!
		</p>
	</div>
</fieldset>

<fieldset class="list browser">
	<legend>Kompatible Browser!</legend>
	
	<ul>
		<li><a href="http://www.mozilla.org/de/firefox/" target="_blank">Firefox ab Version 3</a></li>
		<li><a href="http://www.google.de/chrome" target="_blank">Google Chrome</a></li>
		<li><a href="www.apple.com/de/safari/download/" target="_blank">Safari</a></li>
	</ul>
</fieldset>

<fieldset class="title">
	<legend>Sie haben Fragen?</legend>
	<p class="bold">
		Nehmen Sie gerne jederzeit Kontakt mit unserem Vertrieb auf:
	</p>
	</strong>
		<p class="nomargin">
			<em class="label">Telefon:</em> 02555 - 997500
		</p>
		
		<p>
			<em class="label">eMail:</em> info@shopware.de
		</p>
		
		<p>
			<a href="#contact-btn" name="contact-btn" class="contact-btn">
				Zum <strong>Kontaktformular</strong>
			</a>
		</p>
	
	</strong>
</fieldset>

{/block}