{*
 * Copyright (c) 2012 SOFORT AG
 *
 * $Date: 2012-07-09 11:10:01 +0200 (Mon, 09 Jul 2012) $
 * @version Shopware SOFORT AG Multipay 1.1.0  $Id: skeleton.tpl 4656 2012-07-09 09:10:01Z dehn $
 * @author SOFORT AG http://www.sofort.com (integration@sofort.com)
 *
*}
{
	"init": {
		"title": "{s name="orders_by_sofort" namespace="sofort_multipay_backend"}{/s}",
		"width": 900,
		"height": 650,
		"id": "pnag",
		"minwidth": 800,
		"minheight": 650,
		"content": "",
		"loader": "action",
		"url": "{url action='index'|escape:'javascript'}",
		"help": "https://www.payment-network.com/sue_de/integration/list/88"
	}
}