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
			 * @ticket #5712 (intern)
			 * @ticket #100484 (extern)
	         * @author s.pohl
			 * @date 2011-07-27
			 */
			var tabLinks = $('#tabs a'), commentTabIndex;
			tabLinks.each(function(i, el) {
				var $el = $(el);
				if($el.attr('href') == '#comments') {
					commentTabIndex = i;
				}
			});
			commentTabIndex = commentTabIndex - 1;
			
			{if $sAction == 'ratingAction'}
				$.tabNavi.tabs('select', commentTabIndex);
			{/if}
			
			if(window.location.hash == '#bewertung') {
				$.tabNavi.tabs('select', commentTabIndex);
			}
			
			$('.write_comment').click(function(e) {
				e.preventDefault();
				$.tabNavi.tabs('select', commentTabIndex);
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