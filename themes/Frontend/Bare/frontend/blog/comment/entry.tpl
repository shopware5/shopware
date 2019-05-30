{namespace name="frontend/blog/comments"}

{if $sArticle.comments}

    {* List comments *}
    {block name='frontend_blog_comments_comment'}
        <ul class="comments--list list--unstyled">

            {foreach $sArticle.comments as $vote}
                <li class="list--entry" itemscope itemtype="http://schema.org/UserComments">

                    {* Comment meta data *}
                    {block name='frontend_blog_comments_comment_meta'}
                        <div class="entry--meta">

                            {* Stars *}
                            {block name='frontend_blog_comments_comment_rating'}
                                <div class="meta--rating">
                                    {include file="frontend/_includes/rating.tpl" points=$vote.points microData=false}
                                </div>
                            {/block}

                            {* Author *}
                            {block name='frontend_blog_comments_comment_author'}
                                <div class="meta--author">

                                    {block name='frontend_blog_comments_comment_author_label'}
                                        {* Legacy block with typo. *}
                                        {block name='frontned_blog_comments_comment_author_label'}
                                                <strong class="author--label">
                                                {s name="DetailCommentInfoFrom" namespace='frontend/detail/comment'}{/s}
                                                </strong>
                                        {/block}
                                    {/block}

                                    {block name='frontend_blog_comments_comment_author_name'}
                                        {* Legacy block with typo. *}
                                        {block name='frontned_blog_comments_comment_author_name'}
                                                <span itemprop="creator" class="author--name">{$vote.name|escapeHtml}</span>
                                        {/block}
                                    {/block}

                                </div>
                            {/block}

                            {* Date *}
                            {block name='frontend_blog_comments_comment_date'}
                                <div class="meta--date">

                                    {block name='frontend_blog_commetns_comment_date_label'}
                                        <strong class="date--label">
                                            {s name="DetailCommentInfoAt" namespace='frontend/detail/comment'}{/s}
                                        </strong>
                                    {/block}

                                    {block name='frontend_blog_comments_comment_date_creationdate'}
                                        <meta itemprop="commentTime" content="{$vote.creationDate->format(DateTime::ATOM)|escapeHtml}">
                                        <span class="date--creation">
                                            {$vote.creationDate|date:date_long}
                                        </span>
                                    {/block}

                                </div>
                            {/block}

                        </div>
                    {/block}

                    {* Comment Content *}
                    {block name='frontend_blog_comments_comment_right'}
                        <div class="entry--content">
                            {* Comments headline *}
                            {block name='frontend_blog_comments_comment_headline'}
                                <h3 class="content--headline">{$vote.headline|escapeHtml}</h3>
                            {/block}

                            {* Comment *}
                            {block name='frontend_blog_comments_comment_text'}
                                <p class="content--comment" itemprop="commentText">
                                    {$vote.comment|escapeHtml|nl2br}
                                </p>
                            {/block}
                        </div>
                    {/block}

                </li>
            {/foreach}

        </ul>
    {/block}
{/if}
