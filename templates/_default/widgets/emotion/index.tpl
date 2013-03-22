{if $sEmotions|@count > 0}

{foreach $sEmotions as $emotion}

    {* Calculate the cell width and get the cell height from the emotion settings *}
    {$cellWidth = $emotion.containerWidth / $emotion.grid.cols}
    {$cellHeight = $emotion.grid.cellHeight}
    {$finalEndRow = 1}

    <div class="emotion-listing emotion-col{$emotion.grid.cols} emotion-{$emotion@index}" style="width:{$emotion.containerWidth}px">
        {if $emotion.elements.0}
            {foreach $emotion.elements as $element}

                {* Massive calculation =) *}
                {$colWidth = ($element.endCol - $element.startCol) + 1}
                {$colHeight = ($element.endRow - $element.startRow) + 1}
                {$elementWidth = (($element.endCol - $element.startCol) + 1) * $cellWidth}
                {$elementHeight = (($element.endRow - $element.startRow) + 1) * $cellHeight}
                {$left = ($element.startCol - 1) * $cellWidth}
                {$top = ($element.startRow - 1) * $cellHeight}
                {$listingTpl = "listing-{$emotion.grid.cols}col"}

                <div class="emotion-element box{$colWidth}x{$colHeight} col{$colWidth} row{$colHeight}" style="width:{$elementWidth}px; height:{$elementHeight}px;left:{$left}px;top:{$top}px">
                    <div class="emotion-inner-element {$element.component.cls}" style="width:{$elementWidth-$emotion.grid.gutter}px;height:{$elementHeight-$emotion.grid.gutter}px">
                    {if "widgets/emotion/components/{$element.component.template}.tpl"|template_exists}
                        {include file="widgets/emotion/components/{$element.component.template}.tpl"
                            Data=$element.data
                            sArticle=$element.data
                            sTemplate=$listingTpl
                            sColWidth=$colWidth
                            sColHeight=$colHeight
                            sElementHeight=$elementHeight-$emotion.grid.gutter
                            sElementWidth=$elementWidth-$emotion.grid.gutter
                            sCategoryId=$categoryId
                            sController=$Controller
                            sEmotionCols=$emotion.grid.cols
                        }
                    {else}
                        &nbsp;
                    {/if}
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
{/if}