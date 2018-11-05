{namespace name="frontend/detail/comment"}

{* Offcanvas buttons *}
{block name='frontend_detail_rating_buttons_offcanvas'}
    <div class="buttons--off-canvas">
        {block name='frontend_detail_rating_buttons_offcanvas_inner'}
            {s name="OffcanvasCloseMenu" namespace="frontend/detail/description" assign="snippetOffcanvasCloseMenu"}{/s}
            <a href="#" title="{$snippetOffcanvasCloseMenu|escape}" class="close--off-canvas">
                <i class="icon--arrow-left"></i>
                {s name="OffcanvasCloseMenu" namespace="frontend/detail/description"}{/s}
            </a>
        {/block}
    </div>
{/block}

<div class="content--product-reviews" id="detail--product-reviews">

    {* Response save comment *}
    {if $sAction == "ratingAction"}
        {block name='frontend_detail_comment_error_messages'}
            {if $sErrorFlag}
                {if $sErrorFlag['sCaptcha']}
                    {$file = 'frontend/_includes/messages.tpl'}
                    {$type = 'error'}
                    {s name="DetailCommentInfoFillOutCaptcha" assign="content"}{/s}
                {else}
                    {$file = 'frontend/_includes/messages.tpl'}
                    {$type = 'error'}
                    {s name="DetailCommentInfoFillOutFields" assign="content"}{/s}
                {/if}
            {else}
                {if {config name="OptinVote"} && !{$smarty.get.sConfirmation} && !{$userLoggedIn}}
                    {$file = 'frontend/_includes/messages.tpl'}
                    {$type = 'success'}
                    {s name="DetailCommentInfoSuccessOptin" assign="content"}{/s}
                {else}
                    {$file = 'frontend/_includes/messages.tpl'}
                    {$type = 'success'}
                    {s name="DetailCommentInfoSuccess" assign="content"}{/s}
                {/if}
            {/if}

            {include file=$file type=$type content=$content}
        {/block}
    {/if}

    {* Review title *}
    {block name="frontend_detail_tabs_rating_title"}
        <div class="content--title">
            {s name="DetailCommentHeader"}{/s} "{$sArticle.articleName}"
        </div>
    {/block}

    {* Display review *}
    {if $sArticle.sVoteComments}
        {foreach $sArticle.sVoteComments as $vote}

            {* Review entry *}
            {block name="frontend_detail_comment_block"}
                {include file="frontend/detail/comment/entry.tpl" isLast=$vote@last}
            {/block}

            {* Review answer *}
            {block name="frontend_detail_answer_block"}
                {if $vote.answer}
                    {include file="frontend/detail/comment/answer.tpl" isLast=$vote@last}
                {/if}
            {/block}
        {/foreach}
    {/if}

    {* Publish product review *}
    {block name='frontend_detail_comment_post'}
        <div class="review--form-container">
            {include file="frontend/detail/comment/form.tpl"}
        </div>
    {/block}
</div>
