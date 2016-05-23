{extends file="frontend/address/index.tpl"}
{namespace name="frontend/address/index"}

{* Breadcrumb *}
{block name="frontend_index_start" append}
    {$sBreadcrumb[] = ["name"=>"{s name="AddressesDeleteTitle"}Delete address{/s}", "link"=>{url}]}
{/block}

{* Main content *}
{block name="frontend_index_content"}
    <div class="account--address account--content address--delete">

        {* Addresses headline *}
        {block name="frontend_address_headline"}
            <div class="account--welcome">
                <h1 class="panel--title">{s name="AddressesDeleteTitle"}Delete address{/s}</h1>
            </div>
        {/block}

        {block name="frontend_address_content"}

            {block name="frontend_address_delete_notice"}
                <p>
                    {s name="AddressesDeleteNotice"}<b>Please note:</b> Deleting this address will not delete any pending orders being shipped to this address.{/s}
                    <br/>
                    {s name="AddressesDeleteConfirmText"}To permanently remove this address from your address book, click Confirm.{/s}
                </p>
            {/block}

            {block name="frontend_address_delete_content"}
                <div class="panel has--border is--rounded block">

                    <div class="panel--body is--wide">
                        {block name="frontend_address_delete_content_inner"}
                            {if $address.company}
                                <p>{$address.company}{if $address.department} - {$address.department}{/if}</p>
                            {/if}

                            {$address.salutation|salutation}
                            {if {config name="displayprofiletitle"}}
                                {$address.title}<br/>
                            {/if}
                            {$address.firstname} {$address.lastname}<br />
                            {$address.street}<br />
                            {if $address.additionalAddressLine1}{$address.additionalAddressLine1}<br />{/if}
                            {if $address.additionalAddressLine2}{$address.additionalAddressLine2}<br />{/if}
                            {if {config name=showZipBeforeCity}}{$address.zipCode} {$address.city}{else}{$address.city} {$address.zipCode}{/if}<br />
                            {if $address.state.name}{$address.state.name}<br />{/if}
                            {$address.country.name}
                        {/block}
                    </div>

                </div>
            {/block}

            {block name="frontend_address_delete_actions"}
                <div class="address--delete-actions">
                    <form action="{url controller=address action=delete id=$address.id}" method="post">

                        {block name="frontend_address_delete_actions_cancel"}
                            <a href="{url controller=address action=index}" title="{s name="AddressesDeleteCancelText"}Cancel{/s}" class="btn  is--secondary">
                                {s name="AddressesDeleteCancelText"}Cancel{/s}
                            </a>
                        {/block}

                        {block name="frontend_address_delete_actions_confirm"}
                            <button type="submit" title="{s name="AddressesDeleteButtonText"}Confirm{/s}" class="btn is--primary is--right">
                                {s name="AddressesDeleteButtonText"}Confirm{/s}
                            </button>
                        {/block}
                    </form>
                </div>
            {/block}
        {/block}
    </div>
{/block}