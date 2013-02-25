{if $sEmotions|@count > 0}

    {foreach $sEmotions as $emotion}

    {* Calculate the cell width and get the cell height from the emotion settings *}
        {$cellWidth = $emotion.containerWidth / $emotion.cols}
        {$cellHeight = $emotion.cellHeight}
        {$finalRowHeight = 1}

    <div class="emotion-listing emotion-col{$emotion.cols} emotion-{$emotion@index}" style="width:{$emotion.containerWidth}px">
        {if $emotion.elements.0}
            {foreach $emotion.elements as $element}

                {* Massive calculation *}
                {$colWidth = ($element.endCol - $element.startCol) + 1}
                {$colHeight = ($element.endRow - $element.startRow) + 1}
                {$elementWidth = (($element.endCol - $element.startCol) + 1) * $cellWidth}
                {$elementHeight = (($element.endRow - $element.startRow) + 1) * $cellHeight}
                {$left = ($element.startCol - 1) * $cellWidth}
                {$top = ($element.startRow - 1) * $cellHeight}
                {$listingTpl = "listing-{$emotion.cols}col"}
                {$template = $element.component.template}

                {* Inner template vars *}
                {$Data=$element.data}
                {$sArticle=$element.data}
                {$sTemplate=$listingTpl}
                {$sColWidth=$colWidth}
                {$sColHeight=$colHeight}
                {$sElementHeight=$elementHeight-10}
                {$sElementWidth=$elementWidth-10}
                {$sCategoryId=$categoryId}
                {$sEmotionCols=$emotion.cols}

                <div class="emotion-element box{$colWidth}x{$colHeight} col{$colWidth} row{$colHeight}" style="width:{$elementWidth}px; height:{$elementHeight}px;left:{$left}px;top:{$top}px">
                    <div class="emotion-inner-element {$element.component.cls}" style="width:{$elementWidth-10}px;height:{$elementHeight-10}px">
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
        <script type="text/javascript">
            var emotionHeight{$emotion@index} = '{$finalEndRow * $cellHeight}';
            jQuery('.emotion-{$emotion@index}').css('height', emotionHeight{$emotion@index});
        </script>
        {$finalEndRow=1}
    </div>
    {/foreach}
{/if}