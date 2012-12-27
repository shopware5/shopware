<?php


function GetLangOpts($llcc, $adobeCJK) {
	if (strlen($llcc) == 5) {
		$lang = substr(strtolower($llcc),0,2);
		$country = substr(strtoupper($llcc),3,2);
	}
	else { $lang = strtolower($llcc); $country = ''; }
	$unifonts = "";
	$dir = "ltr";
	$spacing = "";
	$coreSuitable = false;

	switch($lang){
	  CASE "en":
	  CASE "ca":
	  CASE "cy":
	  CASE "da":
	  CASE "de":
	  CASE "es":
	  CASE "eu":
	  CASE "fr":
	  CASE "ga":
	  CASE "fi": 
	  CASE "is":
	  CASE "it":
	  CASE "nl":
	  CASE "no":
	  CASE "pt":
	  CASE "sv":
		// Edit this value to define how mPDF behaves when using new mPDF('-x')
		// If set to TRUE, mPDF will use Adobe core fonts only when it recognises the languages above
		$coreSuitable = true;  break;



	  // RTL Languages
	  CASE "he":  $dir = "rtl";  $spacing = "W";  
		$unifonts = "dejavusans,dejavusansB,dejavusansI,dejavusansBI";  break;

	  // Arabic
	  CASE "ar":  $dir = "rtl";  $spacing = "W";  
		$unifonts = "xbriyaz,xbriyazB,xbriyazI,xbriyazBI,xbzar,xbzarB,xbzarI,xbzarBI";  break;
	  CASE "fa":  $dir = "rtl";  $spacing = "W";  
		$unifonts = "xbriyaz,xbriyazB,xbriyazI,xbriyazBI,xbzar,xbzarB,xbzarI,xbzarBI";  break;
	  CASE "ps":  $dir = "rtl"; $spacing = "W";  
		$unifonts = "xbriyaz,xbriyazB,xbriyazI,xbriyazBI,xbzar,xbzarB,xbzarI,xbzarBI"; break;
	  CASE "ur":  $dir = "rtl"; $spacing = "W";  
		$unifonts = "xbriyaz,xbriyazB,xbriyazI,xbriyazBI,xbzar,xbzarB,xbzarI,xbzarBI"; break;

	  // Sindhi (Arabic or Devanagari)
	  CASE "sd":
		if ($country == "IN") { $spacing = "W"; $unifonts = "ind_hi_1_001"; }
	//	else if ($country == "PK") { $dir = "rtl"; $spacing = "W"; $unifonts = ""; }
	//	else { $dir = "rtl"; $spacing = "W"; $unifonts = ""; }
		break;


	  // INDIC 
	  // Assamese
	  CASE "as":  $spacing = "W";  $unifonts = "ind_bn_1_001"; break;
	  // Bengali
	  CASE "bn":  $spacing = "W";  $unifonts = "ind_bn_1_001"; break;
	  // Gujarati
	  CASE "gu":  $spacing = "W";  $unifonts = "ind_gu_1_001"; break;
	  // Hindi (Devanagari)
	  CASE "hi":  $spacing = "W";  $unifonts = "ind_hi_1_001"; break;
	  // Kannada
	  CASE "kn":  $spacing = "W";  $unifonts = "ind_kn_1_001"; break;
	  // Kashmiri
	  CASE "ks":  $spacing = "W";  $unifonts = "ind_hi_1_001"; break;
	  // Malayalam
	  CASE "ml":  $spacing = "W";  $unifonts = "ind_ml_1_001"; break;
	  // Nepali (Devanagari)
	  CASE "ne":  $spacing = "W";  $unifonts = "ind_hi_1_001"; break;
	  // Oriya
	  CASE "or":  $spacing = "W";  $unifonts = "ind_or_1_001"; break;
	  // Punjabi (Gurmukhi)
	  CASE "pa":  $spacing = "W";  $unifonts = "ind_pa_1_001"; break;
	  // Tamil
	  CASE "ta":  $spacing = "W";  $unifonts = "ind_ta_1_001"; break;
	  // Telegu
	  CASE "te":  $spacing = "W";  $unifonts = "ind_te_1_001"; break;

	  // THAI
	  CASE "th":  $spacing = "C";  $unifonts = "garuda,garudaB,garudaI,garudaBI,norasi,norasiB,norasiI,norasiBI";  break;

	  // VIETNAMESE
	  CASE "vi":  
		$unifonts = "dejavusanscondensed,dejavusanscondensedB,dejavusanscondensedI,dejavusanscondensedBI,dejavusans,dejavusansB,dejavusansI,dejavusansBI"; break;

	  // CJK Langauges
	  CASE "ja":  
		if ($adobeCJK) {
			$unifonts = "sjis,sjisB,sjisI,sjisBI";
		}
/* Uncomment these lines if CJK fonts available */
//		else {
//			$unifonts = "sun-exta,sun-extb,hannoma,hannomb";
//		}
		$spacing = "C"; 
		break;

	  CASE "ko":  
		if ($adobeCJK) {
			$unifonts = "uhc,uhcB,uhcI,uhcBI";
		}
/* Uncomment these lines if CJK fonts available */
//		else {
//			$unifonts = "unbatang_0613"; 
//		}
		$spacing = "C"; 
		break;

	  CASE "zh":
		if ($country == "HK" || $country == "TW") { 
			if ($adobeCJK) {
				$unifonts = "big5,big5B,big5I,big5BI"; 
			}
/* Uncomment these lines if CJK fonts available */
//			else {
//				$unifonts = "sun-exta,sun-extb,hannoma,hannomb";
//			}
		}
		else if ($country == "CN") { 
			if ($adobeCJK) {
				$unifonts = "gb,gbB,gbI,gbBI";  
			}
/* Uncomment these lines if CJK fonts available */
//			else {
//				$unifonts = "sun-exta,sun-extb,hannoma,hannomb";
//			}
		}
	  	else { 
			if ($adobeCJK) {
				$unifonts = "gb,gbB,gbI,gbBI"; 
			}
/* Uncomment these lines if CJK fonts available */
//			else {
//				$unifonts = "sun-exta,sun-extb,hannoma,hannomb";
//			}
		}
		$spacing = "C"; 
		break;

	}


	$unifonts_arr = array();
	if ($unifonts) {
		$unifonts_arr = preg_split('/\s*,\s*/',$unifonts);
	}
	return array($coreSuitable ,$unifonts_arr,$dir,$spacing);
}

?>