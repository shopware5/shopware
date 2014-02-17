{* Footer menu *}
<div class="footer_menu">
	
	<div class="footer_column col1">
		<span class="head">{s name="sFooterServiceHotlineHead"}Service Hotline{/s}</span>
		<p>{s name="sFooterServiceHotline"}Telefonische Unterst&uuml;tzung und Beratung unter:<br /><br /><strong style="font-size:19px;">0180 - 000000</strong><br/>Mo-Fr, 09:00 - 17:00 Uhr{/s}</p>
	</div>
	
	<div class="footer_column col2">
		<span class="head">{s name="sFooterShopNavi1"}Shop Service{/s}</span>
		<ul>
		{foreach from=$sMenu.gBottom item=item  key=key name="counter"}
			<li>
				<a href="{if $item.link}{$item.link}{else}{url controller='custom' sCustom=$item.id title=$item.description}{/if}" title="{$item.description}" {if $item.target}target="{$item.target}"{/if}>
					{$item.description}
				</a>
			</li>
		{/foreach}
		</ul>
	</div>
	<div class="footer_column col3">
		<span class="head">{s name="sFooterShopNavi2"}Informationen{/s}</span>
		<ul>
		{foreach from=$sMenu.gBottom2 item=item key=key name="counter"}
			<li>
				<a href="{if $item.link}{$item.link}{else}{url controller='custom' sCustom=$item.id title=$item.description}{/if}" title="{$item.description}" {if $item.target}target="{$item.target}"{/if}>
					{$item.description}
				</a>
			</li>
		{/foreach}
		</ul>
	</div>
	<div class="footer_column col4 last">
		<span class="head">{s name="sFooterNewsletterHead"}Newsletter{/s}</span>
		<p>
			{s name="sFooterNewsletter"}Abonnieren Sie den kostenlosen DemoShop Newsletter und verpassen Sie keine Neuigkeit oder Aktion mehr aus dem DemoShop.{/s}
		</p>

		<form action="{url controller='newsletter'}" method="post">
			
			<input type="hidden" value="1" name="subscribeToNewsletter" />
			
			<div class="fieldset">
				<input type="text" name="newsletter" id="newsletter_input" value="{s name="IndexFooterNewsletterValue"}Ihre E-Mail Adresse{/s}" />
				<input type="submit" class="submit" id="newsletter" value="{s name='IndexFooterNewsletterSubmit'}Newsletter abonnieren{/s}" />
			</div>
		</form>
	</div>
	
</div>