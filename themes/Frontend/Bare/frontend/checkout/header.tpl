<header class="header-main">

    {* Hide top bar navigation *}
    {block name='frontend_index_top_bar_container'}{/block}

    <div class="container header--navigation">

        {* Logo container *}
        {block name='frontend_index_logo_container'}
            {include file="frontend/index/logo-container.tpl"}
        {/block}

        {* Hide Shop navigation *}
        {block name='frontend_index_shop_navigation'}{/block}
    </div>
</header>

{* Hide Maincategories navigation top *}
{block name='frontend_index_navigation_categories_top'}{/block}
