{extends file="frontend/listing/product-box/box-basic.tpl"}

{namespace name="frontend/listing/box_article"}

{block name='frontend_listing_box_article_rating'}{/block}

{block name='frontend_listing_box_article_description'}{/block}

{block name='frontend_listing_box_article_actions'}{/block}

{block name='frontend_listing_box_article_picture'}
    <a href="{$sArticle.linkDetails|rewrite:$sArticle.articleName}"
       title="{$sArticle.articleName|escape:'html'}"
       class="product--image{if $imageOnly} is--large{/if}">
	<span data-picture data-alt="{$sArticle.articleName|escape:'html'}" class="image--element">
		<span class="image--media" data-src="{if isset($sArticle.image.src)}{$sArticle.image.src.4}{else}{link file='frontend/_public/src/img/no-picture.jpg'}{/if}"></span>
		<span class="image--media" data-src="{if isset($sArticle.image.src)}{$sArticle.image.src.5}{else}{link file='frontend/_public/src/img/no-picture.jpg'}{/if}" data-media="(min-width: 48em)"></span>
		<span class="image--media" data-src="{if isset($sArticle.image.src)}{$sArticle.image.src.5}{else}{link file='frontend/_public/src/img/no-picture.jpg'}{/if}" data-media="(min-width: 78.75em)"></span>

		<noscript>
            <img src="{if isset($sArticle.image.src)}{$sArticle.image.src.4}{else}{link file='frontend/_public/src/img/no-picture.jpg'}{/if}" alt="{$sArticle.articleName|escape:'html'}">
        </noscript>
	</span>
    </a>
{/block}

{block name='frontend_listing_box_article_badges'}
    {if !$imageOnly}
        {$smarty.block.parent}
    {/if}
{/block}

{block name='frontend_listing_box_article_name'}
    {if !$imageOnly}
        {$smarty.block.parent}
    {/if}
{/block}

{block name='frontend_listing_box_article_price_info'}
    {if !$imageOnly}
        {$smarty.block.parent}
    {/if}
{/block}