<?php

/**
 * Removes invalid characters from xml string.
 */
function stripInvalidXml($value)
{
	$ret = "";
	$current;

	if (empty($value)) {
		return $ret;
	}

	$length = strlen($value);

	for ($i = 0; $i < $length; $i++) {
		$current = ord($value{$i});

		if (($current == 0x9) ||
			($current == 0xA) ||
			($current == 0xD) ||
			(($current >= 0x20) && ($current <= 0xD7FF)) ||
			(($current >= 0xE000) && ($current <= 0xFFFD)) ||
			(($current >= 0x10000) && ($current <= 0x10FFFF))
		) {

			$ret .= chr($current);

		} else {
			$ret .= " ";
		}
	}
	return $ret;
}

/**
 * Removes all invalid characters in xml string, e.g. & replaces with &amp; , < replaces with &lt; ....
 */
function normalizeXML($data)
{
	$LT = '<';
	$GT = '>';
	$res = "";

	//remove all invalid xml characters
	$data = stripInvalidXml($data);
	$lastTagName = "";

	for ($startIndex = 0; $startIndex < strlen($data);) {
		if ($data[$startIndex] == $LT) {
			// parse tag name
			//echo "\nTag";
			$tagStart = $startIndex;
			$tagEnd = strpos($data, $GT, $tagStart);
			$tagName = trim(substr($data, $tagStart + 1, $tagEnd - $tagStart - 1));
			//$tagName = htmlspecialchars_decode($tagName);
			//$tagName = htmlspecialchars($tagName);
			$lastTagName = $tagName;
			$tag = $LT . $tagName . $GT;
			$res = $res . $tag;
			//echo "\n" . $res;
			$startIndex = $tagEnd + 1;

		} else {
			// parse value
			//echo "\nValue";
			$endTag = $LT . "/" . $lastTagName . $GT;
			$valueStart = $startIndex;
			$valueEnd = strpos($data, $endTag, $valueStart);
			$startNextTag = strpos($data, $LT, $valueStart);

			if ($valueEnd === false) {
				$valueEnd = $startNextTag;
			}

			if ($startNextTag < $valueEnd) {
				$valueEnd = $startNextTag;
			}

			$value = trim(substr($data, $valueStart, $valueEnd - $valueStart));
			$value = htmlspecialchars_decode($value);
			$value = htmlspecialchars($value);

			$res = $res . $value;
			//echo "\n" . $res;
			$startIndex = $valueEnd;
		}
	}

	return $res;
}