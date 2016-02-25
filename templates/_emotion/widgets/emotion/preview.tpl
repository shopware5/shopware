{extends file="parent:frontend/index/index.tpl"}

{* hide shop navigation *}
{block name='frontend_index_navigation'}{/block}

{* hide breadcrumb bar *}
{block name='frontend_index_breadcrumb'}{/block}

{* hide left sidebar *}
{block name='frontend_index_content_left'}{/block}

{block name="frontend_index_content"}
    {action module=widgets controller=emotion categoryId=$emotionId}

    <div class="clear"></div>
{/block}

{* hide right sidebar *}
{block name='frontend_index_content_right'}{/block}

{* hide last seen articles *}
{block name='frontend_index_left_last_articles'}{/block}

{* hide shop footer *}
{block name="frontend_index_footer"}{/block}

{* hide shopware footer *}
{block name="frontend_index_shopware_footer"}{/block}