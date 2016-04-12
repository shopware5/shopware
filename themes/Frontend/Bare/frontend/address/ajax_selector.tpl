{block name='frontend_checkout_select_address_modal'}
    {if count($addresses) > 0}
        <div class="modal--container" data-panel-auto-resizer="true">
            {foreach $addresses as $address}
                {block name='frontend_checkout_select_address_modal_item'}
                    <div class="modal--container-item">
                        <div class="panel has--border is--rounded block">
                            {block name='frontend_checkout_select_address_modal_item_body'}
                                <div class="panel--body is--wide">
                                    <b>{$address.firstname} {$address.lastname}</b><br />
                                    {if $address.company}{$address.company}<br/>{/if}
                                    {$address.street}<br />
                                    {if $address.additionalAddressLine1}{$address.additionalAddressLine1}<br />{/if}
                                    {if $address.additionalAddressLine2}{$address.additionalAddressLine2}<br />{/if}
                                    {if {config name=showZipBeforeCity}}{$address.zipcode} {$address.city}{else}{$address.city} {$address.zipcode}{/if}<br />
                                    {$address.country.name}
                                </div>
                            {/block}

                            {block name='frontend_checkout_select_address_modal_item_actions'}
                                <div class="panel--actions">
                                    <button class="btn is--block select-address--btn is--primary is--icon-right" data-preloader-button="true" data-checkFormIsValid="false" data-address-id="{$address.id}" data-target="{$addressTarget}">
                                        {if $addressTarget == 'billing'}
                                            {s name="SelectBillingAddressButton"}Use this address{/s}
                                        {else}
                                            {s name="SelectShippingAddressButton"}Deliver to this address{/s}
                                        {/if}
                                        <span class="icon--arrow-right"></span>
                                    </button>
                                </div>
                            {/block}
                        </div>
                    </div>
                {/block}
            {/foreach}
        </div>
    {else}
        {block name="frontend_address_select_address_modal_empty_addresses"}
            <div class="modal--empty-text">
                {s name="EmptyAddressesText"}{/s}
            </div>
        {/block}
    {/if}
{/block}