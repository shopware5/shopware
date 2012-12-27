<?php


if ($_REQUEST['pdf']) { $pdf = $_REQUEST['pdf']; }
else { $pdf = 0; }

if ($_REQUEST['systemdir']) { $pdf = $_REQUEST['systemdir']; }
else { $systemdir = 0; }


ini_set("memory_limit","256M");


define('_MPDF_PATH','../');

include("../mpdf.php");
$mpdf=new mPDF('s');
$mpdf->useSubstitutionsMB = true;
if ($systemdir) { 
	if (defined('_MPDF_SYSTEM_TTFONTS')) { $ttfdir = _MPDF_SYSTEM_TTFONTS; }
	else { die('Must define _MPDF_SYSTEM_TTFONTS!'); }
}
else { $ttfdir = _MPDF_TTFONTPATH; }



$mqr=ini_get("magic_quotes_runtime");
if ($mqr) { set_magic_quotes_runtime(0); }
		if (!class_exists('TTFontFile', false)) { include(_MPDF_PATH .'classes/ttfontsuni.php'); }
$ttf = new TTFontFile();

$tempfontdata = array();
$tempsansfonts = array();
$tempseriffonts = array();
$tempmonofonts = array();
$tempfonttrans = array();

$ff = scandir($ttfdir);

///////////////
/////////////////
foreach($ff AS $f) {
	$ret = array();
	$isTTC = false;
	if (strtolower(substr($f,-4,4))=='.ttc' || strtolower(substr($f,-4,4))=='.ttcf') {	// Mac ttcf
		$isTTC = true;
		$ttf->getTTCFonts($ttfdir.$f);
		$nf = $ttf->numTTCFonts;
		for ($i=1; $i<=$nf; $i++) {
			$ret[] = $ttf->extractCoreInfo($ttfdir.$f, $i);
		}
	}
	else if (strtolower(substr($f,-4,4))=='.ttf') {
		$ret[] = $ttf->extractCoreInfo($ttfdir.$f);
	}
	for ($i=0; $i<count($ret); $i++) {
	   if (!is_array($ret[$i])) { 
		if (!$pdf) echo $ret[$i].'<br />'; 
	   }
	   else {
		$tfname = $ret[$i][0];
		$bold = $ret[$i][1];
		$italic = $ret[$i][2];
		$fname = strtolower($tfname );
		$fname = preg_replace('/[ ()]/','',$fname );
		$tempfonttrans[$tfname] = $fname;
		$style = '';
		if ($bold) { $style .= 'B'; }
		if ($italic) { $style .= 'I'; }
		if (!$style) { $style = 'R'; }
		$tempfontdata[$fname][$style] = $f;
		if ($isTTC) { 
			$tempfontdata[$fname]['TTCfontID'][$style] = $ret[$i][4];
		}
		if ($ret[$i][5]) { $tempfontdata[$fname]['rtl'] = true; }
		if ($ret[$i][7]) { $tempfontdata[$fname]['cjk'] = true; }
		if ($ret[$i][8]) { $tempfontdata[$fname]['sip'] = true; }

		$ftype = $ret[$i][3];		// mono, sans or serif
		if ($ftype=='sans') { $tempsansfonts[] = $fname; }
		else if ($ftype=='serif') { $tempseriffonts[] = $fname; }
		else if ($ftype=='mono') { $tempmonofonts[] = $fname; }
	   }
	}

}
$tempsansfonts = array_unique($tempsansfonts);
$tempseriffonts = array_unique($tempseriffonts );
$tempmonofonts = array_unique($tempmonofonts );
$tempfonttrans = array_unique($tempfonttrans);

