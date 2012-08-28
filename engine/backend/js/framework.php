<?php
define('sAuthFile', 'sSUMMARY');
define('sConfigPath',"../../../");
include("../../backend/php/check.php");

$result = new checkLogin();

$result = $result->checkUser();
if ($result!="SUCCESS"){
	
}
ob_start();
?>
<?php if (1!=1) { ?> <script> <?php } ?>
/*
SHOPWARE 3.5 - Backend-Framework -
(c)2010, shopware AG
*/

// -------------------------------------------------------------------------------------
// Ajax - suggest-search
// -------------------------------------------------------------------------------------
var ms;
var suggestTimer = 0;
var hideresults = false;



window.addEvent('domready',function(){
	
	   Ext.MessageBox.buttonText = {
	      ok     : "OK",
	      cancel : "Abbrechen",
	      yes    : "Ja",
	      no     : "Nein"
	   };

	
	if($('result')&&$('search'))
	{
		$('search').addEvent('keydown', suggestSearch.bindWithEvent($('search')));
		$('search').addEvent('submit', suggestSearch.bindWithEvent($('search')));
		$('search').addEvent('blur',function(e){
			window.clearInterval(suggestTimer);
			suggestTimer = 0;
			if(hideresults)
				hideSuggestSearch();
		});
		var defaultValue = $('search').getProperty('value');
		$('search').addEvent('focus',function(e){
			if ($('search').getProperty('value')==defaultValue) {
				$('search').setProperty('value',"");
			}
			if(!suggestTimer) {
				suggestTimer = window.setInterval("doSuggestSearch($('search').value)", 500);
			}
		});
		
		$('searchfocus').addEvent('click',function(e){
			hideSuggestSearch();
		});
		$('result').addEvent('mousemove', function(e){
			hideresults = false;
		});
		$('result').addEvent('mouseleave', function(e){
			$('search').focus();
			hideresults = true;
		});
	}
});

function suggestSearch(e){
	var keyword = $('search').value;
	if(e&&e.code&&e.code == 13)
		e.stop();
	if(keyword.length>2)
	{
		if(e&&e.code)
		{
			if((e.code>47&&e.code<58)||(e.code>64&&e.code<91))
				keyword += e.key;
			else 
			{
				switch (e.code) {
					case 192: keyword += "ö"; break;
					case 222: keyword += "ä"; break;
					case 59: keyword += "ü"; break;
					case 219: keyword += "ß"; break;
					case 32: keyword += " "; break;
					case 13: break;
					default:
						break;
				}
			}
		}
		doSuggestSearch(keyword);
	}
	else
	{
		hideSuggestSearch();
		return;
	}
};

var last;
function doSuggestSearch(keyword){
	if(keyword.length>2)
	{
		$('result').setStyle('display','block');
		$('searchfocus').removeClass('search_disabled').addClass('search_enabled');
		if(keyword!=last)
		{
			var myAjax = new Request.HTML({url: basePath+"/engine/backend/ajax/search.php", update: $('result')}).get({"search":keyword});
			last = keyword;
		}
	}
	else
	{
		hideSuggestSearch();
	}
}

function hideSuggestSearch(){
	try {
	$('result').setStyle('display','none');
	$('searchfocus').removeClass('search_enabled').addClass('search_disabled');
	} catch(e){}
}



// -------------------------------------------------------------------------------------
// Call remote script to delete shop cache
// -------------------------------------------------------------------------------------
function deleteCache(type){
		var request = new Request.JSON({
			url: basePath+'/backend/cache/'+type,
			onStart: function(el){},
			onFailure: function (el){
				window.Growl("Cache konnte nicht geleert werden");
			},
			onSuccess: function(skeleton,result){
				window.Growl("Cache wurde geleert");
			},
		}).post();
		//loadSkeleton('cache');
}
// -------------------------------------------------------------------------------------
// Call remote script to delete shop cache
// -------------------------------------------------------------------------------------

// -------------------------------------------------------------------------------------
// Logout
// -------------------------------------------------------------------------------------
function logout(){
	window.location.href = "auth.php";
}
// -------------------------------------------------------------------------------------
// Logout
// -------------------------------------------------------------------------------------

// -------------------------------------------------------------------------------------
// Sidebar related code
// -------------------------------------------------------------------------------------
function initSidebar(){
	
}



// -------------------------------------------------------------------------------------
// Setting resolution
// -------------------------------------------------------------------------------------
function setupResolution(){
	// Default-Größe Definition
	var noticeMarginRight = 5;
	var noticeMarginBottom = 5;
	var noticeHeight = 50;
	var headerHeight = $('header').getSize().y;
	/*var footerTop = $('footer').getStyle('top');
	$('im').setStyle('top',footerTop);*/
	// Fenster-Größe
	windowSize = window.getSize();
	//$('windowTracker').setStyle('top',windowSize.y-headerHeight-10+"px");
	
	

}
// -------------------------------------------------------------------------------------
// Setting resolution
// -------------------------------------------------------------------------------------

// -------------------------------------------------------------------------------------
// Code to execute after window loaded
// -------------------------------------------------------------------------------------

window.onload=function()
{
	setupResolution();
}
// -------------------------------------------------------------------------------------
// Code to execute after window loaded
// -------------------------------------------------------------------------------------

// -------------------------------------------------------------------------------------
// Window related funtions
// -------------------------------------------------------------------------------------

function newWindow(){
 	var temp = new sWindow("Fenster", 
 	{width: 800, height: 400, order: 'desc', transition:true, opacity:false, theme:'default'}
 	);
}

function notifyNotImplemented(){
	window.Growl('Noch nicht implementiert');
}

