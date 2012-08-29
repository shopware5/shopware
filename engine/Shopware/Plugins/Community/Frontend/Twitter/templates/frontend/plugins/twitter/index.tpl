{block name="frontend_index_footer" append}
<div class="doublespace"></div>
<script charset="utf-8" src="http://widgets.twimg.com/j/2/widget.js"></script>
<script>
new TWTR.Widget({
  version: 2,
  type: 'profile',
  rpp: {$TwitterConfig->twitterMaxTweets},
  interval: {$TwitterConfig->twitterTweetInterval},
  width: 'auto',
  height: 300,
  theme: {
    shell: {
      background : '{$TwitterConfig->twitterBackgroundColor|escape:javascript}',
      color      : '{$TwitterConfig->twitterForegroundColor|escape:javascript}'
    },
    tweets: {
      background : '{$TwitterConfig->twitterTweetBackgroundColor|escape:javascript}',
      color      : '{$TwitterConfig->twitterTweetForegroundColor|escape:javascript}',
      links      : '{$TwitterConfig->twitterLinkColor|escape:javascript}'
    }
  },
  features: {
      scrollbar : {if $TwitterConfig->twitterShowScrollbar}true{else}false{/if},
      loop      : {if $TwitterConfig->twitterLoopResults}true{else}false{/if},
      live      : {if $TwitterConfig->twitterLive}true{else}false{/if},
      behavior  : '{$TwitterConfig->twitterBehavior|escape:javascript}'
  }
}).render().setUser('{$TwitterConfig->twitterUsername|escape:javascript}').start();

</script>
{/block}