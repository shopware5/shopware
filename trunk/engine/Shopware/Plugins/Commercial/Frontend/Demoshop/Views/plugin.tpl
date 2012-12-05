{block name="frontend_index_header_css_screen" append}
	<link type="text/css" media="all" rel="stylesheet" href="{link file='scripts/css/bottom.css'}" />
{/block}

{block name="frontend_index_header_javascript" append}
	<script type="text/javascript" src="{link file='scripts/js/bottom.js'}"></script>
<script type="text/javascript">
 
  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-25492547-1']);
  _gaq.push(['_trackPageview']);
 
  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();
 
</script>
  
{/block}

{block name="frontend_index_shopware_footer" append}

<div class="demo-theme-switcher">

	<div class="handle">
		>
	</div>

	<div class="row">
		<div class="color-box black" title="Template 'Black' anwenden">
			<a href="{$sStart}?sTpl=emotion_black" class="inner-box"></a>
		</div>
		<div class="color-box blue"  title="Template 'Blue' anwenden">
			<a href="{$sStart}?sTpl=emotion_blue" class="inner-box"></a>
		</div>
		<div class="color-box brown"  title="Template 'Brown' anwenden">
			<a href="{$sStart}?sTpl=emotion_brown" class="inner-box"></a>
		</div>
		
		<div class="clear"></div>
	</div>
	
	<div class="row">
		<div class="color-box gray"  title="Template 'Gray' anwenden">
			<a href="{$sStart}?sTpl=emotion_gray" class="inner-box"></a>
		</div>
		<div class="color-box green"  title="Template 'Green' anwenden">
			<a href="{$sStart}?sTpl=emotion_green" class="inner-box"></a>
		</div>
		
		<div class="clear"></div>
	</div>
	
	<div class="row">
		<div class="color-box orange"  title="Template 'Orange' anwenden">
			<a href="{$sStart}?sTpl=emotion_orange" class="inner-box"></a>
		</div>
		<div class="color-box pink"  title="Template 'Pink' anwenden">
			<a href="{$sStart}?sTpl=emotion_pink" class="inner-box"></a>
		</div>
		<div class="color-box red"  title="Template 'Red' anwenden">
			<a href="{$sStart}?sTpl=emotion_red" class="inner-box"></a>
		</div>
		<div class="color-box turquoise"  title="Template 'Turquoise' anwenden">
			<a href="{$sStart}?sTpl=emotion_turquoise" class="inner-box"></a>
		</div>
		
		<div class="clear"></div>
	</div>
</div>

<div class="bottom-bar-wrapper">
	<div class="top-line">
		<div class="handle">&nbsp;</div>
	</div>
	<div class="header">Adminbereich / Eigene Demo</div>
	<div class="content">
	
		<div class="row">
			<div class="col colfirst">
				<h1>Wichtige Info:</h1>
				
				<p class="desc">
					Diese &ouml;ffentliche Demo von Shopware wird täglich um 23.00 Uhr zur&uuml;ckgesetzt!
					
				</p> 
				
				<p class="nospace">
					<strong>Version:</strong> <em>Shopware 4.0.1</em>
				</p>
				
				<p>
					<strong>Release-Datum:</strong> <em>31.08.2012</em>
				</p>
				<p>
					<span class="action">
						<a class="download"href="http://wiki.shopware.de/Downloads_cat_448.html">
							Download Shopware CE
						</a>
					</span>
				</p>
			</div>
			
			<div class="col">
				<h1>Zum Adminbereich</h1>
				
				{* the form action and method will be used for the ajax request *}
				<form action="{url controller='myController' action='myAction'}" method="post">
					<p class="desc">
						Der Administrationsbereich von Shopware kann mit jedem modernen Webbrowser genutzt werden. Ideal sind beispielsweise:
					</p>
					
					<ul class="nolist">
						<li>
							Firefox ab Version 3
						</li>
						
						<li>
							Google Chrome
						</li>
						
						<li>
							Safari
						</li>
						<li>
							Internet Explorer 9
						</li>
					</ul>
					
					<p>
						<input type="submit" class="contact-button" value="Adminbereich &ouml;ffnen" onclick="window.open('http://www.shopwaredemo.de/backend/index')" />
					</p>
				</form>
			</div>
				
			<div class="col">
				<h1>Ihre n&auml;chsten Schritte</h1>
				<p>
					Nehmen Sie Kontakt zu uns auf und besprechen Sie die nächsten Schritte:
				</p>
				
				<div class="tel">
					+49 (0) 2555 92885-0
				</div>
				
				<div class="email">
					<a href="mailto:info@shopware.de">info&#64;shopware.de</a>
				</div>
				
				<div class="list">
					<a href="http://www.shopware.de/demozugang-jetzt-anfordern/?sCategory=374" target="_blank">
						Eigene Testumgebung beantragen
					</a>
					
					<a href="http://www.shopware.de/Kontakt" target="_blank">
						Individuelles Angebot anfordern
					</a>
					
					<a href="http://www.shopware.de/Kontakt" target="_blank">
						Termin für Live Pr&auml;sentation
					</a>
					
					<a href="http://www.shopware.de/shopware.php/sViewport,support/sFid,5" target="_blank">
						R&uuml;ckruf anfordern
					</a>
				</div>
			</div>
			
			<div class="col collast">
				<h1>Hilfe / Wiki / Forum</h1>
				
				<p>
					Das zentrale Informationsportal
				</p>
				
				<div class="list">
					<a href="http://wiki.shopware.de" target="_blank">
						Zur Shopware Community Seite
					</a>
				</div>
				
				<h1 class="secondheadline">Weitere Funktionen</h1>
				
				<p>
					Shopware l&auml;sst sich dank Plugin-System flexibel anpassen
				</p>
				
				<div class="list">
					<a href="http://store.shopware.de" target="_blank">
						Zum Community Store (> 200 Plugins)
					</a>
					
					<a href="http://www.shopware.de/partner/ueberblick/" target="_blank">
						Anpassungen bei Partner anfragen
					</a>
				</div>
			</div>
		</div>
	
	</div>
</div>
{/block}
