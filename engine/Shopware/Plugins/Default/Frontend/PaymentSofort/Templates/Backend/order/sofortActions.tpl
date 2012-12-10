<script type="text/javascript">

function updateCart(transactionId, paymentStatus, store, updatedRow) 
{

	var itemCount = store.getTotalCount();
	var articles = new Array(transactionId);
	var deletedArticles = new Array();
	var dataChanged = false;

	var sum = 0;

	// take every element into account ...
	for (var i = 0; i < itemCount; i++) {
		var originalQuantity = store.getAt(i).json.quantity;	// original value taken from database
		var originalPrice = store.getAt(i).json.price;	// original value taken from database
		var item = store.getAt(i).data;	// corresponding item in grid to look at
		var quantityChanged = parseInt(item.quantity) != parseInt(originalQuantity);
		var priceChanged = parseFloat(item.price) != parseFloat(originalPrice);
		
		// ... and set every item accordingly
		var article = {
			"articleId"		  : item.articleId,
			"articleNumber"	  : item.articleordernumber,
			"articleProductType" : item.productType,
			"articleTitle"	   : item.name,
			"articlePrice"	   : item.price,
			"articleQuantity"	: item.quantity,
			"articleTax"		 : item.tax
		};
		
		// if article was NOT set to be deleted in grid, add it to the set of articles (array)
		if (item.delete === false || item.delete == '') {
			articles.push(article);
		} else {
			// if article >>was set to be deleted<<, do not add it to the set of articles (array), set data changed to true instead
			deletedArticles.push(article);
			dataChanged = true;
		}
		
		// some articles in shopware are not accompanied with some sort of an article number, so we provide those with temporary constants
		if (parseInt(item.productType) == 1 || parseInt(item.productType) == 2) {
			article.articleId = store.getAt(i).data.articleId;
		}
		
		// things in grid have changed
		if (quantityChanged || priceChanged) {
			dataChanged = true;
		}
		
		// quantity set to zero -> delete this item
		if (parseInt(item.quantity) == 0) {
			article.delete = true;
		}
		if (parseInt(item.quantity) > parseInt(originalQuantity)
			|| parseInt(item.quantity) < 0
			|| parseFloat(item.price) > originalPrice
			|| (parseFloat(item.price) <= 0 && (originalPrice > 0))) {
			Ext.MessageBox.alert('Warning', '{s name="error_9012" namespace="sofort_multipay_errors"}{/s}');
			store.rejectChanges();
			return;
		}
		sum += (article.articlePrice * article.articleQuantity);
	}
	if (sum <= 0) {
		Ext.MessageBox.alert('Warning', '{s name="error_9012" namespace="sofort_multipay_errors"}{/s}');
		return;
	}
	
	// if no data have been changed, just return
	if (!dataChanged) {
		Ext.MessageBox.alert('{s name="admin.action.warning" namespace="sofort_multipay_backend"}{/s}', '{s name="error_9018" namespace="sofort_multipay_errors"}{/s}');
		store.rejectChanges();
		return;
	}
	
	if (quantityChanged && priceChanged == true) {
		Ext.MessageBox.alert('{s name="admin.action.warning" namespace="sofort_multipay_backend"}{/s}', '{s name="admin.action.update_price_and_quantity.hint" namespace="sofort_multipay_backend"}{/s}');
		store.rejectChanges();
		return;
	}
	
	// in case no "real" products are still left in cart, show an adequate error
	if (!checkIfProductsExistInCart(articles)) {
		Ext.Msg.alert('{s name="admin.action.hint" namespace="sofort_multipay_backend"}{/s}', '{s name="admin.action.update_shipping_costs.hint" namespace="sofort_multipay_backend"}{/s}');
		store.rejectChanges();
		return;
	}
	
	// there has to be one item left within this invoice, otherwise trigger cancellation of payment
	if (articles.length == 1 || parseInt(item.quantity) == 0) {
		
		if (articles[0].productType == 1) {
			Ext.Msg.confirm('{s name="admin.action.hint" namespace="sofort_multipay_backend"}{/s}', '{s name="admin.action.cancel_invoice.question" namespace="sofort_multipay_backend"}{/s}', function (btn) {
				if (btn == 'yes') {
					return cancelInvoice(transactionId);
				} else {
					store.rejectChanges();
				}
			});
		} else {
			Ext.Msg.confirm('{s name="admin.action.cancel_invoice" namespace="sofort_multipay_backend"}{/s}', '{s name="admin.action.remove_last_article.hint" namespace="sofort_multipay_backend"}{/s}', function (btn) {
				if (btn == 'yes') {
					return cancelInvoice(transactionId);
				} else {
					store.rejectChanges();
				}
			});
		}
		
		return;
	}
	
	// if invoice has already been confirmed, a comment must be entered
	if (paymentStatus != undefined && paymentStatus != 'confirm_invoice') {
		Ext.MessageBox.show({
			title	 : '{s name="admin.action.update_confirmed_invoice" namespace="sofort_multipay_backend"}{/s}',
			msg	   : '{s name="error_9011" namespace="sofort_multipay_errors"}{/s}',
			width	 : 300,
			buttons   : Ext.MessageBox.OKCANCEL,
			multiline : true,
			fn		: performUpdate,
			params	: {
				"articles"   : articles,
				"store"	  : store,
				"updatedRow" : updatedRow
			}
		});
		return;
	} else {
		var params = {
			"params" : {
				"articles"   : articles,
				"store"	  : store,
				"updatedRow" : updatedRow
			}
		};
		performUpdate(null, 'empty', params);
	}
}


