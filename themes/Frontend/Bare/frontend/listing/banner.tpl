{block name="frontend_listing_banner"}
    {foreach $sBanner as $banner}
        {block name="frontend_listing_banner_single"}
            <div class="banner--container">
                {if $banner.media.thumbnails}
                    {if !$banner.link || $banner.link == "#" || $banner.link == ""}

                        {* Image only banner *}
                        {block name='frontend_listing_image_only_banner'}
                            <picture>
                                <source srcset="{$banner.media.thumbnails[1].sourceSet}" media="(min-width: 48em)">

                                <img srcset="{$banner.media.thumbnails[0].sourceSet}" alt="{$banner.description|escape}" class="banner--img" />
                            </picture>
                        {/block}
                    {else}

                        {* Normal banner *}
                        {block name='frontend_listing_normal_banner'}
                            <a href="{$banner.link}" class="banner--link" {if $banner.link_target}target="{$banner.link_target}"{/if} title="{$banner.description|escape}">
                                <picture>
                                    <source srcset="{$banner.media.thumbnails[1].sourceSet}" media="(min-width: 48em)">

                                    <img srcset="{$banner.media.thumbnails[0].sourceSet}" alt="{$banner.description|escape}" class="banner--img" />
                                </picture>
                            </a>
                        {/block}
                    {/if}
                {/if}
            </div>
        {/block}
    {/foreach}
{/block}
