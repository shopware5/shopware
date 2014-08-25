{foreach $sArticles as $sArticle}
    {include file="frontend/listing/box_article.tpl" sTemplate=$sTemplate lastitem=$sArticle@last firstitem=$sArticle@first}
{/foreach}