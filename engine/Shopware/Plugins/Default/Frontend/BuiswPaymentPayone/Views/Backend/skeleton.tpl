{
	"init": {
		"title": "{s name='WindowTitle' force}PAYONE {$actionTitle} {$text}{/s}",
		"width": 950,
		"height": 650,
		"id": "PayOne" + "{$action}",
		"minwidth": 800,
		"minheight": 650,
		"content": "",
		"loader": "action",
		"url": "{url action='index'|escape:'javascript'}" + "/{$action}{if $key}?key={$key}{/if}",
		"help": ""
	}
}