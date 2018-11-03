{namespace name=backend/search/index}

{block name="backend/search/index/result_total"}
    <div class="row customers">

        {block name="backend/search/index/result_header"}
            <div class="header">
                <div class="inner">
                    {s name="title/customers"}Customers{/s}:
                </div>
            </div>
        {/block}

        {block name="backend/search/index/result_content"}
            <div class="result-container">
                {foreach $searchResult.customers as $item}
                    <a onclick="openSearchResult('customers', {$item.id});return false;" href="#"{if $item@iteration is odd by 2} class="odd"{/if}>
                        <span class="name">
                            {if $item.company}{$item.company} {/if}{$item.firstname} {$item.lastname}
                        </span>

                        <span class="desc">
                            {$item.street} {$item.zipcode} {$item.city}
                        </span>

                        <span class="right">{$item.number}</span>
                    </a>
                {/foreach}
            </div>
        {/block}
    </div>
{/block}
