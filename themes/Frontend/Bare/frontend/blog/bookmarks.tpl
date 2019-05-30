{if !$sArticle.sBookmarks}
    {block name='frontend_blog_bookmarks_bookmarks'}
        <div class="blog--bookmarks block">
            <div class="blog--bookmarks-icons">

                {* Twitter *}
                {block name='frontend_blog_bookmarks_twitter'}
                    {s name="BookmarkTwitterShare" assign="snippetBookmarkTwitterShare"}{/s}
                    <a href="http://twitter.com/home?status={$sArticle.title|escape:'url'}+-+{url controller=blog action=detail sCategory=$sArticle.categoryId blogArticle=$sArticle.id}"
                        title="{$snippetBookmarkTwitterShare|escape}"
                        class="blog--bookmark icon--twitter2"
                        rel="nofollow"
                        target="_blank">
                    </a>
                {/block}

                {* Facebook *}
                {block name='frontend_blog_bookmarks_facebook'}
                    {s name="BookmarkFacebookShare" assign="snippetBookmarkFacebookShare"}{/s}
                    <a href="http://www.facebook.com/share.php?v=4&amp;src=bm&amp;u={url controller=blog action=detail sCategory=$sArticle.categoryId blogArticle=$sArticle.id}&amp;t={$sArticle.title|escape:'url'}"
                        title="{$snippetBookmarkFacebookShare|escape}"
                        class="blog--bookmark icon--facebook2"
                        rel="nofollow"
                        target="_blank">
                    </a>
                {/block}

                {* Del.icio.us *}
                {block name='frontend_blog_bookmarks_delicious'}
                    {s name="BookmarkDeliciousShare" assign="snippetBookmarkDeliciousShare"}{/s}
                    <a href="http://del.icio.us/post?url={url controller=blog action=detail sCategory=$sArticle.categoryId blogArticle=$sArticle.id}&amp;title={$sArticle.title|escape:'url'}"
                        title="{$snippetBookmarkDeliciousShare|escape}"
                        class="blog--bookmark icon--delicious"
                        rel="nofollow"
                        target="_blank">
                    </a>
                {/block}

                {* Digg *}
                {block name='frontend_blog_bookmarks_digg'}
                    {s name="BookmarkDiggitShare" assign="snippetBookmarkDiggitShare"}{/s}
                    <a href="http://digg.com/submit?phase=2&amp;url={url controller=blog action=detail sCategory=$sArticle.categoryId blogArticle=$sArticle.id}&amp;title={$sArticle.title|escape:'url'}"
                        title="{$snippetBookmarkDiggitShare|escape}"
                        class="blog--bookmark icon--digg"
                        rel="nofollow"
                        target="_blank">
                    </a>
                {/block}
            </div>
        </div>
    {/block}
{/if}