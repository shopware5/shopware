<div class="bundle-container" data-request-url="{url controller=Bundle action=getArticleConfiguration}">

    <input type="hidden" name="discount-type" value="{$sBundle.discountType}">
    <input type="hidden" name="discount-value-usage" value="{$sBundle.discount.usage}">
    <input type="hidden" name="discount-percentage" value="{$sBundle.discount.percentage}">
    <div class="currency-helper" style="display: none">{0|currency}</div>
    <h2 class="headingbox">{s namespace="frontend/detail/bundle" name="DetailBundleHeaeder"}Sparen Sie jetzt mit unserem Bundle-Angeboten:{/s}</h2>

    <div class="inner-bundle">
        <div class="item-list-all">
        	<div class="item-left">
                {* Image listing, displayed within the first container of a single bundle, displays onlay the small article thumbnails *}
                {foreach $sBundle.articles as $sArticle}
                    <div class="thumb {if $sArticle@last} last-item{/if}" style="background-image: url({if $sArticle.cover.src.1}{$sArticle.cover.src.1}{else}{link file='frontend/_resources/images/no_picture.jpg'}{/if});">
                        <span class="plus-sep">+</span>
                    </div>
                {/foreach}
        	</div>
        	<div class="item-right price">
	            <div class="price-display">
	            
	                {* Bundle rabatt *}
	                <span class="price-rabatt">
	                    ({s namespace="frontend/detail/bundle" name="DetailBundleInstead"}Statt{/s} <em class="total-price-value" style="text-decoration: line-through;">{$sBundle.totalPrice|currency}</em> {s name="Star" namespace="frontend/listing/box_article"}{/s} - <span class="discount-value">{$sBundle.discount.display|currency}</span> {s name="Star" namespace="frontend/listing/box_article"}{/s} {s namespace="frontend/detail/bundle" name="DetailBundleDiscount"}Rabatt{/s})
	                </span>
	            
	                <strong class="price-desc">{s namespace="frontend/detail/bundle" name="DetailBundle"}Preis für alle:{/s}</strong>

	                {* Bundle price *}
	                <span class="bundle-price">
	                    <span class="bundle-price-value">{$sBundle.price.display|currency}</span> {s name="Star" namespace="frontend/listing/box_article"}{/s}
	                </span>
	            </div>

                <div class="actions">
                    <input type="submit" class="add-bundle" value="In den Warenkorb" />
                </div>
        	</div>
        	<div class="clear"></div>
        </div>

        <ul>
            {foreach $sBundle.articles as $sArticle}
                <li class="item checked-item{if $sArticle@first} first-item{/if} {if $sArticle@last} last-item{/if}"
                    data-bundle-article-id="{if $sArticle.bundleArticleId}{$sArticle.bundleArticleId}{else}0{/if}"
                    data-bundle-id="{$sBundle.id}">

                        <input type="hidden" name="price" value="{$sArticle.price.total}">

                        {* Cross-Selling checkbox *}
                        {if $sBundle.type === 2 && !$sArticle@first}
                            <input type="checkbox" name="bundle-article-{$sArticle.bundleArticleId}" checked="checked" class="checkbox" />
                        {/if}

                        {* Thumbnail *}
                        <div class="outer-thumb" style="background-image:url({$sArticle.cover.src.0});">&nbsp;</div>

                        {* Article title *}
                        <span class="article-title">
                             <a class="show-bundle-detail" href="{url controller=detail sArticle=$sArticle.articleId}">{$sArticle.quantity}x {$sArticle.name}</a> {if $sArticle.supplier}<span class="supplier">- {$sArticle.supplier} -</span>{/if}
                        </span>

                        {* Article price *}
                        <span class="bundle-price">
                            {$sArticle.price.total|currency} {s name="Star" namespace="frontend/listing/box_article"}{/s}
                        </span>
                        <div class="clear"></div>


                    <div class="clear"></div>

                    {if $sArticle.configuration|@count > 0}
                        <div class="lower-content bundle-article-configuration">
                            <input type="hidden" class="bundle-article-configuration-id" value="{$sArticle.bundleArticleId}">
                            <input type="hidden" class="request-url" value="{url controller=bundle action=configureArticle}">

                            {foreach $sArticle.configuration as $group}
                                <div class="select-inner">
                                    <label>{$group.name}:</label><br />
                                    <select name="group-{$group.id}" {if $sArticle.bundleArticleId==0}disabled="disabled"{/if}>
                                        {foreach $group.options as $option}
                                            <option value="{$option.id}"{if $option.id == $group.selected} selected="selected"{/if}>
                                                {$option.name}
                                            </option>
                                        {/foreach}
                                    </select>
                                </div>
                            {/foreach}

                            {if $sArticle.bundleArticleId > 0}
                                <a href="#" class="add-configuration">{s name="sConfigurationTake" namespace="frontend/detail/bundle"}Konfiguration übernehmen{/s}</a>
                            {/if}
                            <div class="clear"></div>
                        </div>
                    {/if}
                </li>
            {/foreach}
        </ul>
    </div>
</div>