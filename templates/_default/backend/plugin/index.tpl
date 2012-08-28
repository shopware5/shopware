{extends file='backend/index/parent.tpl'}

{block name="backend_index_css" append}
	<link href="{link file='backend/_resources/styles/Ext.ux.FileUploadField.css'}" rel="stylesheet" type="text/css" />
	<style type="text/css">
	.inactive {
		opacity: 0.5;
	}

	.red {
		color:#db6800;
	}

	.green {
		color: #008000;
	}
	
	a.ico {
		height:20px;
		margin:0 0 0 5px;
		padding:0;
		width:20px;
		cursor:pointer;
		float:right;
	}
	</style>
{/block}

{block name="backend_index_javascript" append}
	<script type="text/javascript" src="{link file='backend/_resources/javascript/plugins/Ext.ux.FileUploadField.js'}"></script>
	<script type="text/javascript" src="{link file='frontend/_resources/javascript/jquery-1.7.2.min.js'}"></script>
	<script type="text/javascript" src="{link file='engine/backend/css/icons.css'}"></script>
	<script type="text/javascript" src="{link file='engine/backend/css/icons4.css'}"></script>
	<script type="text/javascript" src="{link file='engine/backend/js/ext.rowExpander.js'}"></script>
<script type="text/javascript">
//<![CDATA[
	Ext.ux.IFrameComponent = Ext.extend(Ext.BoxComponent, {
		onRender : function(ct, position){
		  this.el = ct.createChild({ tag: 'iframe', id: 'framepanel'+this.id, frameBorder: 0, src: this.url});
		}
	});
//]]>
</script>
	{include file='backend/plugin/update.tpl'}
	{include file='backend/plugin/dlupdate.tpl'}
	{include file='backend/plugin/list.tpl'}
	{include file='backend/plugin/upload.tpl'}
	{include file='backend/plugin/viewport.tpl'}
<script type="text/javascript">
//<![CDATA[
var Viewport
Ext.onReady(function(){
	Viewport = new Shopware.Plugin.Viewport;
});
//]]>
</script>
{/block}