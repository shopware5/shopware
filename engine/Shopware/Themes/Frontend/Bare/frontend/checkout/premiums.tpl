{block name='frontend_checkout_premium_body'}
	<ul class="table-premium--list">
		{foreach $sPremiums as $premium}
			{if $premium.sArticle.active}
				<li class="list--entry block-group{if $premium@iteration is odd by 1} is--even{/if}{if $premium@index + 1 == $premium@total || $premium@index + 2 == $premium@total} is--last-row{/if}">

					{* Product image *}
					{block name='frontend_checkout_premium_active_image'}
						<a class="table--media block" href="{$premium.sArticle.linkDetails}" title="{$premium.sArticle.articleName}">
							{if $premium.sArticle.image.src}
								<img src="{$premium.sArticle.image.src.3}" alt="{$premium.sArticle.articleName}"
									 title="{$premium.sArticle.articleName}">
							{else}
								<img src="{link file='frontend/_resources/images/no_picture.jpg'}"
									 alt="{s name="PremiumInfoNoPicture"}{/s}">
							{/if}
						</a>
					{/block}

					{* Product information *}
					{block name='frontend_checkout_premium_active_info'}
						<div class="entry--info block">

							{* Product name *}
							{block name='frontend_checkout_premium_active_info_name'}
								<a href="{$premium.sArticle.linkDetails}" title="{$premium.sArticle.articleName}" class="entry--name">
									{$premium.sArticle.articleName|truncate:60}
								</a>
							{/block}

							{if $premium.available}
								<form action="{url action='addPremium' sTargetAction=$sTargetAction}" method="post"
									  id="sAddPremiumForm{$key}" name="sAddPremiumForm{$key}">

									{block name='frontend_checkout_premium_select_article'}
										{if $premium.sVariants && $premium.sVariants|@count > 1}
											<select class="premium--selection" id="sAddPremium{$key}" name="sAddPremium">
												<option value="">{s name="PremiumInfoSelect"}{/s}</option>
												{foreach from=$premium.sVariants item=variant}
													<option value="{$variant.ordernumber}">{$variant.additionaltext}</option>
												{/foreach}
											</select>
										{else}
											<input type="hidden" name="sAddPremium" value="{$premium.sArticle.ordernumber}"/>
										{/if}

										{block name='frontend_checkout_premium_active_info_button'}
											<input type="submit" class="btn btn--primary is--small" title="{$premium.sArticle.articleName}"
												   value="{s name='PremiumActionAdd'}{/s}"/>
										{/block}
									{/block}
								</form>
							{/if}
						</div>
					{/block}
				</li>
			{else}
				<li class="list--entry block-group is--disabled{if $premium@iteration is odd by 1} is--even{/if}{if $premium@index + 1 == $premium@total || $premium@index + 2 == $premium@total} is--last-row{/if}">
					{* Product image *}
					{block name='frontend_checkout_premium_image'}
						<a class="table--media block" href="{$premium.sArticle.linkDetails}" title="{$premium.sArticle.articleName}">
							{if $premium.sArticle.image.src}
								<img src="{$premium.sArticle.image.src.3}" alt="{$premium.sArticle.articleName}"
									 title="{$premium.sArticle.articleName}">
							{else}
								<img src="{link file='frontend/_resources/images/no_picture.jpg'}"
									 alt="{s name="PremiumInfoNoPicture"}{/s}">
							{/if}
						</a>
					{/block}

					{* Product information *}
					{block name='frontend_checkout_premium_info'}
						<div class="table--content block">

							{* Product name *}
							{block name='frontend_checkout_premium_info_name'}
								<a href="{$premium.sArticle.linkDetails}" title="{$premium.sArticle.articleName}" class="entry--name">
									{$premium.sArticle.articleName|truncate:60}
								</a>
							{/block}

							{if $premium.available}
								<form action="{url action='addPremium' sTargetAction=$sTargetAction}" method="post"
									  id="sAddPremiumForm{$premium@key}" name="sAddPremiumForm{$premium@key}">

									{block name='frontend_checkout_premium_select_article'}
										{if $premium.sVariants && $premium.sVariants|@count > 1}
											<select class="premium--selection" id="sAddPremium{$key}" name="sAddPremium">
												<option value="">{s name="PremiumInfoSelect"}{/s}</option>
												{foreach from=$premium.sVariants item=variant}
													<option value="{$variant.ordernumber}">{$variant.additionaltext}</option>
												{/foreach}
											</select>
										{else}
											<input type="hidden" name="sAddPremium" value="{$premium.sArticle.ordernumber}"/>
										{/if}

										{block name='frontend_checkout_premium_info_button'}
											<span class="table--badge">GRATIS</span>
											<input type="submit" class="btn btn--primary is--small right" title="{$premium.sArticle.articleName}"
												   value="{s name='PremiumActionAdd'}{/s}"/>
										{/block}
									{/block}
								</form>
							{else}

								{* Show difference between the necessary basket value to collect the premium product and the actucal basket value *}
								{block name='frontend_checkout_premium_info_difference'}
									<div class="table--difference">
										{s name="PremiumsInfoAtAmount"}{/s} {$premium.startprice|currency}
										{s name="PremiumsInfoDifference"}{/s} {$premium.sDifference|currency}
									</div>
								{/block}
							{/if}
						</div>
					{/block}
				</li>
			{/if}
		{/foreach}
	</ul>
{/block}