{extends file="parent:frontend/detail/header.tpl"}

{* Javascript *}
{block name="frontend_index_header_javascript" append}
<script type="text/javascript">
    //<![CDATA[

    {* LastSeenArticle Client Script *}
    var getThumbnailSize = function(configThumbnailSize) {
        var thumbnail;

        configThumbnailSize = ~~(1 * configThumbnailSize);
        if(configThumbnailSize == 1) thumbnail = '{$sArticle.image.src.1}';
        else if(configThumbnailSize == 2) thumbnail = '{$sArticle.image.src.2}';
        else if(configThumbnailSize == 3) thumbnail = '{$sArticle.image.src.3}';
        else if(configThumbnailSize == 4) thumbnail = '{$sArticle.image.src.4}';
        else if(configThumbnailSize == 5) thumbnail = '{$sArticle.image.src.5}';
        else thumbnail = '{$sArticle.image.src.2}';

        return thumbnail;
    };
    var configLastArticles = {ldelim}
        {foreach $sLastArticlesConfig as $key => $value}
        '{$key}': '{$value}',
        {/foreach}
        'articleId': ~~(1 * '{$sArticle.articleID}'),
        'linkDetailsRewrited': '{$sArticle.linkDetailsRewrited}',
        'articleName': '{$sArticle.articleName}',
        'thumbnail': getThumbnailSize('{$sLastArticlesConfig.thumbnail}'),
        {rdelim};

    jQuery(function($) {
        $('#detail').lastSeenArticlesCollector(configLastArticles);
    });
    //]]>
</script>
{/block}