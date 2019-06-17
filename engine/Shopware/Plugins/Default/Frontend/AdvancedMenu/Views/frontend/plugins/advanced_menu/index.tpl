{function name="categories_top" level=0}

    {$columnIndex = 0}
    {$menuSizePercentage = 100 - (25 * $columnAmount * intval($hasTeaser))}
    {$columnCount = 4 - ($columnAmount * intval($hasTeaser))}

    <ul class="menu--list menu--level-{$level} columns--{$columnCount}"{if $level === 0} style="width: {$menuSizePercentage}%;"{/if}>
        {block name="frontend_plugins_advanced_menu_list"}
            {foreach $categories as $category}
                {if $category.hideTop}
                    {continue}
                {/if}

                {$categoryLink = $category.link}
                {if $category.external}
                    {$categoryLink = $category.external}
                {/if}

                <li class="menu--list-item item--level-{$level}"{if $level === 0} style="width: 100%"{/if}>
                    {block name="frontend_plugins_advanced_menu_list_item"}
                        <a href="{$categoryLink|escapeHtml}" class="menu--list-item-link" aria-label="{$category.name|escape}" title="{$category.name|escape}"{if $category.external && $category.externalTarget} target="{$category.externalTarget}"{/if}>{$category.name}</a>

                        {if $category.sub}
                            {call name=categories_top categories=$category.sub level=$level+1}
                        {/if}
                    {/block}
                </li>
            {/foreach}
        {/block}
    </ul>
{/function}

<div class="advanced-menu" data-advanced-menu="true" data-hoverDelay="{$hoverDelay}">
    {block name="frontend_plugins_advanced_menu"}
        {foreach $sAdvancedMenu as $mainCategory}
            {if !$mainCategory.active || $mainCategory.hideTop}
                {continue}
            {/if}

            {$link = $mainCategory.link}
            {if $mainCategory.external}
                {$link = $mainCategory.external}
            {/if}

            {$hasCategories = $mainCategory.activeCategories > 0  && $columnAmount < 4}
            {$hasTeaser = (!empty($mainCategory.media) || !empty($mainCategory.cmsHeadline) || !empty($mainCategory.cmsText)) && $columnAmount > 0}

            <div class="menu--container">
                {block name="frontend_plugins_advanced_menu_main_container"}
                    <div class="button-container">
                        {block name="frontend_plugins_advanced_menu_button_category"}
                            <a href="{$link|escapeHtml}" class="button--category" aria-label="{s name="toCategoryBtn" namespace="frontend/plugins/advanced_menu/advanced_menu"}{/s}{$mainCategory.name|escape:'html'}" title="{s name="toCategoryBtn" namespace="frontend/plugins/advanced_menu/advanced_menu"}{/s}{$mainCategory.name|escape:'html'}"{if $mainCategory.external && $category.externalTarget} target="{$mainCategory.externalTarget}"{/if}>
                                <i class="icon--arrow-right"></i>
                                {s name="toCategoryBtn" namespace="frontend/plugins/advanced_menu/advanced_menu"}{/s}{$mainCategory.name}
                            </a>
                        {/block}

                        {block name="frontend_plugins_advanced_menu_button_close"}
                            <span class="button--close">
                                <i class="icon--cross"></i>
                            </span>
                        {/block}
                    </div>

                    {if $hasCategories || $hasTeaser}
                        <div class="content--wrapper{if $hasCategories} has--content{/if}{if $hasTeaser} has--teaser{/if}">
                            {if $hasCategories}
                                {block name="frontend_plugins_advanced_menu_sub_categories"}
                                    {call name="categories_top" categories=$mainCategory.sub}
                                {/block}
                            {/if}

                            {if $hasTeaser}
                                {block name="frontend_plugins_advanced_menu_teaser"}
                                    {if $hasCategories}
                                        <div class="menu--delimiter" style="right: {$columnAmount * 25}%;"></div>
                                    {/if}
                                    <div class="menu--teaser"{if $hasCategories} style="width: {$columnAmount * 25}%;"{else} style="width: 100%;"{/if}>
                                        {if !empty($mainCategory.media)}
                                            <a href="{$link|escapeHtml}" aria-label="{s name="toCategoryBtn" namespace="frontend/plugins/advanced_menu/advanced_menu"}{/s}{$mainCategory.name|escape:'html'}" title="{s name="toCategoryBtn" namespace="frontend/plugins/advanced_menu/advanced_menu"}{/s}{$mainCategory.name|escape:'html'}" class="teaser--image" style="background-image: url({link file={$mainCategory.media.path}});"{if $mainCategory.external && $category.externalTarget} target="{$mainCategory.externalTarget}"{/if}></a>
                                        {/if}

                                        {if !empty($mainCategory.cmsHeadline)}
                                            <div class="teaser--headline">{$mainCategory.cmsHeadline}</div>
                                        {/if}

                                        {if !empty($mainCategory.cmsText)}
                                            <div class="teaser--text">
                                                {$mainCategory.cmsText|strip_tags|truncate:250:"..."}
                                                <a class="teaser--text-link" href="{$link|escapeHtml}" aria-label="{s name="learnMoreLink" namespace="frontend/plugins/advanced_menu/advanced_menu"}mehr erfahren{/s}" title="{s name="learnMoreLink" namespace="frontend/plugins/advanced_menu/advanced_menu"}mehr erfahren{/s}">
                                                    {s name="learnMoreLink" namespace="frontend/plugins/advanced_menu/advanced_menu"}mehr erfahren{/s}
                                                </a>
                                            </div>
                                        {/if}
                                    </div>
                                {/block}
                            {/if}
                        </div>
                    {/if}
                {/block}
            </div>
        {/foreach}
    {/block}
</div>
