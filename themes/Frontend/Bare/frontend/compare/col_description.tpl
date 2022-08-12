{* Compare Description *}
<div class="compare--group group--small">
    {block name="frontend_compare_article_description_group_list"}
        <ul class="compare--group-list list--head list--unstyled">
            {block name='frontend_article_picture'}
                <li class="list--entry entry--colored entry--picture">
                    {s name="CompareColumnPicture"}{/s}
                </li>
            {/block}
            {block name='frontend_compare_article_name_header'}
                <li class="list--entry entry--name">
                    {s name="CompareColumnName"}{/s}
                </li>
            {/block}
            {block name='frontend_compare_votings_header'}
                {if !{config name="VoteDisable"}}
                    <li class="list--entry entry--voting">
                        {s name="CompareColumnRating"}{/s}
                    </li>
                {/if}
            {/block}
            {block name='frontend_compare_description_header'}
                <li class="list--entry entry--description">
                    {s name="CompareColumnDescription"}{/s}
                </li>
            {/block}
            {block name='frontend_compare_price_header'}
                <li class="list--entry entry--price">
                    {s name="CompareColumnPrice"}{/s}
                </li>
            {/block}
            {foreach $sComparisonsList.properties as $property}
                {block name='frontend_compare_properties_header'}
                    {if $property}
                        <li class="list--entry entry--colored entry--property" data-property-row="{$property@iteration}">
                            {$property}:
                        </li>
                    {/if}
                {/block}
            {/foreach}
        </ul>
    {/block}
</div>
