
{if $sEmotions|@count > 0}
{$style = ''}
{foreach $sEmotions as $emotion}

    {* Calculate the cell width and get the cell height from the emotion settings *}
    {$cellWidth = $emotion.containerWidth / $emotion.grid.cols}
    {$cellHeight = $emotion.grid.cellHeight}
    {$finalEndRow = 1}

    <div class="emotion-listing emotion-col{$emotion.grid.cols} emotion-{$emotion@index}" style="width:{$emotion.containerWidth}px">
        {if $emotion.elements.0}
            {foreach $emotion.elements as $element}

                {* Massive calculation *}
                {$colWidth = ($element.endCol - $element.startCol) + 1}
                {$colHeight = ($element.endRow - $element.startRow) + 1}
                {$elementWidth = (($element.endCol - $element.startCol) + 1) * $cellWidth}
                {$elementHeight = (($element.endRow - $element.startRow) + 1) * $cellHeight}
                {$left = ($element.startCol - 1) * $cellWidth}
                {$top = ($element.startRow - 1) * $cellHeight}
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

                {$style = "{$style}.emotion-element-{$emotion@index}-{$element@index}{ldelim}width:{$elementWidth}px;height:{$elementHeight}px;left:{$left}px;top:{$top}px{rdelim}"}
                {$style = "{$style}.emotion-inner-element-{$emotion@index}-{$element@index}{ldelim}width:{$elementWidth-$emotion.grid.gutter}px;height:{$elementHeight-$emotion.grid.gutter}px{rdelim}"}
                
                <div class="emotion-element emotion-element-{$emotion@index}-{$element@index} box{$colWidth}x{$colHeight} col{$colWidth} row{$colHeight}">
                    <div class="emotion-inner-element emotion-inner-element-{$emotion@index}-{$element@index} {$element.component.cls}">
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
                    </div>
                </div>

                {* Get the last row to compute the final height of the emotion world *}
                {if $finalEndRow < $element.endRow}
                    {$finalEndRow=$element.endRow}
                {/if}
            {/foreach}
        {/if}
        <div class="emotion-spacer" style="height:{$finalEndRow * $cellHeight}px"></div>
        {$finalEndRow=1}
    </div>
{/foreach}
<style type="text/css">{$style}</style>
{/if}
