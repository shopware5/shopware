<div class="emotion--element-video-inner">
	<video{if $Data.fallback_picture} poster="{$Data.fallback_picture}"{/if}{if $Data.autobuffer} autobuffer{/if}{if $Data.autoplay} autoplay{/if}{if $Data.loop} loop{/if}{if $Data.controls} controls{/if}>
		<source src="{$Data.webm_video}" type="video/webm">
		<source src="{$Data.h264_video}" type="video/mp4">
		<source src="{$Data.ogg_video}" type="video/ogg" />
	</video>

	{if $Data.html_text}
		<div class="emotion--element-video--text">
			{$Data.html_text}
		</div>
	{/if}
</div>