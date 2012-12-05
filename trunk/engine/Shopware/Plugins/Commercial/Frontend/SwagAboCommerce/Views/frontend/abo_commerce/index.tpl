{extends file="parent:frontend/index/index.tpl"}

{* Add breadcrumb item *}
{block name='frontend_index_start' append}
	{assign var='sBreadcrumb' value=[['name'=>"Abo-Commerce", 'link' =>{url controller='AboCommerce' action='index'}]]}
{/block}

{* Remove the sidebar left *}
{block name='frontend_index_content_left'}{/block}

{* Main content area *}
{block name='frontend_index_content'}
<div class="grid_16 first abo-landing abo-landing-content">

    {* Hero unit *}
    <div class="hero-unit abo-teaser">
        <div class="hero-unit-text">
            <h2 class="main-headline">Abo Commerce</h2>
            <h3 class="sub-headline">Lorem ipsum dolor sit amet</h3>
        </div>
    </div>

    <div class="space"></div>

    {include file="frontend/abo_commerce/listing.tpl"}
</div>

{/block}

{* Sidebar right *}
{block name='frontend_index_content_right'}
    {include file="frontend/abo_commerce/right.tpl"}
{/block}