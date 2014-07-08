
{if $Data && $Data.video_id}
<iframe width="100%" height="100%" src="http://www.youtube.com/embed/{$Data.video_id}{if $Data.video_hd}?hd=1&vq=hd720{/if}" frameborder="0" allowfullscreen></iframe>
{/if}