function initSkeleton(module, skeleton){
	skeleton.init.width  = skeleton.init.minwidth;
	skeleton.init.height  = skeleton.init.minheight;
	skeleton.init.module = module;
				
	var temp = new sWindow("Fenster", skeleton.init);
	var sCreatedNewWindow = true;
	
	var url = basePath+'/engine/backend/js/include.php?module='+escape(module)+'&include='+escape(skeleton.init.url);
	
	// Check how to load the wished content
	switch (skeleton.init.loader){
		case "ajax":
		/*
		Load content via ajax from an existing file 
		*/
			
			var myAjax = new Request.HTML({url: url, method: 'get', 
			onFailure: function (el){
				window.Growl("Modul konnte nicht gefunden werden");
			},
			onComplete: function (response){
				if (response){
					sWindows.focus.setContent(response);
				}
			}
			}).get();	
			break;
		case "iframe":
		/*
		Display content within an iframe
		*/
			sWindows.focus.setContent("<iframe id='contentFrame' style=\"border: 1px solid #707070;\" border=\"0\" frameborder=\"0\" class=\"contentFrame\" src=\""+url+"\"></iframe>");
			break;
		case "iframe2":
		/*
		Display content within an iframe
		*/
			sWindows.focus.setContent("<iframe scrolling=\"No\" id='contentFrame' style=\"border: 1px solid #707070;\" border=\"0\" frameborder=\"0\" class=\"contentFrame\" src=\""+url+"\"></iframe>");
			break;
		case "extern":
		case "action":
		/*
		Display content within an iframe
		*/
			sWindows.focus.setContent("<iframe id='contentFrame' style=\"border: 1px solid #707070;\" border=\"0\" frameborder=\"0\" class=\"contentFrame\" src=\""+skeleton.init.url+"\"></iframe>");
			break;
	}
	
	/*
	Anchor-Support
	*/
	
	/*
	Buttons
	*/
	if (skeleton.buttons){
		sWindows.focus.setButton(1);
		sWindows.focus.clone.getElement('.buttons').setStyle('display','block');
		skeleton.buttons.forEach(
			function (button){
				if (button.active){
					sWindows.focus.addButton(new sButton("",{id: button.id,label: button.title, remoteCall: button.remotecall, remoteAttribute: button.remoteattribute }));
				}else {
					
					sWindows.focus.addButton(new sButton("",{id: button.id, deactivate: true, label: button.title, remoteCall: null, remoteAttribute: null }));
				}
			}
		);
	}
	// Prototype - Tab - Implementierung
	// Tab-Template clonen, ins Zielfenster kopieren und sichtbar machen
	if (skeleton.tabs)
	{
	
		sWindows.focus.setTab(1);
		
		
		var targetTabTemplate = sWindows.focus.clone.getElement('.tabTemplate');
		targetTabTemplate.setStyle('display','block');
		
		var targetTabNode =  targetTabTemplate.getElement('.tabNode').getFirst();
		var i=0;
	
		 // Tab-Elemente initialisieren
		skeleton.tabs.forEach(function(tab){
			 	i++;	
			 
			 	var targetTabNodeClone = targetTabNode.clone();
			 	targetTabNodeClone.injectBefore(targetTabNode);
			 	targetTabNodeClone.setStyle('display','block');
			 	targetTabNodeClone.getFirst().set('html',tab.title);
			 	
			 	targetTabNodeClone.setProperty('lister',tab.lister ? tab.lister : "");
			 
			 	if (tab.active){
			 		if (i==1){
			 			targetTabNodeClone.setProperty('class','current');
			 			var content = tab.content;	
			 			content = content.replace(/div/,"iframe");
						content = content.replace(/\<\/div\>/,"");
						sWindows.focus.setContent(content);
						sWindows.focus.refresh();
						sWindows.focus.sActiveTab = targetTabNodeClone;
			 		}else {
			 			targetTabNodeClone.setProperty('class','disabled');
			 		}
			 		
			 		// Doing something
					targetTabNodeClone.addEvent('click',function(e){
					try {
						
						
						if(tab.hideButtons == "true")
						{
							//hide all buttons
							skeleton.buttons.forEach(
							function (button){
								$(button.id).setStyle('display', 'none');
							}
							);
						}else{
							//show all buttons
							skeleton.buttons.forEach(
							function (button){
								
								$(button.id).setStyle('display', 'block');
							}
							);
						}
					} catch(e){}
						
						sWindows.focus.sActiveTab.setProperty('class','disabled');
						// Make this tab active
						this.setProperty('class','current');
						//this.setProperty('lister',tab.lister ? tab.lister : "");
						
						sWindows.focus.sActiveTab = this;
						var content = this.getElement('.tabNodeChildContent').innerHTML;
						content = content.replace(/div/,"iframe");
						content = content.replace(/\<\/div\>/,"");
						sWindows.focus.setContent(content);
						sWindows.focus.refresh();
					});
			 		
			 	}else {
			 		targetTabNodeClone.setStyle('opacity','0.6');
			 	}
			 	
			 	targetTabNodeClone.getElement('.tabNodeChildContent').set('html',tab.content);
			 }
		 );
	}
	sWindows.focus.setTitle(skeleton.init.title);
	sWindows.focus.refresh();
}


function loadSkeleton(module,forceNewWindow, options){

		if (!options) options = {'':''};
		
		if (!module){
			window.Growl("Kein Modul angegeben");
			return false;
		}
		
		if(!options) options = {};
		options.module = module;
		
		var jSonRequest = new Request.JSON({
			url: basePath+'/engine/backend/js/include.php',
			onStart: function(el){
				
			},
			onFailure: function (el){
				window.Growl("Skeleton konnte nicht geladen werden");
			},
			onSuccess: function(skeleton,result){
				if (!skeleton || result.match('/Time\-Out/') || result.match('/FAIL/') ){
					parent.location.reload();
					return;
				}
				initSkeleton(module, skeleton);
			
			},
		}).post(options);
}


