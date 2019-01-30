{namespace name="frontend/blog/comments"}

{block name='frontend_blog_comments_form'}
    <div class="blog--comments-form">

        {block name='frontend_blog_comments_form_headline'}
            <div class="comments--actions">
                <a class="btn is--primary is--icon-right btn--create-entry"
                   title="{s name="BlogHeaderWriteComment"}{/s}"
                   rel="nofollow"
                   data-collapse-panel="true"
                   data-collapseTarget=".comment--collapse-target">
                    {s name="BlogHeaderWriteComment"}{/s}
                    <i class="icon--arrow-right"></i>
                </a>
            </div>
        {/block}

        {block name='frontend_blog_comments_form_errors'}
            <div class="blog--comments-form-errors">
            {if $sAction == "rating"}
                {if isset($sErrorFlag)}
                    {$type = "error"}
                    {if $sErrorFlag['sCaptcha']}
                        {s name="BlogInfoFailureCaptcha" assign="snippet"}{/s}
                    {elseif $sErrorFlag['invalidHash']}
                        {s name="BlogInfoFailureDoubleOptIn" assign="snippet"}{/s}
                    {else}
                        {s name="BlogInfoFailureFields" assign="snippet"}{/s}
                    {/if}
                {else}
                    {$type = "success"}
                    {if {config name=OptInVote} && !{$smarty.get.sConfirmation} && !{$userLoggedIn}}
                        {s name="BlogInfoSuccessOptin" assign="snippet"}{/s}
                    {else}
                        {s name="BlogInfoSuccess" assign="snippet"}{/s}
                    {/if}
                {/if}
                {include file="frontend/_includes/messages.tpl" type=$type content=$snippet}
            {/if}
            </div>
        {/block}

        <form method="post" class="comment--collapse-target{if $sErrorFlag} collapse--soft-show{/if}" action="{url controller=blog action=rating blogArticle=$sArticle.id}#blog--comments-start">

            <div class="form--comment-add">

                {* Name *}
                {block name='frontend_blog_comments_input_name'}
                    <div class="blog--comments-name">
                        {s name="BlogLabelName" assign="snippetBlogLabelName"}{/s}
                        {s name="RequiredField" namespace="frontend/register/index" assign="snippetRequiredField"}{/s}
                        <input name="name" type="text"
                               placeholder="{$snippetBlogLabelName|escape}{$snippetRequiredField|escape}"
                               required="required" aria-required="true"
                               value="{$sFormData.name|escape}"
                               class="input--field{if $sErrorFlag.name} has--error{/if}" />
                    </div>
                {/block}

                {* E-Mail *}
                {block name='frontend_blog_comments_input_mail'}
                    <div class="blog--comments-email">
                        {s name="BlogLabelMail" assign="snippetBlogLabelMail"}{/s}
                        {s name="RequiredField" namespace="frontend/register/index" assign="snippetRequiredField"}{/s}
                        <input name="eMail" type="email"
                            placeholder="{$snippetBlogLabelMail}{if {config name=OptInVote}}{$snippetRequiredField}{/if}"
                            {if {config name=OptInVote}}required="required" aria-required="true"{/if}
                            value="{$sFormData.eMail|escape}"
                            class="input--field{if $sErrorFlag.eMail} has--error{/if}" />
                    </div>
                {/block}

                {* Summary *}
                {block name='frontend_blog_comments_input_summary'}
                    <div class="blog--comments-summary">
                        {s name="BlogLabelSummary" assign="snippetBlogLabelSummary"}{/s}
                        <input name="headline"
                               type="text"
                               placeholder="{$snippetBlogLabelSummary|escape}{s name="RequiredField" namespace="frontend/register/index"}{/s}"
                               required="required" aria-required="true"
                               value="{$sFormData.headline|escape}"
                               class="input--field{if $sErrorFlag.headline} has--error{/if}" />
                    </div>
                {/block}

                {* Voting *}
                {block name='frontend_blog_comments_input_voting'}
                    <div class="blog--comments-voting select-field">
                        <select required="required" aria-required="true" name="points" class="text{if $sErrorFlag.points} has--error{/if}">
                            <option value="">{s name="BlogLabelRating"}{/s}{s name="RequiredField" namespace="frontend/register/index"}{/s}</option>
                            <option value="10"{if $sFormData.points == 10} selected="selected"{/if}>{s name="rate10"}{/s}</option>
                            <option value="9"{if $sFormData.points == 9} selected="selected"{/if}>{s name="rate9"}{/s}</option>
                            <option value="8"{if $sFormData.points == 8} selected="selected"{/if}>{s name="rate8"}{/s}</option>
                            <option value="7"{if $sFormData.points == 7} selected="selected"{/if}>{s name="rate7"}{/s}</option>
                            <option value="6"{if $sFormData.points == 6} selected="selected"{/if}>{s name="rate6"}{/s}</option>
                            <option value="5"{if $sFormData.points == 5} selected="selected"{/if}>{s name="rate5"}{/s}</option>
                            <option value="4"{if $sFormData.points == 4} selected="selected"{/if}>{s name="rate4"}{/s}</option>
                            <option value="3"{if $sFormData.points == 3} selected="selected"{/if}>{s name="rate3"}{/s}</option>
                            <option value="2"{if $sFormData.points == 2} selected="selected"{/if}>{s name="rate2"}{/s}</option>
                            <option value="1"{if $sFormData.points == 1} selected="selected"{/if}>{s name="rate1"}{/s}</option>
                        </select>
                    </div>
                {/block}

                {* Opinion *}
                {block name='frontend_blog_comments_input_comment'}
                    <div class="blog--comments-opinion">
                        {s name="BlogLabelComment" assign="snippetBlogLabelComment"}{/s}
                        <textarea name="comment" type="text" placeholder="{$snippetBlogLabelComment|escape}" class="input--field{if $sErrorFlag.comment} has--error{/if}" rows="5" cols="5">
                            {$sFormData.comment|escape}
                        </textarea>
                    </div>
                {/block}

                {* Captcha *}
                {block name='frontend_blog_comments_input_captcha'}
                    {if {config name=captchaMethod} === 'legacy'}
                        <div class="blog--comments-captcha">

                            {block name='frontend_blog_comments_input_captcha_placeholder'}
                                <div class="captcha--placeholder" data-autoLoad="true"{if $sErrorFlag.sCaptcha} data-hasError="true"{/if} data-src="{url module=widgets controller=Captcha action=refreshCaptcha}"></div>
                            {/block}

                            {block name='frontend_blog_comments_input_captcha_placeholder'}
                                <strong class="captcha--notice">{s name="BlogLabelCaptcha"}{/s}</strong>
                            {/block}

                            {block name='frontend_blog_comments_input'}
                                <div class="captcha--code">
                                    <input type="text" name="sCaptcha" class="input--field{if $sErrorFlag.sCaptcha} has--error{/if}" required="required" aria-required="true" />
                                </div>
                            {/block}
                        </div>
                    {else}
                        {$captchaName = {config name=captchaMethod}}
                        {$captchaHasError = isset($sErrorFlag) && count($sErrorFlag) > 0}
                        {include file="widgets/captcha/custom_captcha.tpl" captchaName=$captchaName captchaHasError=$captchaHasError}
                    {/if}
                {/block}

                {block name='frontend_blog_comments_input_notice'}
                    <p class="required--notice">{s name="BlogInfoFields"}{/s}</p>
                {/block}

                {* Data protection information *}
                {block name='frontend_blog_comments_input_privacy'}
                    {if {config name=ACTDPRTEXT} || {config name=ACTDPRCHECK}}
                        {include file="frontend/_includes/privacy.tpl"}
                    {/if}
                {/block}

                {* Submit button *}
                {block name='frontend_blog_comments_input_submit'}
                    <input class="btn is--primary" type="submit" name="Submit" value="{s name='BlogLinkSaveComment'}{/s}" />
                {/block}

            </div>

        </form>
    </div>
{/block}