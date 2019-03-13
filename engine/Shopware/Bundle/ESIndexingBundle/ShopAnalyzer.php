<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace Shopware\Bundle\ESIndexingBundle;

use Shopware\Bundle\StoreFrontBundle\Struct\Shop;

class ShopAnalyzer implements ShopAnalyzerInterface
{
    /**
     * @see https://www.elastic.co/guide/en/elasticsearch/guide/current/analysis-intro.html
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/analysis-lang-analyzer.html
     *
     * @return string[]
     */
    public function get(Shop $shop)
    {
        $mapping = ['aa_DJ' => 'afar', 'aa_ER' => 'afar', 'aa_ET' => 'afar', 'af_NA' => 'afrikaans', 'af_ZA' => 'afrikaans', 'ak_GH' => 'akan', 'am_ET' => 'amharic', 'ar_AE' => 'arabic', 'ar_BH' => 'arabic', 'ar_DZ' => 'arabic', 'ar_EG' => 'arabic', 'ar_IQ' => 'arabic', 'ar_JO' => 'arabic', 'ar_KW' => 'arabic', 'ar_LB' => 'arabic', 'ar_LY' => 'arabic', 'ar_MA' => 'arabic', 'ar_OM' => 'arabic', 'ar_QA' => 'arabic', 'ar_SA' => 'arabic', 'ar_SD' => 'arabic', 'ar_SY' => 'arabic', 'ar_TN' => 'arabic', 'ar_YE' => 'arabic', 'as_IN' => 'assamese', 'az_AZ' => 'azerbaijani', 'be_BY' => 'belarusian', 'bg_BG' => 'bulgarian', 'bn_BD' => 'bengali', 'bn_IN' => 'bengali', 'bo_CN' => 'tibetan', 'bo_IN' => 'tibetan', 'bs_BA' => 'bosnian', 'byn_ER' => 'blin', 'ca_ES' => 'catalan', 'cch_NG' => 'atsam', 'cs_CZ' => 'czech', 'cy_GB' => 'welsh', 'da_DK' => 'danish', 'de_AT' => 'german', 'de_BE' => 'german', 'de_CH' => 'german', 'de_DE' => 'german', 'de_LI' => 'german', 'de_LU' => 'german', 'dv_MV' => 'divehi', 'dz_BT' => 'dzongkha', 'ee_GH' => 'ewe', 'ee_TG' => 'ewe', 'el_CY' => 'greek', 'el_GR' => 'greek', 'en_AS' => 'english', 'en_AU' => 'english', 'en_BE' => 'english', 'en_BW' => 'english', 'en_BZ' => 'english', 'en_CA' => 'english', 'en_GB' => 'english', 'en_GU' => 'english', 'en_HK' => 'english', 'en_IE' => 'english', 'en_IN' => 'english', 'en_JM' => 'english', 'en_MH' => 'english', 'en_MP' => 'english', 'en_MT' => 'english', 'en_NA' => 'english', 'en_NZ' => 'english', 'en_PH' => 'english', 'en_PK' => 'english', 'en_SG' => 'english', 'en_TT' => 'english', 'en_UM' => 'english', 'en_US' => 'english', 'en_VI' => 'english', 'en_ZA' => 'english', 'en_ZW' => 'english', 'es_AR' => 'spanish', 'es_BO' => 'spanish', 'es_CL' => 'spanish', 'es_CO' => 'spanish', 'es_CR' => 'spanish', 'es_DO' => 'spanish', 'es_EC' => 'spanish', 'es_ES' => 'spanish', 'es_GT' => 'spanish', 'es_HN' => 'spanish', 'es_MX' => 'spanish', 'es_NI' => 'spanish', 'es_PA' => 'spanish', 'es_PE' => 'spanish', 'es_PR' => 'spanish', 'es_PY' => 'spanish', 'es_SV' => 'spanish', 'es_US' => 'spanish', 'es_UY' => 'spanish', 'es_VE' => 'spanish', 'et_EE' => 'estonian', 'eu_ES' => 'basque', 'fa_AF' => 'persian', 'fa_IR' => 'persian', 'fi_FI' => 'finnish', 'fil_PH' => 'filipino', 'fo_FO' => 'faroese', 'fr_BE' => 'french', 'fr_CA' => 'french', 'fr_CH' => 'french', 'fr_FR' => 'french', 'fr_LU' => 'french', 'fr_MC' => 'french', 'fr_SN' => 'french', 'fur_IT' => 'friulian', 'ga_IE' => 'irish', 'gaa_GH' => 'ga', 'gez_ER' => 'geez', 'gez_ET' => 'geez', 'gl_ES' => 'galician', 'gsw_CH' => 'swiss german', 'gu_IN' => 'gujarati', 'gv_GB' => 'manx', 'ha_GH' => 'hausa', 'ha_NE' => 'hausa', 'ha_NG' => 'hausa', 'ha_SD' => 'hausa', 'haw_US' => 'hawaiian', 'he_IL' => 'hebrew', 'hi_IN' => 'hindi', 'hr_HR' => 'croatian', 'hu_HU' => 'hungarian', 'hy_AM' => 'armenian', 'id_ID' => 'indonesian', 'ig_NG' => 'igbo', 'ii_CN' => 'sichuan yi', 'is_IS' => 'icelandic', 'it_CH' => 'italian', 'it_IT' => 'italian', 'ja_JP' => 'japanese', 'ka_GE' => 'georgian', 'kaj_NG' => 'jju', 'kam_KE' => 'kamba', 'kcg_NG' => 'tyap', 'kfo_CI' => 'koro', 'kk_KZ' => 'kazakh', 'kl_GL' => 'kalaallisut', 'km_KH' => 'khmer', 'kn_IN' => 'kannada', 'ko_KR' => 'korean', 'kok_IN' => 'konkani', 'kpe_GN' => 'kpelle', 'kpe_LR' => 'kpelle', 'ku_IQ' => 'kurdish', 'ku_IR' => 'kurdish', 'ku_SY' => 'kurdish', 'ku_TR' => 'kurdish', 'kw_GB' => 'cornish', 'ky_KG' => 'kirghiz', 'ln_CD' => 'lingala', 'ln_CG' => 'lingala', 'lo_LA' => 'lao', 'lt_LT' => 'lithuanian', 'lv_LV' => 'latvian', 'mk_MK' => 'macedonian', 'ml_IN' => 'malayalam', 'mn_CN' => 'mongolian', 'mn_MN' => 'mongolian', 'mr_IN' => 'marathi', 'ms_BN' => 'malay', 'ms_MY' => 'malay', 'mt_MT' => 'maltese', 'my_MM' => 'burmese', 'nb_NO' => 'norwegian bokmål', 'nds_DE' => 'low german', 'ne_IN' => 'nepali', 'ne_NP' => 'nepali', 'nl_BE' => 'dutch', 'nl_NL' => 'dutch', 'nn_NO' => 'norwegian nynorsk', 'nr_ZA' => 'south ndebele', 'nso_ZA' => 'northern sotho', 'ny_MW' => 'nyanja', 'oc_FR' => 'occitan', 'om_ET' => 'oromo', 'om_KE' => 'oromo', 'or_IN' => 'oriya', 'pa_IN' => 'punjabi', 'pa_PK' => 'punjabi', 'pl_PL' => 'polish', 'ps_AF' => 'pashto', 'pt_BR' => 'portuguese', 'pt_PT' => 'portuguese', 'ro_MD' => 'romanian', 'ro_RO' => 'romanian', 'ru_RU' => 'russian', 'ru_UA' => 'russian', 'rw_RW' => 'kinyarwanda', 'sa_IN' => 'sanskrit', 'se_FI' => 'northern sami', 'se_NO' => 'northern sami', 'sh_BA' => 'serbo-croatian', 'sh_CS' => 'serbo-croatian', 'sh_YU' => 'serbo-croatian', 'si_LK' => 'sinhala', 'sid_ET' => 'sidamo', 'sk_SK' => 'slovak', 'sl_SI' => 'slovenian', 'so_DJ' => 'somali', 'so_ET' => 'somali', 'so_KE' => 'somali', 'so_SO' => 'somali', 'sq_AL' => 'albanian', 'sr_BA' => 'serbian', 'sr_CS' => 'serbian', 'sr_ME' => 'serbian', 'sr_RS' => 'serbian', 'sr_YU' => 'serbian', 'ss_SZ' => 'swati', 'ss_ZA' => 'swati', 'st_LS' => 'southern sotho', 'st_ZA' => 'southern sotho', 'sv_FI' => 'swedish', 'sv_SE' => 'swedish', 'sw_KE' => 'swahili', 'sw_TZ' => 'swahili', 'syr_SY' => 'syriac', 'ta_IN' => 'tamil', 'te_IN' => 'telugu', 'tg_TJ' => 'tajik', 'th_TH' => 'thai', 'ti_ER' => 'tigrinya', 'ti_ET' => 'tigrinya', 'tig_ER' => 'tigre', 'tn_ZA' => 'tswana', 'to_TO' => 'tonga', 'tr_TR' => 'turkish', 'ts_ZA' => 'tsonga', 'tt_RU' => 'tatar', 'ug_CN' => 'uighur', 'uk_UA' => 'ukrainian', 'ur_IN' => 'urdu', 'ur_PK' => 'urdu', 'uz_AF' => 'uzbek', 'uz_UZ' => 'uzbek', 've_ZA' => 'venda', 'vi_VN' => 'vietnamese', 'wal_ET' => 'walamo', 'wo_SN' => 'wolof', 'xh_ZA' => 'xhosa', 'yo_NG' => 'yoruba', 'zh_CN' => 'chinese', 'zh_HK' => 'chinese', 'zh_MO' => 'chinese', 'zh_SG' => 'chinese', 'zh_TW' => 'chinese', 'zu_ZA' => 'zulu'];
        $supported = ['arabic', 'armenian', 'basque', 'brazilian', 'bulgarian', 'catalan', 'chinese', 'cjk', 'czech', 'danish', 'dutch', 'english', 'finnish', 'french', 'galician', 'german', 'greek', 'hindi', 'hungarian', 'indonesian', 'irish', 'italian', 'latvian', 'norwegian', 'persian', 'portuguese', 'romanian', 'russian', 'sorani', 'spanish', 'swedish', 'turkish', 'thai'];
        $language = $shop->getLocale()->getLocale();

        if (!isset($mapping[$language])) {
            return ['standard'];
        }

        $language = $mapping[$language];
        $language = strtolower($language);

        if (!in_array($language, $supported)) {
            return ['standard'];
        }

        return [$language];
    }
}
