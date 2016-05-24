{if $sEmotions|@count > 0}
    {foreach $sEmotions as $emotion}

        {if $emotion.grid}

            {block name="widgets/emotion/index/config"}
                {$cellHeight = $emotion.grid.cellHeight}
                {$cellWidth = 100 / $emotion.grid.cols}
                {$cellSpacing = $emotion.grid.gutter}
                {$baseWidth = 1160}

                {if $Controller == 'listing' && $theme.displaySidebar}
                    {$baseWidth = 900}
                {/if}

                {$lastRow = 0}
            {/block}

            {block name="widgets/emotion/index/container"}

                <section class="emotion--container emotion--column-{$emotion.grid.cols} emotion--mode-{$emotion.mode} emotion--{$emotion@index}"
                         data-emotion="true"
                         data-gridMode="{$emotion.mode}"
                         data-cellSpacing="{$cellSpacing}"
                         data-fullscreen="{if $emotion.fullscreen}true{else}false{/if}"
                         data-columns="{$emotion.grid.cols}"
                         data-cellHeight="{$cellHeight}"
                         data-baseWidth="{$baseWidth}">

                    {if $emotion.elements.0}
                        {foreach $emotion.elements as $element}
                            {block name="widgets/emotion/index/element"}

                                {$template = $element.component.template}
                                {$Data = $element.data}

                                {$itemCols = ($element.endCol - $element.startCol) + 1}
                                {$itemRows = ($element.endRow - $element.startRow) + 1}
                                {$itemHeight = $itemRows * ($cellHeight + $cellSpacing)}
                                {$itemTop = ($element.startRow - 1) * ($cellHeight + $cellSpacing)}
                                {$itemLeft = $cellWidth * ($element.startCol - 1)}

                                {if $lastRow < $element.endRow}
                                    {$lastRow = $element.endRow}
                                {/if}

                                {strip}
                                <div class="emotion--element column--{$itemCols} row--{$itemRows}"
                                     style="padding-left: {$cellSpacing / 16}rem;
                                            padding-bottom: {$cellSpacing / 16}rem;
                                            height: {$itemHeight / 16}rem;
                                            top: {$itemTop / 16}rem;
                                            left: {$itemLeft}%;">

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
                                {/strip}
                            {/block}
                        {/foreach}

                        {block name="widgets/emotion/index/sizer"}
                            {$containerHeight = $lastRow * ($cellHeight + $cellSpacing)}

                            <div class="emotion--sizer column--1"{if $emotion.mode == 'resize'} style="height: {$containerHeight / 16}rem;"{/if}></div>
                        {/block}
                    {/if}
                </section>
            {/block}
        {/if}
    {/foreach}
{/if}
