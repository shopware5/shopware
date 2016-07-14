{namespace name="frontend/listing/listing_actions"}

{block name="frontend_listing_filter_facet_value_list"}
	<div class="filter-panel filter--property facet--{$facet->getFacetName()|escape:'htmlall'}"
		 data-filter-type="value-list"
		 data-field-name="{$facet->getFieldName()|escape:'htmlall'}">

		{block name="frontend_listing_filter_facet_value_list_flyout"}
			<div class="filter-panel--flyout">

				{block name="frontend_listing_filter_facet_value_list_title"}
					<label class="filter-panel--title">
						{$facet->getLabel()|escape}
					</label>
				{/block}

				{block name="frontend_listing_filter_facet_value_list_icon"}
					<span class="filter-panel--icon"></span>
				{/block}

				{block name="frontend_listing_filter_facet_value_list_content"}
					<div class="filter-panel--content">

						{block name="frontend_listing_filter_facet_value_list_list"}
							<ul class="filter-panel--option-list">

								{foreach $facet->getValues() as $option}

									{block name="frontend_listing_filter_facet_value_list_option"}
										<li class="filter-panel--option">

                                            {block name="frontend_listing_filter_facet_value_list_option_container"}
                                                <div class="option--container">

                                                    {block name="frontend_listing_filter_facet_value_list_input"}
                                                        <span class="filter-panel--checkbox">
                                                            <input type="checkbox"
                                                                   id="__{$facet->getFieldName()|escape:'htmlall'}__{$option->getId()|escape:'htmlall'}"
                                                                   name="__{$facet->getFieldName()|escape:'htmlall'}__{$option->getId()|escape:'htmlall'}"
                                                                   value="{$option->getId()|escape:'htmlall'}"
                                                                   {if $option->isActive()}checked="checked" {/if}/>

                                                            <span class="checkbox--state">&nbsp;</span>
                                                        </span>
                                                    {/block}

                                                    {block name="frontend_listing_filter_facet_value_list_label"}
                                                        <label class="filter-panel--label"
                                                               for="__{$facet->getFieldName()|escape:'htmlall'}__{$option->getId()|escape:'htmlall'}">
                                                            {$option->getLabel()|escape}
                                                        </label>
                                                    {/block}
                                                </div>
                                            {/block}
										</li>
									{/block}
								{/foreach}
							</ul>
						{/block}
					</div>
				{/block}
			</div>
		{/block}
	</div>
{/block}
