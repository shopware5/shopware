{namespace name="frontend/search/filter_category"}

{function name=categoryTree level=0}

    {if $level == 0}
        {$activeCategoryTree[] = [
            'label' => "{s name="SearchFilterMainCategories"}Hauptkategorien{/s}",
            'link' => '#?c=reset'
        ]}
    {else}
        {$activeCategoryTree[] = [
            'label' => "{$category->getLabel()}",
            'link' => "#?c={$category->getId()}"
        ]}
    {/if}

    {if $category->isActive()}
        <div class="search--cat-filter">

            {block name="frontend_search_category_filter_headline"}
                <div class="cat-filter--headline">
                    <span class="cat-filter--title">{s name="SearchFilterByCategory"}Filtern nach Kategorien{/s} |</span>

                    {foreach $activeCategoryTree as $categoryLink}
                        {block name="frontend_search_category_filter_link"}
                            <span class="cat-filter--link">
                                {if !$categoryLink@first}
                                    <i class="icon--arrow-right"></i>
                                {/if}
                                {if !$categoryLink@last}
                                    <a href="{$categoryLink.link}"
                                       data-action-link="true">
                                        {$categoryLink.label}
                                    </a>
                                {else}
                                    <span>{$categoryLink.label}</span>
                                {/if}
                            </span>
                        {/block}
                    {/foreach}
                </div>
            {/block}

            {if count($category->getValues()) > 0}
                {block name="frontend_search_category_filter_sub_categories"}
                    <div class="cat-filter--sub-categories">
                        {foreach $category->getValues() as $subCategory}
                            <a href="#?c={$subCategory->getId()}" class="cat-filter--sub-cat" data-action-link="true">
                                <i class="icon--arrow-right"></i>{$subCategory->getLabel()}
                            </a>{if !$subCategory@last} |{/if}
                        {/foreach}
                    </div>
                {/block}
            {/if}
        </div>
    {else}
        {$subCategories = $category->getValues()}
        {categoryTree category=$subCategories[0] activeCategoryTree=$activeCategoryTree level=$level+1}
    {/if}
{/function}

{$mainCategories = $facet->getValues()}
{categoryTree category=$mainCategories[0] activeCategoryTree=[]}
