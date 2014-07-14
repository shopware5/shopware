{extends file="backend/index/parent.tpl"}


{block name="backend_index_javascript" prepend}
	<script language="javascript" type="text/javascript" src="{link file='engine/backend/js/mootools.js'}"></script>
	<link href="{link file='engine/backend/css/modules.css'}" rel="stylesheet" type="text/css" />
	{if $selectedElement}
	<script language="javascript" type="text/javascript" src="{link file='engine/vendor/tinymce/tiny_mce.js'}"></script>
	<script language="javascript" type="text/javascript">
	  	tinyMCE.init({
			// General options
			mode: "exact",
			elements : "ElementValue",
			theme : "advanced",
			{$Config.sTINYMCEOPTIONS}, 
			extended_valid_elements : "font[size],script[src|type],object[width|height|classid|codebase|ID],param[name|value],embed[name|src|type|wmode|width|height|style|allowScriptAccess|menu|quality|pluginspage|tinybrowser]",
			plugins : "safari,pagebreak,style,layer,table,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,visualchars,nonbreaking,xhtmlxtras,template",
			theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,formatselect,fontselect,fontsizeselect",
			theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code",
			theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,ltr,rtl,|,fullscreen",
			theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak,|,insertdate,inserttime,preview,|,forecolor,backcolor",
			height:"350px"
		});
		function tinyBrowser (field_name, url, type, win) {
		   type = "image";
	       var cmsURL = "{link file='engine/vendor/tinymce/backend/plugins/tinybrowser/tinybrowser.php'}";    // script URL - use an absolute path!
	       if (cmsURL.indexOf("?") < 0) {
	           //add the type as the only query parameter
	           cmsURL = cmsURL + "?type=" + type;
	       }
	       else {
	           cmsURL = cmsURL + "&type=" + type;
	       }
	       tinyMCE.activeEditor.windowManager.open({
	           file : cmsURL,
	           title : 'Tiny Browser',
	           width : 650, 
	           height : 440,
	           resizable : "yes",
	           scrollbars : "yes",
	           inline : "yes",  // This parameter only has an effect if you use the inlinepopups plugin!
	           close_previous : "no"
	       		}, {
	           window : win,
	           input : field_name
	       });
	       return false;
	     }
	</script>
	{/if}
	
	<script>
	function askDuplicate(ev){ 
		ev = 1;
		parent.parent.parent.sConfirmationObj.show('Sollen die Eigenschaften dieses Beleges für alle Belege übernommen werden?',window,'askDuplicateConfirmation',ev);
	}
	function sWrapper(sFunction, sId){ 
		switch (sFunction){
			case "askDuplicateConfirmation":
				window.location.href='{url action="duplicateProperties" id={$id}}';
			break;
		}
	}
	</script>
	
{/block}
{block name="backend_index_css" append}
<link rel="stylesheet" type="text/css" href="{link file='engine/backend/css/icons4.css'}" />
{/block}

{block name="backend_index_body_inline"}
<style>
body {
	font-size:11px;
	font-color: #000;
	overflow:auto;
}
label {
	text-align:left;
	width: 100px;
	font-size: 11px;
	color:#000;
}

fieldset {
	padding:5 5 5 5;
}

