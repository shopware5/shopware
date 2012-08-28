{if $sCharts|@count}
<h2 class="headingbox_nobg">{se name="TopsellerHeading"}{/se}</h2>
<div class="topseller">
	<ul class="accordion">
	{foreach from=$sCharts item=sArticle}
		<li {if $sArticle@first}class="active"{/if}>			
			<ul class="image">
				<li>
					<a href="{url module=frontend controller=detail sArticle=$sArticle.articleID}" title="{$sArticle.articleName}">
						{if $sArticle.image.src}
							<img src="{$sArticle.image.src.2}" alt="{$sArticle.articleName}" title="{$sArticle.articleName}" border="0"/>
						{else}
							<img src="{link file='frontend/_resources/images/no_picture.jpg'}" alt="{s name='WidgetsTopsellerNoPicture'}{/s}" />
						{/if}
					</a>
				</li>
			</ul>
			
			<div class="detail">
				<a href="{url module=frontend controller=detail sArticle=$sArticle.articleID}" title="{$sArticle.articleName}">
					{$sArticle.articleName|truncate:30:"...":true}
				</a>
				
				<span class="number">
					{$sArticle@index + 1}
				</span>
				
			</div>
		</li>
	{/foreach}
	</ul>
</div>
{/if}
