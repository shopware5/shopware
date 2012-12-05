<?php
/**
 * Helper to create colored lines on shell
 * @description Get diff between revisions and create various install and patch
 * packages for auto deployment
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Stefan Hamann
 * @package Tools
 * @subpackage Deployment
 */
class cmd_colors {

   static private $fg_colors = array(
         "black" => "30",
         "red" => "31",
         "green" => "32",
         "yellow" => "33",
         "blue" => "34",
         "purple" => "35",
         "cyan" => "36",
         "white" => "37"
      );

   static private $bg_colors = array(
         "black" => "40",
         "red" => "41",
         "green" => "42",
         "yellow" => "43",
         "blue" => "44",
         "purple" => "45",
         "cyan" => "46",
         "white" => "47"
      );

  /**
   * Echo normal formated text
   * @static
   * @param  $color
   * @param  $text
   * @param null $bgcolor
   * @return string
   */
   public static function normal($color, $text, $bgcolor=null) {
      return "\033[0;".self::$fg_colors[$color].(!is_null($bgcolor) ? ";".self::$bg_colors[$bgcolor] : "")."m".$text."\033[0m";
   }

	/**
	 * Echo bold formated text
	 * @static
	 * @param  $color
	 * @param  $text
	 * @param null $bgcolor
	 * @return string
	 */
   public static function bold($color, $text, $bgcolor=null) {
      return "\033[1;".self::$fg_colors[$color].(!is_null($bgcolor) ? ";".self::$bg_colors[$bgcolor] : "")."m".$text."\033[0m";
   }

	/**
	 * Echo underline formated text
	 * @static
	 * @param  $color
	 * @param  $text
	 * @param null $bgcolor
	 * @return string
	 */
   public static function underline($color, $text, $bgcolor=null) {
      return "\033[4;".self::$fg_colors[$color].(!is_null($bgcolor) ? ";".self::$bg_colors[$bgcolor] : "")."m".$text."\033[0m";
   }

}