/**
 * perform the requested update with given articles depending on the status of according payment
 */
function performUpdate(btn, comment, object) {
	var articleArray = object.params.articles;
	var store = object.params.store;
	var updatedRow = object.params.updatedRow;
	// select amount and set new value
	var amounts = Ext.query('.x-grid3-col-amount');
	var amountDomElement = Ext.get(amounts[updatedRow]);
	
	// set to JSON for easier transition
	var articles = JSON.stringify(articleArray);
	
	// set amount in DOM for display
	amountDomElement.dom.innerHTML = calculateAmountOfArticles(articleArray, function(amount) {
		return amount.toFixed(2) + ' &euro;';
	});
	
	if (btn == 'cancel') {
		store.rejectChanges();
		return;
	}
	
	if (comment == '') {
		Ext.MessageBox.show({
			title : '{s name="admin.action.update_confirmed_invoice" namespace="sofort_multipay_backend"}{/s}',
			msg	 : '{s name="admin.action.update_confirmed_invoice.hint" namespace="sofort_multipay_backend"}{/s}',
			width : 300,
			buttons : Ext.MessageBox.OKCANCEL,
			multiline : true,
			fn : performUpdate,
			params : {
				"articles" : articleArray,
				"store" : store,
				"updatedRow" : updatedRow
			}
		});
		return;
	}
	
	if (typeof articleToDelete != 'undefined') {
		updateHeader = '{s name="admin.action.remove_from_invoice.question" namespace="sofort_multipay_backend"}{/s} ' + articleToDelete + '';	// todo l10n
	} else {
		updateHeader = '{s name="admin.action.update_cart.question" namespace="sofort_multipay_backend"}{/s}';
	}
	
	Ext.MessageBox.confirm('Warenkorbupdate', updateHeader, function (btn) {
		if (btn == 'yes') {
			var box = Ext.MessageBox.wait('{s name="please_wait" namespace="sofort_multipay_backend"}{/s}', 'Der Warenkorb wird bearbeitet...');
			Ext.Ajax.request({
				url	 : '{url action=editCart}', // call the controller/action SofortOrders::editCartAction($articles)
				params  : 'articles=' + articles + '&comment=' + comment,
				failure : function (response, options) {
					Ext.MessageBox.alert('Warning', 'Beim Bearbeiten des Warenkorbs trat ein Fehler auf.');
					box.hide();
				},
				success : function (response) {
					var detailStore = Ext.getCmp("detailStore").getStore();
					detailStore.reload();
					store.commitChanges();
					box.hide();
				}
			});
		} else {
			// set grid changes back
			var sum = calculateSumOfAllArticlesInStore(store.data.items, function(amount) {
				return amount.toFixed(2) + ' &euro;';
			});
			amountDomElement.dom.innerHTML = sum;
			store.rejectChanges();
		}
	});

}


function calculateAmountOfArticles(articleArray, formatCallback) {
	var sum = 0;
	for (i = 1; i < articleArray.length; i++) {
		var article = articleArray[i];
		sum += (article.articleQuantity * article.articlePrice);
	}
	
	return formatCallback(sum);
}


function calculateSumOfAllArticlesInStore(items, formatCallback) {
	var sum = 0;
	Ext.each(items, function(item, index) {
		sum += parseFloat(item.data.sum);
	});
	return formatCallback(sum);
}


/**
 * Check if any item is a product or not
 */
function checkIfProductsExistInCart(items) {
	for (i = 0; i < items.length; i++) {
		if (parseInt(items[i].articleProductType) == 0) {
			return true;
		}
	}
	return false;
}


/**
 * Requests the action confirmInvoice
 */