div.container {
	margin-top: -18px;
	width: 1000px;
	height:800px
}
div.containerLeft {
	width:600px;height:500px;border:1px;float:left;margin-right:10px;
}
div.containerRight {
	width:350px;border:1px;height:500px;float:left;	
}
div.containerDocument {
	background-color:#FFF;width:315px;height:435px;border:1px solid #000;margin: 0 auto;
}
div.containerRight a { margin:0 auto; }
</style>
<div class="container" style="margin-top:2px">
	<div class="containerLeft">
		<form method="POST" action="{url module=backend controller=document action=saveDocument}">
		<input type="hidden" value="{$id}" name="id">
		<fieldset>
		<legend>Daten</legend>
			<ul>
				<li>
					<label>Name</label>
					<input type="text" value="{$Document.name}" name="name">
				</li>
				<li>
					<label>ID</label>
					<input type="text" value="{$Document.id}" disabled>
				</li>
				<li>
					<label>Template</label>
					<input type="text" value="{$Document.template}" name="template">
				</li>
				<li>
					<label>Nummernkreis</label>
					<input type="text" value="{$Document.numbers}" name="numbers">
				</li>
				<li>
					<label>Abstands Links (mm)</label>
					<input type="text" value="{$Document.left}" name="left">
				</li>
				<li>
					<label>Abstands Rechts (mm)</label>
					<input type="text" value="{$Document.right}" name="right">
				</li>
				<li>
					<label>Abstand Oben (mm)</label>
					<input type="text" value="{$Document.top}" name="top">
				</li>
				<li>
					<label>Abstand Unten (mm)</label>
					<input type="text" value="{$Document.bottom}" name="bottom">
				</li>
				<li>
					<label>Artikel pro Seite</label>
					<input type="text" value="{$Document.pagebreak}" name="pagebreak">
				</li>
			</ul>
			<input type="submit" value="Speichern">
		</fieldset>
		</form>
		<form method="POST" name="selectElementForm" action="{url module=backend controller=document action=detail}">	
		    <input type="hidden" value="{$id}" name="id">
			<fieldset style="width:590px;height:60px;float:left;margin-right:10px">
				<legend>Elemente bearbeiten</legend>
				<ul>
					<li>
						<label>Element:</label>
						<select name="selectElement" onchange="document.selectElementForm.submit();">
							<option>Bitte wählen</option>
							{foreach from=$Elements item=element}
								<option value="{$element.id}" {if $selectedElement.id == $element.id}selected{/if}>{$element.name}</option>
							{/foreach}
						</select>
					</li>
					<li>
						<a href="#" class="ico3 document_arrow" onclick="askDuplicate();">Eigenschaften für alle Dokumenttypen übernehmen</a>
					</li>
				</ul>
			</fieldset>
		</form>
		{if $selectedElement}
		<form method="POST" action="{url module=backend controller=document action=saveDetail}">
			<input type="hidden" name="selectElement" value="{$selectedElement.id}">
			<input type="hidden" name="id" value="{$id}">
			<fieldset style="width:590px;height:430px;float:left">
				<legend>Inhalt des Elements {$selectedElement.name}</legend>
				<textarea style="width:100%;height:250px" name="ElementValue" mce_editable="true" wrap="off" id="ElementValue">{$selectedElement.value}</textarea>
				<input type="submit" value="Speichern">
				{$TranslationValue}
			</fieldset>
			
			<fieldset style="width:590px;height:320px;float:left">
				<legend>Style des Elements {$selectedElement.name}</legend>
			    <textarea style="width:100%;height:250px" wrap="off" name="ElementStyle" id="ElementStyle">{$selectedElement.style|replace:"	":""|replace:"	":""|trim}</textarea>
			    <input type="submit" value="Speichern">
			    {$TranslationStyle}
			</fieldset>
			
		</form>
		{/if}
	</div>
	
	
	<div class="containerRight">
	<fieldset>
	<legend>Beleg Schema / Elemente</legend>
		<div class="containerDocument">
			<div style="border:1px dotted;position:absolute;width:25px;height:15px;margin-left:5px;margin-top:15px;text-align:center">Body</div>
			<div style="width:30px;margin:0 auto;height:5px;background-color:#CCC"></div>
			<div style="position:absolute;width:5px;margin-top:202px;height:30px;background-color:#CCC"></div>
			<div style="position:absolute;width:5px;margin-top:202px;margin-left:310px;height:30px;background-color:#CCC"></div>
			<div style="position:absolute;width:30px;margin-top:425px;;margin-left:140px;height:5px;background-color:#CCC"></div>
			
			<div style="border:1px dotted;position:absolute;width:90px;height:25px;margin-left:45px;margin-top:15px;text-align:center">Logo</div>
			
			<div style="border:1px dotted;position:absolute;width:100px;height:15px;margin-left:45px;margin-top:50px;text-align:center;font-weight:bold">[Header_Box_Left]</div>			

			<div style="border:1px dotted;position:absolute;width:100px;height:45px;margin-left:45px;margin-top:65px;text-align:center">Header_Sender<br />Header_Recipient</div>
			
			<div style="border:1px dotted;position:absolute;width:60px;height:110px;margin-left:245px;margin-top:55px;text-align:center;font-weight:bold">Header_<br />Box_<br />Right</div>
			
			
			<div style="border:1px dotted;position:absolute;width:260px;height:120px;margin-left:45px;margin-top:45px;text-align:center">[Header]</div>
			
			<div style="border:1px dotted;position:absolute;width:160px;height:20px;margin-left:45px;margin-top:180px;text-align:center">Header_Box_Bottom</div>
			
			<div style="border:1px dotted;position:absolute;width:250px;height:105px;margin-left:45px;margin-top:225px;text-align:center">Content<br />Td/Td_Head/Td_Line/Td_Name</div>
			
			<div style="border:1px dotted;position:absolute;width:95px;height:45px;margin-left:200px;margin-top:335px;text-align:center">Content_Amount</div>
		
			<div style="border:1px dotted;position:absolute;width:95px;height:35px;margin-left:45px;margin-top:335px;text-align:center">Content_Info</div>		
			
			<div style="border:1px dotted;position:absolute;width:250px;height:15px;margin-left:45px;margin-top:400px;text-align:center">Footer</div>		
	</div>
		
	</fieldset>
	<form method="POST" action="{url module=backend controller=document action=index}" target="_blank">
	<input type="hidden" name="preview" value="1">
	<input type="hidden" name="sampleData" value="1">
	<input type="hidden" name="typ" value="{$Document.id-1}">
	
	<fieldset>
		<legend><a class="ico page_white_acrobat"></a>Preview</legend>
		<ul><li>
		<label>Typ:</label>
		<select name="typTemp" disabled>
			<option value="{$Document.id-1}">{$Document.name}</option>
		</select>
		</li>
		<li>
			<label>Seitenumbruch</label>
			<input type="checkbox" name="pagebreak">
		</li>
		</ul>
		<input type="submit" value="Öffnen">
	</fieldset>
	</form>
	</div>
</div>
{include file="../../engine/backend/elements/window/translations.htm"}
<script type="text/javascript" src="{$Path}engine/backend/js/translations.php"></script>
{/block}