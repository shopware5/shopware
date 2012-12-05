<?php

/*
  ##############################################################################
  # Plugin for Shopware
  # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
  # @version $Id: Meta.php 107 2012-01-13 10:21:36Z a.knack $
  # @copyright:   found in /lic/copyright.txt
  #
  ##############################################################################
 */

return array(
		'version' => $this->getVersion(),
		'autor' => 'BuI Hinsche GmbH',
		'copyright' => 'Copyright (c) 2012, BuI Hinsche GmbH',
		'label' => self::LABEL, //$this->getName(),
		'source' => $this->getSource(),
		'description' => '',
		'license' => '',
		'support' => 'http://bui-hinsche.de',
		'link' => 'http://bui-hinsche.de',
		'revision' => ' $Id$ ',
		'changes' => array(
				'1.0.0' => array('releasedate' => '2012-06-27', 'lines' => array(
								'ak: First release'
				))
		)
);
?>