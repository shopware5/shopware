{if $sEmotions.0}

    {* We've just only one emotion in a category at the same time *}
    {$emotion = $sEmotions[0]}

    {* Calculate the cell width and get the cell height from the emotion settings *}
    {$cellWidth = $emotion.containerWidth / $emotion.cols}
    {$cellHeight = $emotion.cellHeight}
    {$finalRowHeight = 1}

    <div class="emotion-listing emotion-col{$emotion.cols}" style="width:{$emotion.containerWidth}px">
        {if $emotion.elements.0}
            {foreach $emotion.elements as $element}

                {* Massive calculation =) *}
                {$colWidth = ($element.endCol - $element.startCol) + 1}
                {$colHeight = ($element.endRow - $element.startRow) + 1}
                {$elementWidth = (($element.endCol - $element.startCol) + 1) * $cellWidth}
                {$elementHeight = (($element.endRow - $element.startRow) + 1) * $cellHeight}
                {$left = ($element.startCol - 1) * $cellWidth}
                {$top = ($element.startRow - 1) * $cellHeight}
                {$listingTpl = "listing-{$emotion.cols}col"}

                <div class="emotion-element box{$colWidth}x{$colHeight} col{$colWidth} row{$colHeight}" style="width:{$elementWidth}px; height:{$elementHeight}px;left:{$left}px;top:{$top}px">
                    <div class="emotion-inner-element {$element.component.cls}" style="width:{$elementWidth-10}px;height:{$elementHeight-10}px">
                    {if "widgets/emotion/components/{$element.component.template}.tpl"|template_exists}
                        {include file="widgets/emotion/components/{$element.component.template}.tpl" Data=$element.data sArticle=$element.data sTemplate=$listingTpl sColWidth=$colWidth sColHeight=$colHeight sElementHeight=$elementHeight-10 sElementWidth=$elementWidth-10 sCategoryId=$categoryId sController=$Controller sEmotionCols=$emotion.cols}
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
        <script type="text/javascript">
            var emotionHeight = '{$finalEndRow * $cellHeight}';
            {literal}
            (function($) {
                $(document).ready(function() {
                    if(typeof(emotionHeight) !== 'undefined' && emotionHeight && emotionHeight > 0) {
                        $('.emotion-listing').css('height', emotionHeight);
                    }
                })
            })(jQuery);
            {/literal}
        </script>
    </div>
{/if}