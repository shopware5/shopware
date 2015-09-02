{extends file='frontend/index/header.tpl'}

{* Javascript *}
{block name="frontend_index_header_javascript" append}
<script type="text/javascript">
//<![CDATA[

	try {
		jQuery(document).ready(function($) {

			$.tabNavi = $('#tabs').tabs();

			/**
	         * Find the comment tab
			 */
			var tabLinks = $('#tabs a'), commentTabIndex;
			tabLinks.each(function(i, el) {
				var $el = $(el);
				if($el.attr('href') == '#comments') {
					commentTabIndex = i;
                    return false;
				}
			});
			commentTabIndex = commentTabIndex - 1;

			{if $sAction == 'ratingAction'}
				$.tabNavi.tabs('select', commentTabIndex);
			{/if}

			if(window.location.hash == '#comments') {
                window.location.hash = '';
				$.tabNavi.tabs('select', commentTabIndex);
			}

			$('.write_comment').click(function(e) {
				e.preventDefault();
				$.tabNavi.tabs('select', commentTabIndex);
				$('html, body').scrollTop( $("#write_comment").offset().top );
			});
		});
	} catch(err) { if(debug) console.log(err) };

	var snippedChoose = "{s name='DetailChooseFirst'}{/s}";
	var isVariant = {if !$sArticle.sVariants}false{else}true{/if};
	var ordernumber = '{$sArticle.ordernumber}';
	var useZoom = '{config name=sUSEZOOMPLUS}';
    var isConfigurator = {if !$sArticle.sConfigurator}false{else}true{/if};

	jQuery.ordernumber = '{$sArticle.ordernumber}';
//]]>
</script>
{/block}

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
        ;(function() {
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

            jQuery(function($) {
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
        })();
        //]]>
    </script>
{/block}
