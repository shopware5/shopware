{extends file="parent:frontend/detail/header.tpl"}

{* Javascript *}
{block name="frontend_index_header_javascript" append}
<script type="text/javascript">
    //<![CDATA[

    {* LastSeenArticle Client Script *}
    ;(function() {
        var getThumbnailSize = function(configThumbnailSize) {
            var thumbnail, thumbnails;
            configThumbnailSize = ~~(1 * configThumbnailSize);
            thumbnails = {$sArticle.image.src|json_encode};
            thumbnail = thumbnails[configThumbnailSize];
            return thumbnail;
        };
        var configLastArticles = {ldelim}
            {foreach $sLastArticlesConfig as $key => $value}
            '{$key}': '{$value}',
            {/foreach}
            'articleId': ~~(1 * '{$sArticle.articleID}'),
            'linkDetailsRewrited': '{$sArticle.linkDetailsRewrited}',
            'articleName': '{$sArticle.articleName}',
            'thumbnail': getThumbnailSize('{$sLastArticles.ThumbnailSize}')
            {rdelim};

        jQuery(function($) {
            var numberOfArticles = '{config name=lastarticlestoshow}';
            var languageCode = '{$Shop->getId()}';

            $('#detail').lastSeenArticlesCollector({
                lastArticles: configLastArticles,
                numArticles: numberOfArticles,
                shopId: languageCode
            });
        });
    })();

    //]]>
</script>
{/block}