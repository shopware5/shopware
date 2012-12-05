{block name='frontend_account_order_item_price'}
	{if $article.isBonusArticle}
		{$article.points_per_unit} {s namepspace='frontend/bonus_system' name='BonusSystemPoints'}Punkte{/s}
	{elseif $article.isBonusVoucher}
		{$article.required_points} {s namepspace='frontend/bonus_system' name='BonusSystemPoints'}Punkte{/s}
	{else}
		{$smarty.block.parent}
	{/if}
{/block}

{block name='frontend_account_order_item_amount'}
{if $article.isBonusArticle}
	{$article.required_points} {s namepspace='frontend/bonus_system' name='BonusSystemPoints'}Punkte{/s}
{else}
	{$smarty.block.parent}
{/if}
{/block}
