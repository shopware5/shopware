{extends file="frontend/index/index.tpl"}

{* hide shop navigation *}
{block name='frontend_index_navigation'}{/block}

{* hide breadcrumb bar *}
{block name='frontend_index_breadcrumb'}{/block}

{* hide left sidebar *}
{block name='frontend_index_content_left'}{/block}

{block name="frontend_index_body_classes"}{$smarty.block.parent}{strip} emotion--preview{/strip}{/block}

{block name="frontend_index_content"}

    {block name="widgets_emotion_preview_content"}
        <div class="content content--emotion-preview">
            <div class="content--emotions">

                {block name="widgets_emotion_preview_wrapper"}
                    {include file="frontend/_includes/emotion.tpl" showListing=$emotion.showListing}
                {/block}
            </div>
        </div>
    {/block}
{/block}

{* hide right sidebar *}
{block name='frontend_index_content_right'}{/block}

{* hide last seen articles *}
{block name='frontend_index_left_last_articles'}{/block}

{* hide shop footer *}
{block name="frontend_index_footer"}{/block}
