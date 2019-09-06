{stripLines}
{block name="frontend_robots_txt_user_agent"}
    User-agent: *
{/block}

{block name="frontend_robots_txt_disallows"}
    {$robotsTxt->setDisallow('/compare')}
    {$robotsTxt->setDisallow('/checkout')}
    {$robotsTxt->setDisallow('/register')}
    {$robotsTxt->setDisallow('/account')}
    {$robotsTxt->setDisallow('/address')}
    {$robotsTxt->setDisallow('/note')}
    {$robotsTxt->setDisallow('/widgets')}
    {$robotsTxt->setDisallow('/listing')}
    {$robotsTxt->setDisallow('/ticket')}
    {$robotsTxt->setDisallow('/tracking')}

    {block name="frontend_robots_txt_disallows_output"}
        {foreach $robotsTxt->getDisallows() as $disallow}
            {$disallow}
        {/foreach}
    {/block}
{/block}

{block name="frontend_robots_txt_allows"}
    {$robotsTxt->setAllow('/widgets')}
    {block name="frontend_robots_txt_allows_output"}
        {foreach $robotsTxt->getAllows() as $allow}
            {$allow}
        {/foreach}
    {/block}
{/block}

{block name="frontend_robots_txt_sitemap"}
    {foreach $robotsTxt->getSitemaps() as $sitemap}
        {$sitemap}
    {/foreach}
{/block}

{*
    @deprecated
    Will be removed in 5.6 without alternative
*}
{block name="frontend_robots_txt_sitemap_mobile"}{/block}
{/stripLines}
