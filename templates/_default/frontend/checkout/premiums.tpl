{if $sPremiums}
<div class="table_premium">
	{block name='frontend_checkout_premium_head'}{/block}
	
	{block name='frontend_checkout_premium_body'}
		<div class="body">

		<div class="space">&nbsp;</div>

		{foreach from=$sPremiums item=premium key=key}
			{if $premium.sArticle.active}
				<div class="article{cycle values=",, last"}">

					{* Article picture *}
					{block name='frontend_checkout_premium_image'}
					<a class="thumbnail" href="{$premium.sArticle.linkDetails}" title="{$premium.sArticle.articleName}">

						{if $premium.sArticle.image.src}
							<img src="{$premium.sArticle.image.src.3}" alt="{$premium.sArticle.articleName}" title="{$premium.sArticle.articleName}" border="0"/>
						{else}
							<img src="{link file='frontend/_resources/images/no_picture.jpg'}" alt="{s name="PremiumInfoNoPicture"}{/s}" />
						{/if}
					</a>

					{if $premium.available}
						<div class="bonus_price free">
							<p>{s name="sBonusPriceFree"}<strong>Gratis</strong><br />Jetzt Pr&auml;mie sichern</p>{/s}
						</div>
					{else}
					<div class="overlay">&nbsp;</div>
					{/if}
					{/block}

		        	{block name='frontend_checkout_premium_article_name'}
		        	{* Article name *}
		        	<div class="name">
		        		<a href="{$premium.sArticle.linkDetails}" title="{$premium.sArticle.articleName}">
		        			{$premium.sArticle.articleName}
		        		</a>
		        	</div>
					{/block}
				</div>
			{else}
				<div class="article{cycle values=",, last"}">
					{block name='frontend_checkout_premium_image'}
					<a class="thumbnail" href="{$premium.sArticle.linkDetails}" title="{$premium.sArticle.articleName}">

						{if $premium.sArticle.image.src}
							<img src="{$premium.sArticle.image.src.3}" alt="{$premium.sArticle.articleName}" title="{$premium.sArticle.articleName}" border="0"/>
						{else}
							<img src="{link file='frontend/_resources/images/no_picture.jpg'}" alt="{s name="PremiumInfoNoPicture"}{/s}" />
						{/if}
					</a>

					{if $premium.available}
						<div class="bonus_price free">
							<p>{s name="sBonusPriceFree"}<strong>Gratis</strong><br />Jetzt Pr&auml;mie sichern</p>{/s}
						</div>
					{else}
					<div class="overlay">&nbsp;</div>
					{/if}
					{/block}
					<div class="name">
						{block name='frontend_checkout_premium_article_name'}
						<a href="{$premium.sArticle.linkDetails}" title="{$premium.sArticle.articleName}">
							<strong>{$premium.sArticle.articleName}</strong>
						</a>
						{/block}

						{if $premium.available}
							<form action="{url action='addPremium' sTargetAction=$sTargetAction}" method="post" id="sAddPremiumForm{$key}" name="sAddPremiumForm{$key}">

						{block name='frontend_checkout_premium_select_article'}
							{if $premium.sVariants && $premium.sVariants|@count > 1}
								<select class="variant" id="sAddPremium{$key}" name="sAddPremium">
									<option value="">{s name="PremiumInfoSelect"}{/s}</option>
									{foreach from=$premium.sVariants item=variant}
										<option value="{$variant.ordernumber}">{$variant.additionaltext}</option>
									{/foreach}
								</select>
							{else}
								<input type="hidden" name="sAddPremium" value="{$premium.sArticle.ordernumber}" />
							{/if}

							<br />
							<input type="submit" class="button-right small_right"  title="{$premium.sArticle.articleName}" value="{s name='PremiumActionAdd'}{/s}" />
						{/block}
							</form>
						{else}
							{block name='frontend_checkout_premium_bonus_price'}
							<div class="bonus_price">
								<span class="pr1">{se name="PremiumsInfoAtAmount"}{/se} {$premium.startprice|currency}</span>
								<br />
								<span class="pr2">{se name="PremiumsInfoDifference"}{/se} <br /> {$premium.sDifference|currency}</span>
							</div>
					  		{/block}
						{/if}
					</div>
				</div>
			{/if}
		{/foreach}
		<div class="clear">&nbsp;</div>
		</div>
	{/block}
	<div class="clear">&nbsp;</div>
</div>
{/if}