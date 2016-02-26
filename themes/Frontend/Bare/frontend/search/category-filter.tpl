{namespace name="frontend/search/filter_category"}

{function name=categoryTree level=0}

    {if $level == 0}
        {$activeCategoryTree[] = [
            'label' => "{s name="SearchFilterMainCategories"}{/s}",
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
                <h3 class="cat-filter--headline">
                    {s name="SearchFilterByCategory"}{/s}
                </h3>
            {/block}

            {block name="frontend_search_category_filter_content"}
                <div class="cat-filter--content">

                    {if count($activeCategoryTree) > 1}

                        {* Category filter reset link *}
                        {block name="frontend_search_category_filter_reset_link"}
                            <a href="#?c=reset" title="{"{s name='SearchFilterLinkDefault'}{/s}"|escape}" class="cat-filter--reset" data-action-link="true">
                                {block name="frontend_search_category_filter_reset_link_icon"}
									<i class="icon--cross"></i>
                                {/block}
                                {s name="SearchFilterLinkDefault"}{/s}
                            </a>
                        {/block}

                        {* Category filter active path links *}
                        {block name="frontend_search_category_filter_active_path"}
                            <div class="cat-filter--active-path">

                                {block name="frontend_search_category_filter_active_path_label"}
                                    <span class="cat-filter--label">
                                        {s name="SaerchFilterActivePathLabel"}{/s}
                                    </span>
                                {/block}

                                {foreach $activeCategoryTree as $key => $categoryLink}

                                    {if $categoryLink@first}
                                        {continue}
                                    {/if}

                                    {block name="frontend_search_category_filter_link"}
                                        <span class="cat-filter--path">
                                            {if $key !== 1}
                                                {block name="frontend_search_category_filter_link_spacer_icon"}
                                                    <i class="icon--arrow-right"></i>
                                                {/block}
                                            {/if}

                                            {if !$categoryLink@last}
                                                <a href="{$categoryLink.link}" title="{$categoryLink.label|escape}" class="cat-filter--link" data-action-link="true">
                                                    {block name="frontend_search_category_filter_link_icon"}
                                                        <span class="checkbox is--active">
                                                            <span class="checkbox--state"></span>
                                                        </span>
                                                    {/block}

                                                    {block name="frontend_search_category_filter_link_label"}
                                                        {$categoryLink.label}
                                                    {/block}
                                                </a>
                                            {else}
                                                {block name="frontend_search_category_filter_link_active"}
                                                    <span class="cat-filter--active-cat">{$categoryLink.label}</span>
                                                {/block}
                                            {/if}
                                        </span>
                                    {/block}
                                {/foreach}
                            </div>
                        {/block}
                    {/if}

                    {* Subcategory links *}
                    {if count($category->getValues()) > 0}
                        {block name="frontend_search_category_filter_sub_categories"}
                            <div class="cat-filter--sub-categories">
                                {foreach $category->getValues() as $subCategory}
                                    {block name="frontend_search_category_filter_sub_category_link"}
                                        <a href="#?c={$subCategory->getId()}" title="{$subCategory->getLabel()|escape}" class="cat-filter--sub-cat" data-action-link="true">
                                            {block name="frontend_search_category_filter_sub_category_link_icon"}
                                                <span class="checkbox">
                                                    <span class="checkbox--state"></span>
                                                </span>
                                            {/block}

                                            {block name="frontend_search_category_filter_sub_category_link_label"}
                                                {$subCategory->getLabel()}
                                            {/block}
                                        </a>
                                    {/block}
                                {/foreach}
                            </div>
                        {/block}
                    {/if}
                </div>
            {/block}
        </div>
    {else}
        {$subCategories = $category->getValues()}
        {categoryTree category=$subCategories[0] activeCategoryTree=$activeCategoryTree level=$level+1}
    {/if}
{/function}

{$mainCategories = $facet->getValues()}
{categoryTree category=$mainCategories[0] activeCategoryTree=[]}
