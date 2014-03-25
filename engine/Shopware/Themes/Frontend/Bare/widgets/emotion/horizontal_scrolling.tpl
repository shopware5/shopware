{$style = ''}
{if $sEmotions|@count > 0}
	{foreach $sEmotions as $emotion}
		{if $emotion.elements|@count > 0}

		{$finalEndCol = 1}
		{foreach $emotion.elements as $element}
			{if $finalEndCol < $element.endCol}
				{$finalEndCol=$element.endCol}
			{/if}
		{/foreach}

		{$pages = $finalEndCol / 8}

		<section class="emotion--container emotion--col{$emotion.grid.cols} emotion--{$emotion@index} emotion--horizontal" data-emotions="true" data-max-col="{$emotion.grid.cols}">

			{$style = "{$style}.emotion--{$emotion@index}{ldelim}height:{$emotion.grid.cellHeight * 8}px;{rdelim}"}
			{$style = "{$style}.emotion--list-{$emotion@index}{ldelim}width:{$finalEndCol * 12.5}%{rdelim}"}

			<ul class="emotion--list emotion--list-{$emotion@index}" data-pages="{$pages}">
            	{foreach $emotion.elements as $element}

					{$style = "{$style}.emotion-element--{$emotion@index}-{$element@index}{ldelim}height:{$emotion.grid.cellHeight * (($element.endRow + 1) - $element.startRow)}px;width:{((($element.endCol + 1) - $element.startCol) * 12.5) / $pages}%;left:{($element.startCol - 1) * 12.5 / $pages}%;{rdelim}"}

					<li class="emotion--element {$element.component.cls} emotion-element--{$emotion@index}-{$element@index}" data-col="{$element.startCol - 1}" data-row="{$element.startRow - 1}">

						{$template = $element.component.template}
						{$Data=$element.data}

						{if $template == 'component_article'}
							{include file="widgets/emotion/components/component_article.tpl"}
						{elseif $template == 'component_article_slider'}
							{include file="widgets/emotion/components/component_article_slider.tpl"}
						{elseif $template == 'component_banner'}
							{include file="widgets/emotion/components/component_banner.tpl"}
						{elseif $template == 'component_banner_slider'}
							{include file="widgets/emotion/components/component_banner_slider.tpl"}
						{elseif $template == 'component_blog'}
							{include file="widgets/emotion/components/component_blog.tpl"}
						{elseif $template == 'component_category_teaser'}
							{include file="widgets/emotion/components/component_category_teaser.tpl"}
						{elseif $template == 'component_html'}
							{include file="widgets/emotion/components/component_html.tpl"}
						{elseif $template == 'component_iframe'}
							{include file="widgets/emotion/components/component_iframe.tpl"}
						{elseif $template == 'component_manufacturer_slider'}
							{include file="widgets/emotion/components/component_manufacturer_slider.tpl"}
						{elseif $template == 'component_youtube'}
							{include file="widgets/emotion/components/component_youtube.tpl"}
						{elseif "widgets/emotion/components/{$template}.tpl"|template_exists}
							{include file="widgets/emotion/components/{$element.component.template}.tpl"}
						{else}
							&nbsp;
						{/if}
					</li>
				{/foreach}
			</ul>
		</section>
		{/if}
	{/foreach}
	<style type="text/css">{$style}</style>
{/if}