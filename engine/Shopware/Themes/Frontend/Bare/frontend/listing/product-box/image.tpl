{* Product image - uses the picturefill polyfill for the HTML5 "picture" element *}
<a href="{$sArticle.linkDetails|rewrite:$sArticle.articleName}" title="{$sArticle.articleName}" class="box--image">
    <span data-picture data-alt="{$sArticle.articleName}" class="image--element">
        <span data-src="{if isset($sArticle.image.src)}{$sArticle.image.src.2}{else}{link file='frontend/_resources/images/no_picture.jpg'}{/if}"></span>
        <span data-src="{if isset($sArticle.image.src)}{$sArticle.image.src.3}{else}{link file='frontend/_resources/images/no_picture.jpg'}{/if}" data-media="(min-width: 47.75em)"></span>
        <span data-src="{if isset($sArticle.image.src)}{$sArticle.image.src.4}{else}{link file='frontend/_resources/images/no_picture.jpg'}{/if}" data-media="(min-width: 64em)"></span>
        <span data-src="{if isset($sArticle.image.src)}{$sArticle.image.src.5}{else}{link file='frontend/_resources/images/no_picture.jpg'}{/if}" data-media="(min-width: 120em)"></span>

        <noscript>
            <img src="{if isset($sArticle.image.src)}{$sArticle.image.src.3}{else}{link file='frontend/_resources/images/no_picture.jpg'}{/if}" alt="{$sArticle.articleName}">
        </noscript>
    </span>
</a>