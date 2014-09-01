{namespace name="frontend/detail/index"}

{* Previous product *}
{block name='frontend_detail_article_back'}
    <a href="#" class="navigation--link link--prev">
        <span class="link--prev-inner">{s name='DetailNavPrevious'}Zurück{/s}</span>
    </a>
{/block}

{* Next product *}
{block name='frontend_detail_article_next'}
    <a href="#" class="navigation--link link--next">
        <span class="link--next-inner">{s name='DetailNavNext'}Vor{/s}</span>
    </a>
{/block}