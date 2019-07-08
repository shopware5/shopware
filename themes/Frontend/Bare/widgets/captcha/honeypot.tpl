{block name='frontend_widgets_captcha'}
    {block name='frontend_widgets_captcha_input_label'}
        {* This block is neccessary for e.g. screen reader users and should contain instructions to not fill in the field below. *}
        <span class="c-firstname-confirmation">
            {s name="DetailCommentLabelCaptcha" namespace="frontend/detail/comment"}{/s}
        </span>
    {/block}

    {block name='frontend_widgets_captcha_input_code'}
        <input type="text" name="first_name_confirmation" value="" class="c-firstname-confirmation" aria-label="{s name="DetailCommentLabelName" namespace="frontend/detail/comment "}{/s}" autocomplete="captcha-no-autofill"/>
    {/block}
{/block}