function openAction(controller, action, params)
{
		if (!controller){
			window.Growl("Kein Controller angegeben");
			return false;
		}
		if (!params){
			params = {};
		}
		if (!action){
			action = 'index';
		}
		
		params.target_action = action;
		
		var request = new Request.JSON({
			url: baseUrl+'/backend/'+controller+'/skeleton',
			onStart: function(el){},
			onFailure: function (el){
				window.Growl("Controller konnte nicht geladen werden");
			},
			onSuccess: function(skeleton,result){
				if (!skeleton || result.match('/Time\-Out/') || result.match('/FAIL/') ){
					parent.location.reload();
					return;
				}
				
				initSkeleton(controller, skeleton);
			
			},
		}).post(params);
}
// -------------------------------------------------------------------------------------
// Window related funtions
// -------------------------------------------------------------------------------------


// -------------------------------------------------------------------------------------
// Window management class
// -------------------------------------------------------------------------------------

var sWindows = {
	management: [],	// Array with all window objects
	focus: null,	// Window object with current focus
	
	/*
	Renew all window dimensions
	*/
	_renewRestrictions: function(){
		this.management.forEach(function(window){
			window.setDraggable(window.clone);
		});
	},
	/*
	Register new window to system
	*/
	_register: function(myWindow){
		this.management.push(myWindow);
	},
	
	/*
	Close all windows
	*/
	_closeAll: function (){
		
		$each(this.management,function(val,key){
		   
			val.clone.destroy();
		  	tempID = val.clone.id;
			try {
			Ext.getCmp('myTPanel').remove('tab'+tempID);		    	
		  	} catch (e) {}
		});
		this.management = [];
	},
	/*
	Minimize all windows
	*/
	_minAll: function (){
		$each(this.management,function(val,key){
		   	val.minimize(1);
		});
	},
	
	
	/*
	Blur all Windows
	*/
	_blurAll: function(){
		this.management.forEach(function(window){
			window.blur();
		});
	},
	_groupHorizontal: function(){
		// How much windows we have?
		var numberWindows = this.management.length;
		// 
		if (numberWindows < 1){
			window.Growl("Keine Fenster geöffnet");
		}else {
			BrowserHeight = Window.getHeight();
			BrowserWidth = Window.getWidth();
			
			// Höhe der Top-Bar
			TopBarHeight = $('header').getSize().y;
			
			// Höhe des Footers
			FooterHeight = $('footer').getSize().y;
			
			// Breite der Sidebar
			var SideBarOneWidth = 0;
			// Start-Position-Y
			WindowWidth = BrowserWidth - SideBarOneWidth - (numberWindows*5);
			WindowWidthEach = Math.round(WindowWidth / numberWindows) - 15;
			
			WindowHeight = BrowserHeight - TopBarHeight - FooterHeight;
			
			WindowStartY = TopBarHeight;
			WindowStartX = SideBarOneWidth + 20;
			
			// Jetzt die einzelnen Fenster neu positionieren
				this.management.forEach(function(window){
					window.arrange(WindowStartX,WindowStartY,WindowWidthEach,WindowHeight);
					WindowStartX += WindowWidthEach + 15;
				});
			// 
			
		}
	},
	_groupVertical: function(){
		// How much windows we have?
		var numberWindows = this.management.length;
		// 
		if (numberWindows < 1){
			window.Growl("Keine Fenster geöffnet");
		}else {
			BrowserHeight = Window.getHeight();
			BrowserWidth = Window.getWidth();
			
			// Höhe der Top-Bar
			TopBarHeight = $('header').getSize().y;
			
			// Höhe des Footers
			FooterHeight = $('footer').getSize().y;
			
			var SideBarOneWidth = 0;
			// Start-Position-Y
			WindowWidth = BrowserWidth - SideBarOneWidth - 30;
			
			//Math.round(WindowWidth / numberWindows) - 15;
			//WindowHeight = Math.round((BrowserHeight - TopBarHeight - FooterHeight)/numberWindows)-25;
			WindowHeight = ((BrowserHeight - TopBarHeight - FooterHeight) / numberWindows) - 25;
			WindowHeightEach = WindowHeight - 35;
			WindowStartY = TopBarHeight;
			WindowStartX = SideBarOneWidth + 20;
			
			// Jetzt die einzelnen Fenster neu positionieren
				this.management.forEach(function(window){
					window.arrange(WindowStartX,WindowStartY,WindowWidth,WindowHeight);
					WindowStartY += (WindowHeightEach + 25);
					window.focus();
				});
		}
	},
	/*
	Remove specific window-object from window-manager
	*/
	_unregister: function(myWindow){
		this.management.remove(myWindow);
	}
};



