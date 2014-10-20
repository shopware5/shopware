{$device[0] = 'desktop'}
{$device[1] = 'tablet'}
{$device[2] = 'mobile'}

{if $sEmotions|@count > 0}
    {$style = ''}

    {foreach $sEmotions as $emotion}

        {if $emotion.grid}

            {* Calculate the cell width and get the cell height from the emotion settings *}
            {$cellHeight = $emotion.grid.cellHeight}
            {$finalEndRow = 1}
            {$deviceName = {$device[$emotion.device]}}

            {* Get the last row *}
            {foreach $emotion.elements as $element}
                {if $finalEndRow < $element.endRow}
                    {$finalEndRow=$element.endRow}
                {/if}
            {/foreach}

            {* Set up the basic styling for the emotion world container, so we can position all elements into it *}
            {$style = "{$style}.emotion--{$emotion@index}{ldelim}margin-right:-{$emotion.grid.gutter}px;margin-right:-{$emotion.grid.gutter / 16}rem;margin-bottom:-{$emotion.grid.gutter}px;margin-bottom:-{$emotion.grid.gutter}px;margin-bottom:-{$emotion.grid.gutter / 16}rem;height:{($cellHeight * $finalEndRow)}px;height:{($cellHeight * $finalEndRow / 16)}rem;{rdelim}"}

            {block name="widgets/emotion/index/container"}
                {if $emotion.fullscreen}<div class="emotion--outer-container emotion--{$emotion.mode} emotion--{$deviceName}" style="height:{($cellHeight * $finalEndRow)}px;height:{($cellHeight * $finalEndRow / 16)}rem;">{/if}
                <section class="emotion--container emotion--{$deviceName} emotion--col{$emotion.grid.cols} emotion--{$emotion.mode} emotion--{$emotion@index}{if $emotion.fullscreen} emotion--fullscreen{/if}" data-emotion="true" data-mode="{$emotion.mode}" data-fullscreen="{if $emotion.fullscreen}true{else}false{/if}" data-last-row="{$finalEndRow}" data-max-col="{$emotion.grid.cols}" data-cell-height="{$cellHeight}">
                    <div class="grid--sizer"></div>
                    <div class="gutter--sizer"></div>

                    {if $emotion.elements.0}
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

                            {$style = "{$style}.emotion-element--{$emotion@index}-{$element@index}{ldelim}height:{$elementHeight}rem;{rdelim}"}

                            {* We have to add additional styles to the emotion elements for the "resize" mode *}
                            {if $emotion.mode === 'resize'}
                                {$style = "{$style}.emotion-element--{$emotion@index}-{$element@index}{ldelim}left:{$left}%;top:{$top}%;width:{$elementWidth}%; padding:0 1% 1% 0;{rdelim}"}

                                {if $emotion.fullscreen}
                                    {$style = "{$style}.emotion-element--{$emotion@index}-{$element@index}{ldelim}top:{$top + 1}%;{rdelim}"}
                                {/if}
                            {/if}

                            <div class="emotion--element emotion--element-cols-{$colWidth} {$element.component.cls} emotion-element--{$emotion@index}-{$element@index}{if $element.endRow == $finalEndRow} is--last-row{/if}" data-col="{$element.startCol - 1}" data-row="{$element.startRow - 1}">
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
                                    {/if}
                                {/block}
                            </div>
                        {/foreach}
                    {/if}
                    {$finalEndRow=1}
                </section>
                {if $emotion.fullscreen}</div>{/if}
            {/block}
        {/if}
    {/foreach}
    <style type="text/css">{$style}</style>
{/if}
