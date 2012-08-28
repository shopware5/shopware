<?php
class Shopware_Deprecated_Html2PS {
	public function __construct(){
		require_once('engine/vendor/html2ps/config.inc.php');
		require_once('engine/vendor/html2ps/pipeline.factory.class.php');
		require_once('engine/vendor/html2ps/shopware.php');
		@set_time_limit(20000);
		parse_config_file(HTML2PS_DIR.'html2ps.config');
	}
	
	public function convert($html, $path_to_pdf, $pixels = 792) {
	
	  $pipeline = PipelineFactory::create_default_pipeline("", "");
	  // Override HTML source 
	  $pipeline->fetchers[] = new MyFetcherLocalFile($html['base']);
	  // Override destination to local file
	  $pipeline->destination = new MyDestinationFile($path_to_pdf);
	  
	  $pipeline->data_filters[] = new DataFilterHTML2XHTML;
	  $pipeline->pre_tree_filters = array();
	  $header_html    = "test";
	  $footer_html    = "test";
	  $filter = new PreTreeFilterHeaderFooter($header_html, $footer_html);
	  $pipeline->pre_tree_filters[] = $filter;
	  $pipeline->pre_tree_filters[] = new PreTreeFilterHTML2PSFields();
	  $pipeline->parser = new ParserXHTML();
	  $pipeline->layout_engine = new LayoutEngineDefault;
	
	
	  $baseurl = "";
	  $media = Media::predefined("A4");
	  $media->set_landscape(false);
	  $media->set_margins(array('left'   => 0,
	                            'right'  => 0,
	                            'top'    => 0,
	                            'bottom' => 0));
	  $media->set_pixels($pixels); 
	
		global $g_config;
		$g_config = array(
			'cssmedia'     => 'screen',
			'media' => 'A4',
			'scalepoints'  => '1',
			'renderimages' => true,
	 		'renderlinks'  => true,
			'renderfields' => true,
	 		'renderforms'  => true,
			'html2xhtml2' => 1,
			'mode2'         => 'html',
			'encoding'     => '',
			'debugbox'     => false,
			'compress' => true,
			'pdfversion'    => '1.4',
			'draw_page_border' => false,
		);
		
	  $pipeline->configure($g_config);
	  $pipeline->process($baseurl, $media);
	}
}
?>