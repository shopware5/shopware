{if !$sArticle.sBookmarks}
    {block name='frontend_blog_bookmarks_bookmarks'}
        <div class="blog--bookmarks block">
            <div class="blog--bookmarks-icons">

                {* Twitter *}
                {block name='frontend_blog_bookmarks_twitter'}
                    {s name="BookmarkTwitterShare" assign="snippetBookmarkTwitterShare"}{/s}
                    <a href="https://twitter.com/intent/tweet?text={$sArticle.title|escape:'url'}+-+{url controller=blog action=detail sCategory=$sArticle.categoryId blogArticle=$sArticle.id}"
                        title="{$snippetBookmarkTwitterShare|escape}"
                        class="blog--bookmark icon--twitter2"
                        rel="nofollow"
                        target="_blank">
                    </a>
                {/block}

                {* Facebook *}
                {block name='frontend_blog_bookmarks_facebook'}
                    {s name="BookmarkFacebookShare" assign="snippetBookmarkFacebookShare"}{/s}
                    <a href="https://www.facebook.com/share.php?v=4&amp;src=bm&amp;u={url controller=blog action=detail sCategory=$sArticle.categoryId blogArticle=$sArticle.id}&amp;t={$sArticle.title|escape:'url'}"
                        title="{$snippetBookmarkFacebookShare|escape}"
                        class="blog--bookmark icon--facebook2"
                        rel="nofollow"
                        target="_blank">
                    </a>
                {/block}
            </div>
        </div>
    {/block}
{/if}
