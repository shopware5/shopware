{if $sEmotions|@count > 0}
{$style = ''}
{foreach $sEmotions as $emotion}

    {* Calculate the cell width and get the cell height from the emotion settings *}
    {$cellHeight = $emotion.grid.cellHeight}
    {$finalEndRow = 1}

	{* Get the last row *}
	{foreach $emotion.elements as $element}
		{if $finalEndRow < $element.endRow}
      		{$finalEndRow=$element.endRow}
  		{/if}
	{/foreach}

	{$style = "{$style}.emotion--{$emotion@index}{ldelim}padding-left: {$emotion.grid.gutter / 16}em;{rdelim}"}

    <section class="emotion--container emotion--col{$emotion.grid.cols} emotion--{$emotion@index}" data-emotions="true">

        {if $emotion.elements.0}

			<header class="emotion--header">
				<h1 class="header--headline">{$emotion.name}</h1>

				<a href="#full-screen--mode" class="header--link" data-open="Einkaufswelt bildschirmfüllend anzeigen" data-close="Einkaufswelt bildschirmfüllend beenden">
					<i class="icon--layout"></i> <span class="link--text">Einkaufswelt bildschirmfüllend anzeigen</span>
				</a>
			</header>

			<ul class="emotion--list emotion--list-{$emotion@index}">
            {foreach $emotion.elements as $element}

                {* Massive calculation *}
                {$colWidth = ($element.endCol - $element.startCol) + 1}
                {$colHeight = ($element.endRow - $element.startRow) + 1}
                {$elementWidth = {((($element.endCol - $element.startCol) + 1) / $emotion.grid.cols) * 100}}
				{$elementHeight = ((($element.endRow - $element.startRow) + 1) * $cellHeight) / 16}
                {$left = (($element.startCol - 1) / $emotion.grid.cols) * 100}
                {$top = (($element.startRow - 1) / $finalEndRow) * 100}
                {$listingTpl = "listing-{$emotion.grid.cols}col"}
                {$template = $element.component.template}

                {* Inner template vars *}
                {$Data=$element.data}
                {$sArticle=$element.data}
                {$sTemplate=$listingTpl}
                {$sColWidth=$colWidth}
                {$sColHeight=$colHeight}
                {$sElementHeight=$elementHeight-$emotion.grid.gutter}
                {$sElementWidth=$elementWidth-$emotion.grid.gutter}
                {$sCategoryId=$categoryId}
                {$sController=$Controller}
                {$sEmotionCols=$emotion.grid.cols}

                {$style = "{$style}.emotion-element--{$emotion@index}-{$element@index}{ldelim}width:{$elementWidth}%;height:{$elementHeight}em;left:{$left}%;top:{$top}%;padding-right:{$emotion.grid.gutter / 16}em;padding-bottom:{$emotion.grid.gutter / 16}em{rdelim}"}
                
                <li class="emotion--element {$element.component.cls} emotion-element--{$emotion@index}-{$element@index}">
                    {block name="widgets/emotion/index/inner-element"}
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
                    {/block}
                </li>
            {/foreach}
			</ul>
        {/if}
        {$finalEndRow=1}
    </section>
{/foreach}
<style type="text/css">{$style}</style>
{/if}