{**
 *  Global messages template
 *
 *  The template provides an easy way to display messages in the storefront. The following types are supported:
 *     * error (red)
 *     * success (green)
 *     * warning (yellow)
 *     * info (blue)
 *  The component requires at least the parameters ```content``` and ```type``` to display the message correctly.
 *
 *  ```
 *     {include file="frontend/_includes/messages.tpl" type="error" content="Your content"}
 *  ```
 *
 *  Customized icons can be passed using the icon font to the component using the parameter ```icon```:
 *  ```
 *     {include file="frontend/_includes/messages.tpl" type="error" content="Your content" icon="icon--shopware"}
 *  ```
 *
 *  The border-radius can be modified using the parameter ```borderRadius```. The default behavior contains a
 *  border radius for the message:
 *  ```
 *     {include file="frontend/_includes/messages.tpl" type="error" content="Your content" borderRadius=true}
 *  ```
 *
 * If you need to display a bunch of messages (for example error messages in the registration), you can pass an array
 * of messages to the template using the parameter ```list```:
 *
 * ```
 *    {include file="frontend/_includes/messages.tpl" type="error" list=$error_messages}
 * ```
 *
 * The template also supportes bold texts for the content or list entires, which could be modified using the parameter
 * ```bold```. By default the parameter is set to ```true```.
 *
 * ```
 *    {include file="frontend/_includes/messages.tpl" type="error" content="Your content" bold=false}
 * ```
 *
 * If you need to insert the message into the DOM but don't want to display it, you can use the parameter ```visible```
 * to hide the message on startup. By default the parameter is set to ```true```.
 *
 * ```
 *    {include file="frontend/_includes/messages.tpl" type="error" content="Your content" visible=false}
 * ```
 *}

{* Icon classes *}
{block name="frontend_global_messages_icon_class"}
    {$iconCls = 'icon--check'}
    {if $type == 'error'}
        {$iconCls = 'icon--cross'}
    {elseif $type == 'success'}
        {$iconCls= 'icon--check'}
    {elseif $type == 'warning'}
        {$iconCls = 'icon--warning'}
    {else}
        {$iconCls = 'icon--info'}
    {/if}

    {* Support for customized icons *}
    {if isset($icon) && $icon|@count}
        {$iconCls=$icon}
    {/if}
{/block}

{* Support for non border-radius *}
{block name="frontend_global_messages_border_radius"}
    {$hasBorderRadius=true}
    {if isset($borderRadius)}
        {$hasBorderRadius=$borderRadius}
    {/if}
{/block}

{* Support for bold text *}
{block name="frontend_global_messages_bold"}
    {$isBold=false}
    {if isset($bold)}
        {$isBold=$bold}
    {/if}
{/block}

{* Support for hiding the message on startup *}
{block name="frontend_global_messages_visible"}
    {$isVisible=true}
    {if isset($visible)}
        {$isVisible=$visible}
    {/if}
{/block}

{* Messages container *}
{block name="frontend_global_messages_container"}
    <div class="alert is--{$type}{if $hasBorderRadius && $hasBorderRadius === true} is--rounded{/if}{if $isVisible === false} is--hidden{/if}">

        {* Icon to remove the message *}
        {block name="frontend_global_messages_icon_remove"}
            {if $remoteMessageLink}
                <a class="alert--close icon--cross" data-notification-message-close="true" data-link="{$remoteMessageLink}" title="{s name="Hide" namespace="frontend"}{/s}"></a>
            {/if}
        {/block}

        {* Icon column *}
        {block name="frontend_global_messages_icon"}
            <div class="alert--icon">
                <i class="icon--element {$iconCls}"></i>
            </div>
        {/block}

        {* Content column *}
        {block name="frontend_global_messages_content"}
            <div class="alert--content{if $isBold} is--strong{/if}">
                {if $content && !$list}
                    {$content}
                {else}
                    <ul class="alert--list">
                        {foreach $list as $entry}
                            <li class="list--entry{if $entry@first} is--first{/if}{if $entry@last} is--last{/if}">{$entry}</li>
                        {/foreach}
                    </ul>
                {/if}
            </div>
        {/block}
    </div>
{/block}
