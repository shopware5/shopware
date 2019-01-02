{namespace name=backend/search/index}

{block name="backend/search/index/result_total"}
    <div class="row articles">

        {block name="backend/search/index/result_header"}
            <div class="header">
                <div class="inner">
                    {s name="title/orders"}Orders{/s}:
                </div>
            </div>
        {/block}

        {block name="backend/search/index/result_content"}
            <div class="result-container">
                {foreach $searchResult.orders as $item}
                    <a onclick="openSearchResult('orders', {$item.id});return false;" href="#"{if $item@iteration is odd by 2} class="odd"{/if}>
                        <span class="name">{$item.number}: {$item.orderStateName|truncate:30} </span>
                        <span class="desc">{if $item.company}{$item.company} {/if}{$item.firstname} {$item.lastname} {$item.street} {$item.zipcode} {$item.city}</span>
                        <span class="desc">{$item.shopName} / {$item.dispatchName} / {$item.paymentName}</span>
                        <span class="right">{$item.invoiceAmount|currency}</span>
                    </a>
                {/foreach}
            </div>
        {/block}
    </div>
{/block}