function confirmInvoice(transactionId, grid) {

	var detailStore = Ext.getCmp("detailStore").getStore();
	Ext.MessageBox.confirm('{s name="confirm_invoice" namespace="sofort_multipay_backend"}{/s}', '{s name="question_confirm_sr" namespace="sofort_multipay_backend"}{/s}', function (btn) {

		if (btn == 'yes') {
			var box = Ext.MessageBox.wait('{s name="please_wait" namespace="sofort_multipay_backend"}{/s}', '{s name="confirm_sr" namespace="sofort_multipay_backend"}{/s}');
			Ext.Ajax.request({
				url	 : '{url action=confirmInvoice}',
				params  : 'transactionId=' + transactionId,
				failure : function (response, options) {
					Ext.MessageBox.alert('Warning', 'An error occured');
					box.hide();
				},
				success : function (response) {
					var store = Ext.getCmp("detailStore").getStore();
					// remove confirm button for this is only to be done once
					var confirmButtons = Ext.query('#confirm_invoice');
					confirmButton = Ext.get(confirmButtons[0]);
					confirmButton.dom.innerHTML = '';

					var printButtons = Ext.query('#print_invoice button');
					var button = Ext.get(printButtons);
					button.elements[0].innerHTML = '{s name="admin.action.download_invoice" namespace="sofort_multipay_backend"}{/s}';
					//sofortCookieStack.setCookie('paymentStatus','not_credited_yet');
					sofortCookieStack.setValue(transactionId, 'not_credited_yet');
					box.hide();
				}
			});
		}

	});

}


/**
 * Requests the action cancelInvoice
 */
function cancelInvoice(transactionId, paymentStatus) {
	var cancelString = '';

	if (paymentStatus == 'confirm_invoice') {
		cancelString = '{s name="question_cancel_confirmed_sr" namespace="sofort_multipay_backend"}{/s}';
	} else if (paymentStatus == 'not_credited_yet') {
		cancelString = '{s name="question_cancel_confirmed_sr" namespace="sofort_multipay_backend"}{/s}';
	}

	Ext.MessageBox.confirm('{s name="cancel_sr_title" namespace="sofort_multipay_backend"}{/s}', cancelString, function (btn) {

		if (btn == 'yes') {
			var box = Ext.MessageBox.wait('{s name="please_wait" namespace="sofort_multipay_backend"}{/s}', '{s name="cancel_sr" namespace="sofort_multipay_backend"}{/s}');

			Ext.Ajax.request({
				url	 : '{url action=cancelInvoice}',
				params  : 'transactionId=' + transactionId,
				failure : function (response, options) {
					Ext.MessageBox.alert('Warning', '{s name="confirm_sr_error" namespace="sofort_multipay_backend"}{/s}');
					box.hide();
				},
				success : function (response) {
					// remove cancel button for this is only to be done once
					var cancelButtons = Ext.query('#cancel_invoice');
					cancelButton = Ext.get(cancelButtons[0]);
					cancelButton.dom.innerHTML = '';
					
					if(paymentStatus == 'confirm_invoice') {
						var confirmButtons = Ext.query('#confirm_invoice');
						confirmButton = Ext.get(cancelButtons[0]);
						confirmButton.dom.innerHTML = '';
						var printButtons = Ext.query('#print_invoice');
						printButton = Ext.get(cancelButtons[0]);
						printButton.dom.innerHTML = '';
					}
					
					//sofortCookieStack.setCookie('paymentStatus','refunded');
					sofortCookieStack.setValue(transactionId, 'refunded');
					box.hide();
				}
			});
		}

	});
}


/**
 * Requests the action getInvoice to download pdf
 */
function printInvoice(transactionId, grid) {
	var box = Ext.MessageBox.wait('{s name="please_wait" namespace="sofort_multipay_backend"}{/s}', '{s name="download_invoice" namespace="sofort_multipay_backend"}{/s}');

	Ext.Ajax.request({
		url	 : '{url action=getInvoice}',
		params  : 'transactionId=' + transactionId,
		failure : function (response, options) {
			Ext.MessageBox.alert('Warning', '{s name="download_invoice_error" namespace="sofort_multipay_backend"}{/s}');
			box.hide();
		},
		success : function (response) {
			var w = window.open(response.responseText);
			box.hide();
		}
	});

}


/**
 * Requests the action searchSomething for fulltext search
 */
function searchSomething(word) {
	var box = Ext.MessageBox.wait('{s name="please_wait" namespace="sofort_multipay_backend"}{/s}', '{s name="search_for" namespace="sofort_multipay_backend"}{/s}');

	Ext.Ajax.request({
		url	 : '{url action=searchSomething}',
		params  : 'searchWord=' + word,
		failure : function (response, options) {
			Ext.MessageBox.alert('Warning', '{s name="search_for_error" namespace="sofort_multipay_backend"}{/s}');
			box.hide();
		},
		success : function (response) {
			var store = Ext.getCmp("PaymentNetwork").getStore();
			store.loadData(Ext.decode(response.responseText));
			box.hide();
		}
	});

}
</script>