var counter = 0;
sWindow = new Class({
		initialize: function(el, options) {
			this.options = $extend({
			  active:		2,
			  width:		800,
			  sHaveTabs:	0,
			  sHaveButtons:	0,
			  height:		500,
			  help:			0,
			  label:		'Stammdaten', // default label if panel heading is not set
			  effects:   	true,	// true = use animation effects
			  theme:		'simple',
			  opacity:		false,
			  minwidth:		250,
			  minheight:	100,
			  maxWindowMargin: 15,
			  id:			0,
			  sActiveTab:	0,
			  title: '',
			  module: ''
			}, options || {});
			
				<?php
				if(!empty($_SESSION["sWindow_Width"])) 
					$width = $_SESSION["sWindow_Width"]; 
				else 
					$width = 0;
				if(!empty($_SESSION["sWindow_Height"])) 
					$height = $_SESSION["sWindow_Height"]; 
				else 
					$height = 0;
				?>
				if (this.options.width<<?php echo $width?>)
					this.options.width = <?php echo $width?>;
				if (this.options.height<<?php echo $height?>)
					this.options.height = <?php echo $height?>;
				// Create window physical
				
				this.clone = $('myWindow').clone().injectBefore('myWindow');
				
				this.clone.setStyle('display','block');
				
				
				// Link to uniquie id
				temporaryWindowId = Math.floor(Math.random() * (10000 + 1));
				this.clone.setProperty('id', temporaryWindowId);	
				this.id = temporaryWindowId;
				this.setDraggable(this.clone);
				this.setResizable(this.clone);
				// Set window label
				this.setTitle(el);
				
				if (this.options.id=="activate"){
					this.clone.getElement('.refreshme').setStyle('display','none');
					this.clone.getElement('.maxme').setStyle('display','none');
					this.clone.getElement('.minime').setStyle('display','none');
					this.clone.getElement('.help').setStyle('display','none');
					this.clone.getElement('.closeme').setStyle('display','none');
				 	//var myWindowCloser = this.close.bind(this);
					//this.clone.getElement('.closeme').addEvent('click',myWindowCloser);
					
					BrowserHeight = Window.getHeight();
					BrowserWidth = Window.getWidth();
					// Height of Header
					TopBarHeight = $('header').getSize().y;
					// Height of Footer
					FooterHeight = $('footer').getSize().y;
		
					WindowStartY = (BrowserHeight - TopBarHeight - FooterHeight)/2-(this.options.height/2);
					WindowStartX = (BrowserWidth)/2-(this.options.width/2);
					if (WindowStartY < 0) WindowStartY = 100;
					this.focus();
					this.arrange(WindowStartX,50,this.options.width,this.options.height);
					sWindows._register(this);
					return;			
				}
		
				// Event for close window
				// Event for close window
				var myWindowCloser = this.close.bind(this);
				
				
				
				this.clone.getElement('.closeme').addEvent('click',myWindowCloser);
				// Open Help
				var myWindowHelp = this.help.bind(this);
				
				//this.clone.getElement('.help').addEvent('click',myWindowHelp);
				if (this.options.help){
					this.options.help = "http://www.shopware.de/wiki/shopware.php?sViewport=searchFuzzy&sSearch="+this.options.title;
				}
				
				
				if (this.options.help){
				this.clone.getElement('.bt_opt_info').setProperty('href',this.options.help);
				this.clone.getElement('.bt_opt_info').setProperty('target','_blank');
				}else {
					this.clone.getElement('.help').setStyle('display','none');
				}
				// Event for refresh window
				var myWindowUpdater = this.reload.bind(this);
				this.clone.getElement('.refreshme').addEvent('click',myWindowUpdater);
				// Event for increase the window
				var myWindowMax = this.maximize.bind(this);
				this.clone.getElement('.maxme').addEvent('click',myWindowMax);
				// Event for decrease the window
				var myWindowMin = this.minimize.bind(this);
				this.clone.getElement('.minime').addEvent('click',myWindowMin);
				// Focus on Iron-Mask
				var myWindowFocus = this.focus.bind(this);
			    this.clone.getElement('.ironmask').addEvent('click',myWindowFocus);
			    this.clone.getElement('.description').addEvent('click',myWindowFocus);
			    // Doubleclick on window-title force maximation
			    var myWindowTitle = this.maximize.bind(this);
			    this.clone.getElement('.description').addEvent('dblclick',myWindowTitle);
			    
			    var x;
				var y;
				
				if ($('sidebarbutton') && 1 != 1)
				{
					y= $('header').getSize().y-15;
				
					if ($('sidebarbutton').getProperty('src')=="backend/img/default/sidebar/bt_close.gif"){
						x= $('sidebarOne').getSize().x+15;
					}
					else
					{
						x= 15;
					}
					
					if(counter>=3)
					{
						counter = 0;
					}
					
					if(counter>=1)
					{
						x += counter*15;
						y += counter*15; 
					}
					
					counter++;
				}
				
				if ($('sidebarbutton'))
				{
					var SideBarOneWidth = 0;
				}
				
				WindowHeight = Window.getHeight() - $('header').getSize().y - $('footer').getSize().y + 20;
				WindowWidth = Window.getWidth() - SideBarOneWidth  - this.options.maxWindowMargin - 18;

				var left = Math.ceil(Math.random() * 10);
				var top = 50 + Math.ceil(Math.random() * 10);
				
				var el = this;
				
				if(this.options.title&&this.options.title!='')
				{
					var request = new Request.JSON({url: basePath+"/engine/backend/ajax/AdminSettings.php", method: 'post', async: true, onComplete: function(ret){
						if(ret&&ret!=''&&ret!='FAIL')
						{
						
							el.options.width = ret.width;
							el.options.height = ret.height;
							
						}
						el.arrange(left,top,el.options.width,el.options.height);
					}}).get({'window':this.options.module,'screenwidth':screen.width});
				}
				else
				{
					if (WindowHeight<this.options.height) this.options.height = WindowHeight;
					this.arrange(left,top,this.options.width,this.options.height);
				}
				
				
				// Register window to management
				sWindows._register(this);
				
				try {
				Ext.getCmp('myTPanel').add({
					title    : this.options.label != "Fenster" ? this.options.label : this.options.title,
					tabTip   : this.options.label != "Fenster" ? this.options.label : this.options.title,
					closable : true,
					id	     : 'tab'+temporaryWindowId,
					window	: this/*,
	        		listeners: {
	        			'activate' : {fn: function(tab,tabpanel){
	        				this.focus(this);
	        			}}
	        		}*/
				});
				Ext.getCmp('myTPanel').activate('tab'+temporaryWindowId);
				
				} catch(e){}
				// Add window to bottom-bar
				
				
				// Window will become focus
				this.focus();
				
				
				
				
		},
		truncate: function (text, length, ellipsis) {      
	 
		     // Set length and ellipsis to defaults if not defined  
		     if (typeof length == 'undefined') var length = 100;  
		     if (typeof ellipsis == 'undefined') var ellipsis = '...';  
		   
		     // Return if the text is already lower than the cutoff  
		     if (text.length < length) return text;  
		   
		     // Otherwise, check if the last character is a space.  
		     // If not, keep counting down from the last character  
		     // until we find a character that is a space  
		     for (var i = length-1; text.charAt(i) != ' '; i--) {  
		         length--;  
		     }  
		   
		     // The for() loop ends when it finds a space, and the length var  
		     // has been updated so it doesn't cut in the middle of a word.  
		     return text.substr(0, length) + ellipsis;  
        },
	   // Set Window Content
	   setContent: function(content){
	   	this.clone.getElement('.content').set('html',content);
	   },
	   // Remove window and prepare class for get collected by garbage-collector
	   setTab: function(value){
	   		this.sHaveTabs = 1;
	   },
	   setButton: function(value){
	   		this.sHaveButtons = 1;
	   },
	   close: function(el){
	   	
	   		
	   		this.focus();
			var contentFrame = this.clone.getElement('.content');
			contentFrame = $(contentFrame).getElement('.contentFrame');
			var f = false;
			try {
				if (contentFrame && contentFrame.contentDocument && contentFrame.contentDocument.defaultView)
					f = contentFrame.contentDocument.defaultView;
			}
			catch (e){
			}
   			try {
   				
   				var tempID = sWindows.focus.id;
   				sWindows.focus.clone.destroy();   				
   				sWindows._unregister(sWindows.focus);
   				
   				delete sWindows.focus;
   				if (sWindows.management.length>=1){
   					//sWindows.management[0].focus();
   					/*sWindows.management.forEach(function(window){
						window.focus();
					});*/
   				}
   				
   				Ext.getCmp('myTPanel').remove('tab'+tempID);
   				
   				sWindows.management.reverse.forEach(function(window){
						window.focus();
						return;
				});
   			} catch (e){
   				//console.log(e);
   			}
	   },
	   help: function(el){
	   	if (this.options.help){
	   	
	 
	   		window.loadSkeleton('help_admin',false, {'url':this.options.help});
	   	}else {
	   		window.Growl('Für dieses Modul ist noch keine Onlinehilfe verfügbar');
	   	}
	   },
	   // Makes deactivated tabs accessible, necessary for new-articles for example
	   unlockTabs: function(replaceCondition,replaceRule){
	   	
	   		
		   //	this.clone.getElement('.current');
	   		var allDisabledTabs = this.clone.getElements('.current');
	   		allDisabledTabs.forEach(function(el){
	   			
	   		if (el.getStyle('opacity')==1){
	   			
	   		}else {
		   		el.setStyle('opacity',1);
		   		el.removeClass('current');
		   		el.setProperty('class','disabled');
		   		el.addEvent('click',function(e){
					sWindows.focus.sActiveTab.setProperty('class','disabled');
					// Make this tab active
					this.setProperty('class','current');
					sWindows.focus.sActiveTab = this;
					var content = this.getElement('.tabNodeChildContent').innerHTML;
					content = content.replace(/div/,"iframe");
					content = content.replace(/\<\/div\>/,"");
					sWindows.focus.setContent(content);
					sWindows.focus.refresh();
				});
	   		}
		   	});
		   	
		   	var allEmbeddedIframes = this.clone.getElements('.contentFrame');
		
		 	
		   	allEmbeddedIframes.forEach(function(el){
		   		
		   		var src = el.getProperty('src');
	
		   		var UpdatedSrc = src.replace(new RegExp(replaceCondition,""),replaceRule);
				//console.log(UpdatedSrc);
		   		el.setProperty('src',UpdatedSrc);
		   	});

	   },
	   setTitle: function(title){
	   		this.options.label = title;
	   		this.clone.getElement('.description').set('html',title);
	   },
	   /*
	   hide window
	   */
	   hide: function(){
	   		this.clone.setStyle('display','none');
	   },
	   /*
	   show window
	   */
	   show: function(){
	   		this.clone.setStyle('display','block');
	   },
	   /*
	   Add button to window mask
	   */
	   addButton: function(btObj){
		    buttonTpl = this.clone.getElement('.buttonTemplate');
		   	var button = buttonTpl.clone().injectAfter(buttonTpl);
		   	
		   	button.setStyle('display','block');
	   		button.setProperty('id',btObj.options.id);
		   	button.getElement('.buttonLabel').set('html',btObj.options.label);
		   	
		   	if (btObj.options.deactivate){
		   		button.setStyle('opacity','0.5');
		   	} else {
		   		 btObj.parentObject = this;
			   	btObj.remoteCall = btObj.options.remotecall;
			   	var buttonHandler = btObj.action.bind(btObj);
			   	button.addEvent('click',buttonHandler);
		   	}
	   	
	   },
	   reload: function(){
		   var iFrame = this.clone.getElement('.contentFrame');
		   iFrame.setAttribute('src',iFrame.getAttribute('src'));
	   },
	   /*
	   Reset the whole window (necessary for Loading skeletons into existing  windows)
	   */
	   clean: function(){
	   	 // Delete previous content
	   	 this.setContent("");
	   	 // Delete Tabs 
	   	 this.clone.getElement('.tabTemplate').getChildren().remove();
	   	 // Delete Buttons
	   	 while (button = this.clone.getElement('.buttonTemplate').getNext()){
	   		button.remove();
	   	 }
	   },
	   /*
	   Arrange the window to X,Y Positions and resize it to width, height
	   */
	   arrange: function (x,y,width,height){
	   		WindowHeight = Window.getHeight() - $('header').getSize().y - $('footer').getSize().y + 20;
	   		if (height >= WindowHeight) height-=50;
	   		if (x) this.clone.setStyle('left', x + 'px');
			if (y) this.clone.setStyle('top', y + 'px');
			
			this.clone.setStyle('width', width + 'px');
			
			this.clone.setStyle('height', height + 'px');
			this.refresh();
			
	   },
	   /*
	   Center this window
	   */
	   center: function(){
	   		BrowserHeight = Window.getHeight();
			BrowserWidth = Window.getWidth();
			
			// Height of Header
			TopBarHeight = $('header').getSize().y;
			
			// Height of Footer
			FooterHeight = $('footer').getSize().y;
			
			var SideBarOneWidth = 0;
			// Start-Position-Y
			WindowWidth = this.clone.getSize().x;
			WindowHeight = this.clone.getSize().y;
			
			WindowStartY = (BrowserHeight - TopBarHeight - FooterHeight)/2-(WindowHeight/2);
			WindowStartX = (BrowserWidth - SideBarOneWidth)/2-(WindowWidth/2);
			
			
			// Now center the window
			this.clone.setStyle('left', WindowStartX + 'px');
			this.clone.setStyle('top', WindowStartY + 'px');	
	   },
	   /*
	   minimize window
	   */
	   minimize: function(hideWindows){
			this.hide();	
			this.state = 1; // Minimiert
			var tempID = this.id;
			
			if (hideWindows) return;
			sWindows.management.forEach(function(window){
				if (window.id != tempID){
					Ext.getCmp('myTPanel').activate('tab'+window.id);
					return;
				}
			});
			
			
			
	   },
	   /*
	   awake window from minmize state
	   */
	   restore: function(){
	   		this.state = 0; // Normal
	   		this.show();
	   },
	   /*
	   Set focus on this window
	   */
	   focus: function(el){
	   	
	   		this.show();
	   		hideSuggestSearch();
	   		sWindows._blurAll();
	   		this.refresh();
	 		// Focus auf aktuelles Fenster
	 		//console.log($('windowTracker').getElement('.windowTrackerNode'));
	 		try {
	 		Ext.getCmp('myTPanel').activate('tab'+this.id);
	 		}catch (e){
	 			
	 		}
	 		
	 		this.clone.getElement('.ironmask').setStyle('width',1+"px");
	   		this.clone.getElement('.ironmask').setStyle('height',1+"px");
			this.clone.getElement('.ironmask').setStyle('display',"none");
	   		this.clone.setStyle('z-index',5000);
			this.clone.getElement('.windowTitle').setProperty('class',"windowTitle active");
			
			
			sWindows.focus = this;
			
	   },
	   /*
	   Deactivate this window
	   */
	   blur: function(el){
	   		this.clone.getElement('.ironmask').setStyle('width',this.clone.getCoordinates().width-20+"px");
	   		this.clone.getElement('.ironmask').setStyle('height',this.clone.getCoordinates().height-40+"px");
	   		this.clone.getElement('.ironmask').setStyle('display',"block");
	   		this.clone.getElement('.ironmask').setStyle('z-index',10000);
	   		
			this.clone.setStyle('z-index',100);
	   		this.clone.getElement('.windowTitle').setProperty('class','windowTitle');
	   },
	   /*
	   Adjust size of window elements
	   */
	   refresh: function (headerHeight){
	   	
	   	
		   	// Hier passen wir dynamisch die Höhe des Content-DIVs an
			var marginButtom = 0;	// Abstand zum Fenster-Bottom
			var shadowMargin = 30;
	
			// Höhe - Content-Bereich
			content = this.clone.getElement('.content');
			
			if (!headerHeight){
				headerHeight = content.getCoordinates().top - this.clone.getCoordinates().top;
			}else {
				headerHeight -=  this.clone.getCoordinates().top;
			}
			//alert(headerHeight);
			// Set Window-Proportions
			
			//var marginTabs = 0;
			if (this.sHaveTabs){
				content.setStyle('margin-top',0);  //old margin-top 30
				content.setStyle('padding-top',37); //for Tabs
			}
			
			content.setStyle('height',(this.clone.getCoordinates().height-80)+"px");
			this.clone.getElement('.description').setStyle('width',this.clone.getCoordinates().width-15-130+"px");
			this.clone.getElement('.windowContent').setStyle('width',this.clone.getCoordinates().width+"px");
			// Höhe, Breite eventueller iFrames
			var iFrame = $(content).getElement('.contentFrame');
			
			if (iFrame){
			
				if (this.sHaveButtons){
				
					//this.clone.getElement('.windowContent').setStyle('height',(this.clone.getCoordinates().height+38)+"px"); //TABS
					iFrame.setStyle('height',(content.getCoordinates().height-40)+"px");
				
					iFrame.setStyle('border','1px solid #a8a8a8');
				}else {
					if ($(this.clone).getElement('.resize').getStyle('bottom')=="0px"){
						iFrame.setStyle('height',(content.getCoordinates().height-10)+"px");
						
						iFrame.setStyle('border',0);
					}else {
						iFrame.setStyle('height',(content.getCoordinates().height-10)+"px");
						
						iFrame.setStyle('border',0);
					}
				}
				
				//iFrame.setStyle('border',0);
				iFrame.setStyle('frameborder',0);
				iFrame.setProperty('frameborder',0);
				iFrame.setStyle('height','100%');
				
				iFrame.setStyle('width',(content.getCoordinates().width-20)+"px");
			}
	   },
	   /*
	   Maximize this window
	   */
	   maximize: function (el){
	   	
	   	if (this.state!=2){
	   		// Fenster maximieren
	   		BrowserHeight = Window.getHeight();
			BrowserWidth = Window.getWidth();
			// Höhe der Top-Bar
			TopBarHeight = $('header').getSize().y;
			// Höhe des Footers
			FooterHeight = $('footer').getSize().y;
			// Breite der Sidebar
			// Start-Position-Y
			WindowStartY = TopBarHeight;
			var tab = 0;
					
			if($('sidebarbutton'))
			{
				var SideBarOneWidth = 0
	   		}
	   		else
	   		{
	   			var SideBarOneWidth = 0
	   		}
	   			
			WindowStartX = 0;
			// Höhe des Fensters
			WindowHeight = Window.getHeight() - $('header').getSize().y - $('footer').getSize().y + tab; //+ 20;
			
			WindowWidth = Window.getWidth() - SideBarOneWidth  - this.options.maxWindowMargin + -3;//- 18
			// Cache previous position in the window-object
			this.previousX = this.clone.getPosition().x;
			this.previousY = this.clone.getPosition().y;
			this.previousHeight = this.clone.getSize().y;
			this.previousWidth = this.clone.getSize().x;
			this.state = 2; // 0=normal, 1=minimized, 2=maximizied
			this.clone.setStyle('width', (WindowWidth-5) + 'px');
			this.clone.setStyle('height', (WindowHeight ) + 'px');//- this.options.maxWindowMargin
			//console.log(TopBarHeight+' '+WindowStartY);
			this.clone.setStyle('left', (WindowStartX) + 'px');
			this.clone.setStyle('top', (WindowStartY - this.options.maxWindowMargin+5) + 'px');
			this.refresh();
			this.clone.getElement('.ironmask').setStyle('width',1+"px");
	   		this.clone.getElement('.ironmask').setStyle('height',1+"px");
			this.clone.getElement('.ironmask').setStyle('display',"none");
	   		this.clone.setStyle('z-index',5000);
			
	   	}else {
	   		// Fenster wiederherstellen
	   		this.normalize();
	   	}
	   },
	   /*
	   Restore window to its previous position/size
	   */
	   normalize: function (el){
	   		this.state = 0;
	   		this.clone.setStyle('width', this.previousWidth + this.options.maxWindowMargin + 5 + 'px');
			this.clone.setStyle('height', this.previousHeight + this.options.maxWindowMargin  + 'px');
			this.clone.setStyle('left', this.previousX  + 'px');
			this.clone.setStyle('top', this.previousY + 'px');
			
			this.refresh(this.previousY);
			
	   },
	   /*
	   setDraggable
	   Make the window draggable
	   */
	   setResizable: function(el)
	   {
	   		obj2 = this;
	   		
	   		var ResizeComplete = function ()
	   		{
				enableWidgetPanel();
				sWindows.focus.clone.getElement('.ironmask').setStyle('display','none');
				obj2.options.width = el.getStyle('width').toInt();					
				obj2.options.height = el.getStyle('height').toInt();
				
				new Request( {url: basePath+'/engine/backend/ajax/AdminSettings.php',method: 'post'}).get({'window':obj.options.module,'height':obj.options.height,'width':obj.options.width,'screenwidth':screen.width});
	   		}
	   		var ResizeDrag = function ()
	   		{
	   			sWindows.focus.refresh();	
				sWindows.focus.clone.getElement('.ironmask').setStyle('display','block');
				sWindows.focus.clone.getElement('.ironmask').setStyle('width',sWindows.focus.clone.getCoordinates().width-5+"px");
	   			sWindows.focus.clone.getElement('.ironmask').setStyle('height',sWindows.focus.clone.getCoordinates().height-75+"px");
	   		}
	   		var ResizeStart = function ()
	   		{
			    disableWidgetPanel();
	   			sWindows.focus.clone.getElement('.ironmask').setStyle('display','block');
	   		}
	   		var limit = { x: [250, 2500], y: [0, 2500] };
	   		
			$(el).makeResizable({ 
				limit : limit, 
				//direction: "no ne sw se",
				ghost: true,
				grid: 5,
				handle: el.getElement('.resize-se'),
				preventDefault: true,
				onStart: ResizeStart,
				onComplete: ResizeComplete,
				onDrag: ResizeDrag
			});
			$(el).makeResizable({ 
				limit : limit, 
				modifiers: {'x': false, 'y': 'height'},
				ghost: true,
				grid: 5,
				handle: el.getElement('.resize-s'),
				preventDefault: true,
				onStart: ResizeStart,
				onComplete: ResizeComplete,
				onDrag: ResizeDrag
			});
			$(el).makeResizable({ 
				limit : limit, 
				modifiers: {'x': 'width', 'y': false},
				ghost: true,
				grid: 5,
				handle: el.getElement('.resize-e'),
				preventDefault: true,
				onStart: ResizeStart,
				onComplete: ResizeComplete,
				onDrag: ResizeDrag
			});
			
			
	   },
	   shake: function (amt){
	   		
	   },
	   setDraggable: function(el){
	   		obj = this;
			var y = el.makeDraggable
			({
				opacity: this.options.opacity,
				move: true,
				handle: el.getElement('.description'),
				parentObject: this,
				limit: {
					y:[$('header').getSize().y-15,2500],
					x:[-100,2500]
				},
				onComplete: function(){
					enableWidgetPanel();
					sWindows.focus.clone.getElement('.ironmask').setStyle('display','none');
				},
				onStart: function()
				{
					disableWidgetPanel();
					this.options.parentObject.focus();
					sWindows.focus.clone.getElement('.ironmask').setStyle('display','block');
				},
				onDrag: function(e){
					sWindows.focus.refresh();	
					sWindows.focus.clone.getElement('.ironmask').setStyle('display',"block");
					sWindows.focus.clone.getElement('.ironmask').setStyle('width',sWindows.focus.clone.getCoordinates().width-5+"px");
	   				sWindows.focus.clone.getElement('.ironmask').setStyle('height',sWindows.focus.clone.getCoordinates().height-65+"px");
				}
				
			});
	   }
	 // setDraggable
// End of our window-class
});


