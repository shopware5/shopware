<script type="text/javascript">
<!--
/**
 * ExtJS Cookie Manager
 *
 * $Date: 2012-07-20 15:27:32 +0200 (Fri, 20 Jul 2012) $
 * @version sofort 1.0  $Id: cookieManager.tpl 4867 2012-07-20 13:27:32Z dehn $
 * @author SOFORT AG http://www.sofort.com (f.dehn@sofort.com)
 * @package Shopware 4, sofort.com
 *
 */

/**
 * Data object
 */
var transactionEntity = function(transactionId, status, time) {
	var entity = {
		init : function(transactionId, status, time) {
			this.transactionId = transactionId;
			this.status = status;
			this.time = time;
			return this;
		}
	}
	return entity.init(transactionId, status, time);
};


var sofortCookieStack = function(cookieName, time) {
	var cookieStack = {
		initCookieProvider : function(time) {
			var cookieProvider = new Ext.state.CookieProvider({
				path: "/",
				expires: new Date(new Date().getTime()+(1000*time)),
				domain: window.location.host
			});
			Ext.state.Manager.setProvider(cookieProvider)
			return this;
		},
		setValue : function(transactionId, value) {
			var time = parseInt(new Date().getTime()/1000);
			var newValue = transactionId+'|'+value+'|'+time+';';
			var cookieVal = sofortCookieStack.getCookie(cookieName);
			
			if(cookieVal == undefined) {
				cookieVal = newValue;
			} else {
				cookieVal += newValue;
			}
			sofortCookieStack.setCookie(cookieName, cookieVal);
		},
		getValue : function(transactionId) {
			var cookieVal = sofortCookieStack.getCookie(cookieName);
			if(cookieVal === undefined) {
				return undefined;
			}
			
			var elements = cookieVal.split(';');
			
			// take the last upcoming element into account
			for(var i = elements.length; i >= 0; i--) {
				var element = elements[i];
				if(element == undefined) continue;
				var parts = element.split('|');
				if(parts[0] == transactionId) {
					var entity = new transactionEntity(parts[0], parts[1], parts[2]);
					return entity;
				}
			}
			
			return undefined;
		},
		getStateManager : function() {
			return this.stateManager;
		},
		getCookie : function(key) {
			return Ext.state.Manager.get(key);
		},
		setCookie : function(key, value) {
			Ext.state.Manager.set(key, value);
		},
		clearCookie : function(key) {
			Ext.state.Manager.clear(key);
		}
	};
	return cookieStack.initCookieProvider(time);
};
-->
</script>