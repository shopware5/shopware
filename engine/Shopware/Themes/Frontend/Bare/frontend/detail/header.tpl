{extends file='frontend/index/header.tpl'}

{* Meta title *}
{block name="frontend_index_header_title"}{if $sArticle.metaTitle}{$sArticle.metaTitle} | {config name=sShopname}{else}{$smarty.block.parent}{/if}{/block}

{* Keywords *}
{block name="frontend_index_header_meta_keywords"}{if $sArticle.keywords}{$sArticle.keywords}{elseif $sArticle.sDescriptionKeywords}{$sArticle.sDescriptionKeywords}{/if}{/block}

{* Description *}
{block name="frontend_index_header_meta_description"}{if $sArticle.description}{$sArticle.description|escape}{else}{$sArticle.description_long|strip_tags|escape}{/if}{/block}

{* Canonical link *}
{block name='frontend_index_header_canonical'}
<link rel="canonical" href="{url sArticle=$sArticle.articleID title=$sArticle.articleName}" />
{/block}

{* Javascript *}
{block name="frontend_index_header_javascript" append}
    <script type="text/javascript">
        //<![CDATA[

        {* LastSeenArticle Client Script *}
        ;(function($) {
            var getThumbnailSize = function(configThumbnailSize) {
                var thumbnail, thumbnails;
                configThumbnailSize = ~~(1 * configThumbnailSize);
                thumbnails = {$sArticle.image.src|json_encode};
                if(thumbnails) {
                    thumbnail = thumbnails[configThumbnailSize];
                } else {
                    thumbnail = '{link file='frontend/_resources/images/no_picture.jpg'}';
                }
                return thumbnail;
            };
            var configLastArticles = {ldelim}
                {foreach $sLastArticlesConfig as $key => $value}
                '{$key}': '{$value}',
                {/foreach}
                'articleId': ~~(1 * '{$sArticle.articleID}'),
                'linkDetailsRewrited': '{$sArticle.linkDetailsRewrited}',
                'articleName': '{$sArticle.articleName|escape:"javascript"}',
                'thumbnail': getThumbnailSize('{config name=thumb}')
                {rdelim};

            $(document).ready(function() {
                var numberOfArticles = '{config name=lastarticlestoshow}';
                var languageCode = '{$Shop->getId()}';
                var basePath = '{$Shop->getBaseUrl()}';

                $('#detail').lastSeenArticlesCollector({
                    lastArticles: configLastArticles,
                    numArticles: numberOfArticles,
                    shopId: languageCode,
                    basePath: basePath
                });
            });
        })(jQuery);
        //]]>
    </script>
{/block}