/*
Button-Handler
*/
sButton = new Class({
	label: null,
	parentObject: null,
	deactivate: null,
	initialize: function(el, options) {
		this.options = $extend({
			  active:		2,
			  width:		300,
			  height:		300,
			  id:			null,
			  label:		'Label' // default label if panel heading is not set
			}, options || {});
			
			
			
	},
	action: function(){
		
		var contentFrame = this.parentObject.clone.getElement('.content');
		//console.log(contentFrame);
		var contentFrame = $(contentFrame).getElement('.contentFrame');
		
		if (!contentFrame){
			window.Growl('Contentframe konnte nicht gefunden werden');
		}else {
			// contentDocument.defaultView is addicted to mozilla based browsers only
			//console.log(this);
			contentFrame.contentDocument.defaultView.sWrapper(this.options.remoteCall,this.options.remoteAttribute);
			
		}
		
		/*button.getElementsBySelector('.buttonLabel')[0].setHTML(btObj.options.label);
	   	btObj.parentObject = this;
	   	var buttonHandler = btObj.action.bind(btObj);*/
	}
});
// Pass object
sConfirmation = new Class ({
	initialize: function(el, options) {
		this.options = $extend({
			  child:	   null,
			  pipe:	 	 null,
			  parameter: null
			}, options || {});
		this.box = el;
	},
	test: function (){
		return;
	},
	ToggleOverlay: function (){
		if ($('mb_overlay')){
			 new Fx.Style($('mb_overlay'), 'opacity', {duration:750, }).start(0.5,0).chain(function(){$('mb_overlay').remove();});
		}else {
			 test = new Element('div').setProperty('id', 'mb_overlay').injectInside(document.body);
			 test.setStyles({top: window.getScrollTop()+'px', height: window.getHeight()+'px'});
			 new Fx.Style(test, 'opacity', {duration:750, }).start(0,0.5);
		}
	},
	ok: function(){
		this.pipeThrough.sWrapper(this.pipeFunction,this.pipeId);
		
	},
	abort: function(){
		
	},
	center: function(){
			
	},
	show: function(notice, pipeObject, pipeFunction, pipeId){
		this.pipeThrough = pipeObject;
		this.pipeFunction = pipeFunction;
		this.pipeId = pipeId;

		
		Ext.MessageBox.confirm('Bestätigung',notice, function msgConfirmation(btn){
		 	switch (btn){
		 		case "yes":
			 		pipeObject.sWrapper(pipeFunction,pipeId);
		 			break;
		 		case "no":
		 			Ext.example.msg('Info', 'Vorgang abgebrochen', btn);
		 			break;
		 	}
		    
		  });
	}
});
Ext.example = function(){
    var msgCt;

    function createBox(t, s){
        return ['<div class="msg">',
                '<div class="x-box-tl"><div class="x-box-tr"><div class="x-box-tc"></div></div></div>',
                '<div class="x-box-ml"><div class="x-box-mr"><div class="x-box-mc"><h3>', t, '</h3>', s, '</div></div></div>',
                '<div class="x-box-bl"><div class="x-box-br"><div class="x-box-bc"></div></div></div>',
                '</div>'].join('');
    }
    return {
        msg : function(title, format){
            if(!msgCt){
                msgCt = Ext.DomHelper.insertFirst(document.body, {id:'msg-div'}, true);
            }
            msgCt.alignTo(document, 't-t');
            var s = String.format.apply(String, Array.prototype.slice.call(arguments, 1));
            var m = Ext.DomHelper.append(msgCt, {html:createBox(title, s)}, true);
            m.slideIn('t').pause(2).ghost("t", {remove:true});
        },

        init : function(){
            var t = Ext.get('exttheme');
            if(!t){ // run locally?
                return;
            }
            var theme = Cookies.get('exttheme') || 'aero';
            if(theme){
                t.dom.value = theme;
                Ext.getBody().addClass('x-'+theme);
            }
            t.on('change', function(){
                Cookies.set('exttheme', t.getValue());
                setTimeout(function(){
                    window.location.reload();
                }, 250);
            });

            var lb = Ext.get('lib-bar');
            if(lb){
                lb.show();
            }
        }
    };
}();

Ext.onReady(Ext.example.init, Ext.example);
window.Growl = function (e) { 
	try {
		var module = sWindows.focus.options.label;
	} catch (e) {
		var module = "";
	}
	// Save notice to log-file
	var myAjax = new Request( {url: basePath+'/engine/backend/ajax/saveLog.php',method: 'post'}).get({'msg':e,'mod':module});
	// Display notice
	Ext.example.msg('Hinweis', e); 
}

<?php if (1!=1) { ?> </script> <?php } 

$js = ob_get_contents();
ob_clean();
echo $js;
?>