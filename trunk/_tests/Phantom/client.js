

var page = require('webpage').create(),
    t, controller;


address = "http://phantom.qa.shopware.in/backend/index";

controller = phantom.args[0];
action = phantom.args[1];

if (phantom.args.length === 0) {
    console.log('Usage: client.js <Controller> [<Action>]');
    phantom.exit();
} 

page.open(address, function (status) {
    if (status !== 'success') {
        console.log('Fail ' + status + address);
    } else {
            page.evaluate(function(testController,testAction){
                (function(open){
                    XMLHttpRequest.prototype.open = function(method, url, async, username, password) {
                        console.log(url);
                        // modify the variables here as needed or use 'this` keyword to change the XHR object or add event listeners to it
                        open.call(this, method, url, async, username, password);
                     };
                })(XMLHttpRequest.prototype.open);
                
                (function(ext) {
                   Ext.onReady(function() {
                   	//console.log(testController,testAction);
           			Shopware.app.Application.addSubApplication({
		                name: 'Shopware.apps.'+testController,
		                action: testAction,
		                params: {
		                    
		                }
	            	});
                   });
                })(Ext);

            },controller,action);
        console.log('Success ' + address);
    }
     phantom.exit();
   
});

page.onConsoleMessage = function(msg) {
    console.log("Console.Log: "+msg);
};

page.onError = function (msg, trace) {
    console.log("Javascript-Fail "+msg);
    trace.forEach(function(item) {
        console.log('  ', item.file, ':', item.line);
    })
};

page.onResourceReceived = function (resource) {
    var firstUrl = resource.url.substr(0,4),
            lastUrl = resource.url.substr(-3);

    if(firstUrl === 'data') {
            return;
    }
    if(lastUrl === 'png' || lastUrl === 'gif' || lastUrl === 'jpg') {
            return;
    }
    if (resource.status != 200){
        console.log("Request-Fail: "+resource.url+" "+resource.status)
    }
};


