var page = require('webpage').create(),
    t, address;


address = "http://phantom.qa.shopware.in/backend";

page.open(address, function (status) {
    if (status !== 'success') {
        console.log('Fail ' + status + address);
    } else {
    	    p = false;
            page.evaluate(function(){
                (function(open){
                    XMLHttpRequest.prototype.open = function(method, url, async, username, password) {
                        console.log(url);
                        // modify the variables here as needed or use 'this` keyword to change the XHR object or add event listeners to it
                        open.call(this, method, url, async, username, password);
                     };
                })(XMLHttpRequest.prototype.open);
                (function(ext) {
                    Ext.Ajax.request({
                            url: 'http://phantom.qa.shopware.in/backend/Login/login',
                            async: false,
                            params: {
                                    username: 'demo',
                                    password: 'demo'
                            },
                            success: function() {
                                  console.log('Login successfully');
                            }
                    });
                })(Ext);

            },phantom);

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