foreach ($tempfontdata AS $fname => $v) {
	if (!isset($tempfontdata[$fname]['R']) || !$tempfontdata[$fname]['R']) {
		echo 'WARNING - Font file for '.$fname.' may be an italic cursive script, or extra-bold etc.<br />';
		if (isset($tempfontdata[$fname]['I']) && $tempfontdata[$fname]['I']) {
			$tempfontdata[$fname]['R'] = $tempfontdata[$fname]['I'];
		}
		else if (isset($tempfontdata[$fname]['B']) && $tempfontdata[$fname]['B']) {
			$tempfontdata[$fname]['R'] = $tempfontdata[$fname]['B'];
		}
		else if (isset($tempfontdata[$fname]['BI']) && $tempfontdata[$fname]['BI']) {
			$tempfontdata[$fname]['R'] = $tempfontdata[$fname]['BI'];
		}
	}
	if (isset($tempfontdata[$fname]['sip']) && $tempfontdata[$fname]['sip']) {
		if (preg_match('/^(.*)-extb/',$fname, $fm)) {
			if (isset($tempfontdata[($fm[1])]) && $tempfontdata[($fm[1])]) {
				$tempfontdata[($fm[1])]['sip-ext'] = $fname;
				if (!$pdf) echo 'INFO - Font file '.$fname.' has been defined as a CJK ext-B for '.($fm[1]).'<br />';
			}
			else if (isset($tempfontdata[($fm[1].'-exta')]) && $tempfontdata[($fm[1].'-exta')]) {
				$tempfontdata[($fm[1].'-exta')]['sip-ext'] = $fname;
				if (!$pdf) echo 'INFO - Font file '.$fname.' has been defined as a CJK ext-B for '.($fm[1].'-exta').'<br />';
			}
		}
		// else { unset($tempfontdata[$fname]['sip']); }
	}
}

$mpdf->fontdata = array_merge($tempfontdata ,$mpdf->fontdata);

	$mpdf->available_unifonts = array();
	foreach ($mpdf->fontdata AS $f => $fs) {
		if (isset($fs['R']) && $fs['R']) { $mpdf->available_unifonts[] = $f; }
		if (isset($fs['B']) && $fs['B']) { $mpdf->available_unifonts[] = $f.'B'; }
		if (isset($fs['I']) && $fs['I']) { $mpdf->available_unifonts[] = $f.'I'; }
		if (isset($fs['BI']) && $fs['BI']) { $mpdf->available_unifonts[] = $f.'BI'; }
	}

	$mpdf->default_available_fonts = $mpdf->available_unifonts;


ksort($tempfonttrans);
$html = '';
$extb = "\xf0\xa0\x81\x86 \xf0\xa0\x81\x8e \xf0\xa0\x81\xa8 \xf0\xa0\x82\x86 \xf0\xa0\x82\x87 \xf0\xa0\x82\x8a ";
foreach($tempfonttrans AS $on=>$mn) {
	if (!file_exists($ttfdir.$mpdf->fontdata[$mn]['R'])) { continue; }
	$html .= '<p style="font-family:'.$on.';">'.$on.' font is available as '.$mn;
	if (isset($mpdf->fontdata[$mn]['sip-ext']) && $mpdf->fontdata[$mn]['sip-ext']) {
		$html .= '; CJK ExtB: '.$extb;
	}
	if (isset($mpdf->fontdata[$mn]['rtl'])) {
		// Hallo world
		$html .= " <span>\xd8\xa3\xd9\x87\xd9\x84\xd8\xa7 \xd9\x88\xd8\xb3\xd9\x87\xd9\x84\xd8\xa7 \xd8\xa7\xd9\x84\xd8\xb9\xd8\xa7\xd9\x84\xd9\x85</span>";
	}
	$html .= '</p>';
}

if ($pdf) {
	$mpdf->WriteHTML($html);
	$mpdf->Output();
	exit;
}

foreach($tempfonttrans AS $on=>$mn) {
	echo '<div style="font-family:\''.$on.'\';">'.$on.' font is available as '.$mn;
	if (isset($mpdf->fontdata[$mn]['sip-ext']) && $mpdf->fontdata[$mn]['sip-ext']) {
		echo '; CJK ExtB: '.$mpdf->fontdata[$mn]['sip-ext'];
	}
	echo '</div>';
}



sort($tempsansfonts);
echo '$this->sans_fonts = array(\''.implode("', '", $tempsansfonts)."');\n";
sort($tempseriffonts);
echo '$this->serif_fonts = array(\''.implode("', '", $tempseriffonts)."');\n";
sort($tempmonofonts);
echo '$this->mono_fonts = array(\''.implode("', '", $tempmonofonts)."');\n";

ksort($tempfontdata);
echo '$this->fontdata = '.var_export($tempfontdata,true).";\n";

exit;

?>