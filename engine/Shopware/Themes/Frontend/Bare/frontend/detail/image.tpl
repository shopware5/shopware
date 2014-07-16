{block name="frontend_detail_image"}

	{* Product image - Thumbnails *}
	{block name='frontend_detail_image_thumbs'}
		{include file="frontend/detail/images.tpl"}
	{/block}

    <div data-image-scroller="true" class="box--image">
        <ul class="images--list">
            <li class="images--list-item">
                <span data-picture
					  data-alt="{if $sArticle.image.res.description}{$sArticle.image.res.description|escape:"html"}{else}{$sArticle.articleName|escape:"html"}{/if}"
					  class="image--element">

                    {*Image based on our default media queries *}
                    {block name='frontend_detail_image_default_queries'}
                        <span class="image--media" data-src="{if isset($sArticle.image.src)}{$sArticle.image.src.4}{else}{link file='frontend/_resources/images/no_picture.jpg'}{/if}"></span>
						<span class="image--media" data-src="{if isset($sArticle.image.src)}{$sArticle.image.src.5}{else}{link file='frontend/_resources/images/no_picture.jpg'}{/if}" data-media="(min-width: 48em)"></span>
                    {/block}

                    {*Block to add additional image based on media queries *}
                    {block name='frontend_detail_image_additional_queries'}{/block}

                    {*If the browser doesn't support JS, the following image will be used *}
                    {block name='frontend_detail_image_fallback'}
                        <noscript>
                            <img itemprop="image" src="{if isset($sArticle.image.src)}{$sArticle.image.src.4}{else}{link file='frontend/_resources/images/no_picture.jpg'}{/if}" alt="{$sArticle.articleName}">
                        </noscript>
                    {/block}
                </span>
            </li>

            {foreach $sArticle.images as $image}
                <li class="images--list-item">
                    <span data-picture
						  data-alt="{if $image.res.description}{$image.res.description|escape:"html"}{else}{$sArticle.articleName|escape:"html"}{/if}"
						  class="image--element">

                        {*Image based on our default media queries *}
                        {block name='frontend_detail_image_default_queries'}
							<span class="image--media" data-src="{if isset($image.src)}{$image.src.4}{else}{link file='frontend/_resources/images/no_picture.jpg'}{/if}"></span>
							<span class="image--media" data-src="{if isset($image.src)}{$image.src.5}{else}{link file='frontend/_resources/images/no_picture.jpg'}{/if}" data-media="(min-width: 48em)"></span>
                        {/block}

                        {*Block to add additional image based on media queries *}
                        {block name='frontend_detail_image_additional_queries'}{/block}

                        {*If the browser doesn't support JS, the following image will be used *}
                        {block name='frontend_detail_image_fallback'}
                            <noscript>
                                <img itemprop="image" src="{if isset($image.src)}{$image.src.4}{else}{link file='frontend/_resources/images/no_picture.jpg'}{/if}" alt="{$sArticle.articleName}">
                            </noscript>
                        {/block}
                    </span>
                </li>
            {/foreach}
        </ul>
    </div>
{/block}