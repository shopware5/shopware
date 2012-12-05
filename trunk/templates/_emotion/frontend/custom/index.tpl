{extends file='parent:frontend/custom/index.tpl'}

{block name='frontend_index_content'}
    {if $sCustomPage.subPages}
        {$pages = $sCustomPage.subPages}
    {elseif $sCustomPage.siblingPages}
        {$pages = $sCustomPage.siblingPages}
    {/if}
    {if $pages}
        <div class="custom_subnavi">
            {if $pages}
                <ul class="sub-pages">
                {foreach $pages as $subPage}
                    <li><a href="{url controller=custom sCustom=$subPage.id}" title="{$subPage.description}" {if $subPage.active} class="active"{/if}>
                        {$subPage.description}
                    </a></li>
                {/foreach}
                </ul>
            {/if}
        </div>
    {/if}

    <div id="center" class="custom grid_13">

        <h1>{$sCustomPage.description}</h1>

        {* Article content *}
        {block name='frontend_custom_article_content'}
            {$sContent}
        {/block}
    </div>
{/block}

{* Sidebar right *}
{block name='frontend_index_content_right'}{/block}

{* Sidebar left *}
{block name='frontend_index_content_left'}
	{include file='frontend/index/left.tpl'}
{/block}