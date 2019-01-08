{extends file='frontend/index/index.tpl'}

{namespace name="frontend/account/ajax_logout"}

{* Breadcrumb *}
{block name='frontend_index_start'}
    {$smarty.block.parent}
    {s name="OneTimeAbortTitle" assign="snippetOneTimeAbortTitle"}{/s}
    {$sBreadcrumb = [['name' => $snippetOneTimeAbortTitle, 'link' => {url}]]}
{/block}

{block name='frontend_index_content'}
    <div class="account--logout account--content content is--wide">
        {block name="frontend_account_logout_info"}
            <div class="account--welcome panel">

                {block name="frontend_account_logout_info_headline"}
                    <h1 class="panel--title">{s name="OneTimeAbortTitle"}{/s}</h1>
                {/block}

                {block name="frontend_account_logout_info_content"}
                    <div class="panel--body is--wide">
                        <p class="logout--text">{s name="OneTimeAbortText"}{/s}</p>
                    </div>
                {/block}

                {block name="frontend_account_logout_info_actions"}
                    <div class="panel--actions is--wide">
                        {s name="OneTimeAbortButton" assign="snippetOneTimeAbortButton"}{/s}
                        <a class="btn is--secondary is--icon-left" href="{url controller='index'}" title="{$snippetOneTimeAbortButton|escape}">
                            <i class="icon--arrow-left"></i>{s name="OneTimeAbortButton"}{/s}
                        </a>
                    </div>
                {/block}
            </div>
        {/block}
    </div>
{/block}
