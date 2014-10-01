{namespace name="frontend/detail/index"}

{* Previous product *}
{block name='frontend_detail_article_back'}
    <a href="#" class="navigation--link link--prev">
        <div class="link--prev-button">
            <span class="link--prev-inner">{s name='DetailNavPrevious'}Zurück{/s}</span>
        </div>
        <div class="image--container"></div>
    </a>
{/block}

{* Next product *}
{block name='frontend_detail_article_next'}
    <a href="#" class="navigation--link link--next">
        <div class="link--next-button">
            <span class="link--next-inner">{s name='DetailNavNext'}Vor{/s}</span>
        </div>
        <div class="image--container"></div>
    </a>
{/block}