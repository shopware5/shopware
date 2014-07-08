{namespace name="frontend/detail/description"}

{block name="frontend_detail_description"}
<div class="content--description">
	
	{* Headline *}
	{block name='frontend_detail_description_title'}
		<h2 class="content--title">
			{s name="DetailDescriptionHeader"}{/s} "{$sArticle.articleName}"
		</h2>
	{/block}
	
	{* Properties *}
	{block name='frontend_detail_description_properties'}
		{if $sArticle.sProperties}
			<ul class="description--properties">

				{foreach $sArticle.sProperties as $sProperty}
					<li class="property--entry">

						{* Property label *}
						{block name='frontend_detail_description_properties_label'}
							<strong class="property--label">
								{$sProperty.name}:
							</strong>
						{/block}

						{* Property content *}
						{block name='frontend_detail_description_properties_content'}
							{$sProperty.value}
						{/block}
					</li>
				{/foreach}
			</ul>
		{/if}
	{/block}
	
	{* Product description *}
	{block name='frontend_detail_description_text'}
        <div class="product--description" itemprop="description">
            {$sArticle.description_long}
        </div>
	{/block}
	
	{* Product - Further links *}
	{block name='frontend_detail_description_links'}

		{* Further links title *}
		{block name='frontend_detail_description_links_title'}
			<h2 class="content--title">
				{s name="ArticleTipMoreInformation"}{/s} "{$sArticle.articleName}"
			</h2>
		{/block}

		{* Links list *}
		{block name='frontend_detail_description_links'}
			<ul class="content--list list--unstyled">
				{block name='frontend_detail_actions_contact'}
					<li class="list--entry">
						<a href="{$sInquiry}" rel="nofollow" class="content--link link--contact" title="{s name='DetailLinkContact' namespace="frontend/detail/actions"}{/s}">
							<i class="icon--arrow-right"></i> {s name="DetailLinkContact" namespace="frontend/detail/actions"}{/s}
						</a>
					</li>
				{/block}

				{foreach $sArticle.sLinks as $information}
					{if $information.supplierSearch}

						{* Vendor landing page link *}
						{block name='frontend_detail_description_links_supplier'}
							<li class="list--entry">
								<a href="{url controller='supplier' sSupplier=$sArticle.supplierID}" target="{$information.target}" class="content--link link--supplier" title="{s name="DetailDescriptionLinkInformation"}{/s}">
                                    <i class="icon--arrow-right"></i> {s name="DetailDescriptionLinkInformation"}{/s}
								</a>
							</li>
						{/block}
					{else}

						{* Links which will be added throught the administration *}
						{block name='frontend_detail_description_links_link'}
							<li class="list--entry">
								<a href="{$information.link}" target="{if $information.target}{$information.target}{else}_blank{/if}" class="content--link link--further-links">
                                    <i class="icon--arrow-right"></i> {$information.description}
								</a>
							</li>
						{/block}
					{/if}
				{/foreach}
			</ul>
		{/block}
	{/block}

    {* Product vendor *}
    {block name='frontend_detail_description_supplier'}
		{if $sArticle.supplierDescription}

			{* Vendor title *}
			{block name='frontend_detail_description_supplier_title'}
				<h2 class="content--title">
					{s name="DetailDescriptionSupplier"}{/s} "{$sArticle.supplierName}"
				</h2>
			{/block}

			{* Vendor content *}
			{block name='frontend_detail_description_supplier_content'}
				{$sArticle.supplierDescription}
			{/block}
		{/if}
    {/block}

	{* Downloads *}
	{block name='frontend_detail_description_downloads'}
		{if $sArticle.sDownloads}

			{* Downloads title *}
			{block name='frontend_detail_description_downloads_title'}
				<h2 class="content--title">
					{s name="DetailDescriptionHeaderDownloads"}{/s}
				</h2>
			{/block}

			{block name='frontend_detail_description_downloads_content'}
				{foreach $sArticle.sDownloads as $download}
					{block name='frontend_detail_description_downloads_content_link'}
						<a href="{$download.filename}" target="_blank" class="content--link link--download" title="{s name="DetailDescriptionLinkDownload"}{/s} {$download.description}">
                            <i class="icon--arrow-right"></i> {s name="DetailDescriptionLinkDownload"}{/s} {$download.description}
						</a>
					{/block}
				{/foreach}
			{/block}
		{/if}
	{/block}
		
	{* Comment - Item open text fields attr3 *}
	{block name='frontend_detail_description_our_comment'}
		{if $sArticle.attr3}

			{* Comment title  *}
			{block name='frontend_detail_description_our_comment_title'}
				<h2 class="content--title">
					{s name='DetailDescriptionComment'}{/s} "{$sArticle.articleName}"
				</h2>
			{/block}

			{block name='frontend_detail_description_our_comment_title_content'}
				<blockquote class="content--quote">{$sArticle.attr3}</blockquote>
			{/block}
		{/if}
	{/block}
</div>
{/block}