{extends file="frontend/index/index.tpl"}

{* Breadcrumb *}
{block name="frontend_index_start" append}
    {$sBreadcrumb[] = ["name"=>"{s name="AddressesTitle"}My addresses{/s}", "link"=>{url}]}
{/block}

{* Account Sidebar *}
{block name="frontend_index_left_categories" prepend}
    {block name="frontend_account_sidebar"}
        {include file="frontend/account/sidebar.tpl"}
    {/block}
{/block}

{* Main content *}
{block name="frontend_index_content"}
    <div class="account--addresses account--content">

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

        {block name="frontend_address_content"}
            <div class="addresses--content block-group">

                {foreach $addresses as $address}
                    {block name="frontend_address_content_item"}
                        <div class="addresses--content-item">
                            <div class="panel has--border is--rounded block">

                                <div class="addresses--item-body panel--body is--wide">
                                    {block name="frontend_address_content_item_inner"}
                                        {if $address.company}
                                            <p>{$address.company}{if $address.department} - {$address.department}{/if}</p>
                                        {/if}
                                        <p>
                                            {if $address.salutation eq "mr"}
                                                {s name="RegisterLabelMr" namespace="frontend/register/personal_fieldset"}{/s}
                                            {else}
                                                {s name="RegisterLabelMs" namespace="frontend/register/personal_fieldset"}{/s}
                                            {/if}
                                            {$address.firstname} {$address.lastname}<br />
                                            {$address.street}<br />
                                            {if $address.additionalAddressLine1}{$address.additionalAddressLine1}<br />{/if}
                                            {if $address.additionalAddressLine2}{$address.additionalAddressLine2}<br />{/if}
                                            {if {config name=showZipBeforeCity}}{$address.zipCode} {$address.city}{else}{$address.city} {$address.zipCode}{/if}<br />
                                            {if $address.state.name}{$address.state.name}<br />{/if}
                                            {$address.country.name}
                                        </p>
                                    {/block}
                                </div>

                                {block name="frontend_address_content_item_actions"}
                                    <div class="addresses--item-actions panel--actions is--wide">
                                        <a href="{url controller=address action=edit id=$address.id}" title="{s name="AddressesContentItemActionEdit"}Change{/s}" class="btn is--small">
                                            {s name="AddressesContentItemActionEdit"}Change{/s}
                                        </a>
                                    </div>
                                {/block}

                            </div>
                        </div>
                    {/block}
                {/foreach}

                {block name="frontend_address_content_item_create"}
                    <div class="addresses--content-item addresses--item-create block">
                        <a href="{url controller=address action=create}" title="{s name="AddressesContentItemActionCreate"}Create new address +{/s}" class="btn is--block is--primary">
                            {s name="AddressesContentItemActionCreate"}Create new address +{/s}
                        </a>
                    </div>
                {/block}
            </div>
        {/block}

    </div>

{/block}