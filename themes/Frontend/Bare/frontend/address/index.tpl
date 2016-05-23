{extends file="frontend/account/index.tpl"}

{* Breadcrumb *}
{block name="frontend_index_start" append}
    {$sBreadcrumb[] = ["name"=>"{s name="AddressesTitle"}My addresses{/s}", "link"=>{url action="index"}]}
{/block}

{* Main content *}
{block name="frontend_index_content"}
    <div class="account--address account--content">

        {* Addresses headline *}
        {block name="frontend_address_headline"}
            <div class="account--welcome">
                <h1 class="panel--title">{s name="AddressesTitle"}My addresses{/s}</h1>
            </div>
        {/block}

        {* Success messages *}
        {block name="frontend_address_success_messages"}
            {if $success}
                {include file="frontend/address/success_messages.tpl" type=$success}
            {/if}
        {/block}

        {* Error messages *}
        {block name="frontend_address_error_messages"}
            {if $error}
                {include file="frontend/address/error_messages.tpl" type=$error}
            {/if}
        {/block}

        {block name="frontend_address_content"}
            <div class="address--content block-group" data-panel-auto-resizer="true">

                {foreach $addresses as $address}
                    {block name="frontend_address_content_item"}
                        <div class="address--content-item">
                            <div class="panel has--border is--rounded block">
                                <div class="address--item-body panel--body is--wide">
                                    {block name="frontend_address_content_item_title"}
                                        {if $sUserData.additional.user.default_shipping_address_id == $address.id || $sUserData.additional.user.default_billing_address_id == $address.id}
                                            <div class="panel--title is--underline">
                                                {if $sUserData.additional.user.default_shipping_address_id == $address.id}
                                                    <div>{s name="AddressesTitleDefaultShippingAddress"}Default shipping address{/s}</div>
                                                {/if}
                                                {if $sUserData.additional.user.default_billing_address_id == $address.id}
                                                    <div>{s name="AddressesTitleDefaultBillingAddress"}Default billing address{/s}</div>
                                                {/if}
                                            </div>
                                        {/if}
                                    {/block}

                                    {block name="frontend_address_content_item_inner"}
                                        {if $address.company}
                                            <p>{$address.company}{if $address.department} - {$address.department}{/if}</p>
                                        {/if}
                                        <div>
                                            {$address.salutation|salutation}
                                            {if {config name="displayprofiletitle"}}
                                                {$address.title}<br/>
                                            {/if}
                                            {$address.firstname} {$address.lastname}<br />
                                            {$address.street}<br />
                                            {if $address.additionalAddressLine1}{$address.additionalAddressLine1}<br />{/if}
                                            {if $address.additionalAddressLine2}{$address.additionalAddressLine2}<br />{/if}
                                            {if {config name=showZipBeforeCity}}{$address.zipcode} {$address.city}{else}{$address.city} {$address.zipcode}{/if}<br />
                                            {if $address.state.name}{$address.state.name}<br />{/if}
                                            {$address.country.name}
                                        </div>
                                    {/block}
                                </div>

                                {block name="frontend_address_content_item_actions"}
                                    <div class="address--item-actions panel--actions is--wide">
                                        {block name="frontend_address_content_item_set_default"}
                                            <div class="address--actions-set-defaults">

                                                {block name="frontend_address_content_item_set_default_shipping"}
                                                    {if $sUserData.additional.user.default_shipping_address_id != $address.id}
                                                        <form action="{url controller="address" action="setDefaultShippingAddress"}" method="post">
                                                            <input type="hidden" name="addressId" value="{$address.id}" />
                                                            <button type="submit" class="btn is--link is--small">{s name="AddressesSetAsDefaultShippingAction"}{/s}</button>
                                                        </form>
                                                    {/if}
                                                {/block}

                                                {block name="frontend_address_content_item_set_default_billing"}
                                                    {if $sUserData.additional.user.default_billing_address_id != $address.id}
                                                        <form action="{url controller="address" action="setDefaultBillingAddress"}" method="post">
                                                            <input type="hidden" name="addressId" value="{$address.id}" />
                                                            <button type="submit" class="btn is--link is--small">{s name="AddressesSetAsDefaultBillingAction"}{/s}</button>
                                                        </form>
                                                    {/if}
                                                {/block}

                                            </div>
                                        {/block}

                                        {block name="frontend_address_content_item_actions_change"}
                                            <a href="{url controller=address action=edit id=$address.id}" title="{s name="AddressesContentItemActionEdit"}Change{/s}" class="btn is--small">
                                                {s name="AddressesContentItemActionEdit"}Change{/s}
                                            </a>
                                        {/block}

                                        {block name="frontend_address_content_item_actions_delete"}
                                            {if $sUserData.additional.user.default_shipping_address_id != $address.id && $sUserData.additional.user.default_billing_address_id != $address.id}
                                                <a href="{url controller=address action=delete id=$address.id}" title="{s name="AddressesContentItemActionDelete"}Delete{/s}" class="btn is--small">
                                                    {s name="AddressesContentItemActionDelete"}Delete{/s}
                                                </a>
                                            {/if}
                                        {/block}
                                    </div>
                                {/block}

                            </div>
                        </div>
                    {/block}
                {/foreach}

                {block name="frontend_address_content_item_create"}
                    <div class="address--content-item address--item-create block">
                        <a href="{url controller=address action=create}" title="{s name="AddressesContentItemActionCreate"}Create new address +{/s}" class="btn is--block is--primary">
                            {s name="AddressesContentItemActionCreate"}Create new address +{/s}
                        </a>
                    </div>
                {/block}
            </div>
        {/block}

    </div>

{/block}