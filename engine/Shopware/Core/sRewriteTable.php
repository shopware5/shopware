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

use Shopware\Components\MemoryLimit;
use Shopware\Components\Model\ModelManager;

/**
 * Deprecated Shopware Class that handles url rewrites
 *
 * @category  Shopware
 * @package   Shopware\Core
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class sRewriteTable
{
    /**
     * @var string|null
     */
    protected $rewriteArticleslastId;

    /**
     * Rules from the MIT licensed https://github.com/cocur/slugify project
     * See: https://github.com/cocur/slugify/blob/master/src/Slugify.php#L29
     *
     * @var array
     */
    private $replaceRules = array(
        // Numeric characters
        '¹' => 1,
        '²' => 2,
        '³' => 3,
        // Latin
        '°' => 0,
        'æ' => 'ae',
        'ǽ' => 'ae',
        'À' => 'A',
        'Á' => 'A',
        'Â' => 'A',
        'Ã' => 'A',
        'Å' => 'A',
        'Ǻ' => 'A',
        'Ă' => 'A',
        'Ǎ' => 'A',
        'Æ' => 'AE',
        'Ǽ' => 'AE',
        'à' => 'a',
        'á' => 'a',
        'â' => 'a',
        'ã' => 'a',
        'å' => 'a',
        'ǻ' => 'a',
        'ă' => 'a',
        'ǎ' => 'a',
        'ª' => 'a',
        '@' => 'at',
        'Ĉ' => 'C',
        'Ċ' => 'C',
        'ĉ' => 'c',
        'ċ' => 'c',
        '©' => 'c',
        'Ð' => 'Dj',
        'Đ' => 'D',
        'ð' => 'dj',
        'đ' => 'd',
        'È' => 'E',
        'É' => 'E',
        'Ê' => 'E',
        'Ë' => 'E',
        'Ĕ' => 'E',
        'Ė' => 'E',
        'è' => 'e',
        'é' => 'e',
        'ê' => 'e',
        'ë' => 'e',
        'ĕ' => 'e',
        'ė' => 'e',
        'ƒ' => 'f',
        'Ĝ' => 'G',
        'Ġ' => 'G',
        'ĝ' => 'g',
        'ġ' => 'g',
        'Ĥ' => 'H',
        'Ħ' => 'H',
        'ĥ' => 'h',
        'ħ' => 'h',
        'Ì' => 'I',
        'Í' => 'I',
        'Î' => 'I',
        'Ï' => 'I',
        'Ĩ' => 'I',
        'Ĭ' => 'I',
        'Ǐ' => 'I',
        'Į' => 'I',
        'Ĳ' => 'IJ',
        'ì' => 'i',
        'í' => 'i',
        'î' => 'i',
        'ï' => 'i',
        'ĩ' => 'i',
        'ĭ' => 'i',
        'ǐ' => 'i',
        'į' => 'i',
        'ĳ' => 'ij',
        'Ĵ' => 'J',
        'ĵ' => 'j',
        'Ĺ' => 'L',
        'Ľ' => 'L',
        'Ŀ' => 'L',
        'ĺ' => 'l',
        'ľ' => 'l',
        'ŀ' => 'l',
        'Ñ' => 'N',
        'ñ' => 'n',
        'ŉ' => 'n',
        'Ò' => 'O',
        'Ô' => 'O',
        'Õ' => 'O',
        'Ō' => 'O',
        'Ŏ' => 'O',
        'Ǒ' => 'O',
        'Ő' => 'O',
        'Ơ' => 'O',
        'Ø' => 'O',
        'Ǿ' => 'O',
        'Œ' => 'OE',
        'ò' => 'o',
        'ô' => 'o',
        'õ' => 'o',
        'ō' => 'o',
        'ŏ' => 'o',
        'ǒ' => 'o',
        'ő' => 'o',
        'ơ' => 'o',
        'ø' => 'o',
        'ǿ' => 'o',
        'º' => 'o',
        'œ' => 'oe',
        'Ŕ' => 'R',
        'Ŗ' => 'R',
        'ŕ' => 'r',
        'ŗ' => 'r',
        'Ŝ' => 'S',
        'Ș' => 'S',
        'ŝ' => 's',
        'ș' => 's',
        'ſ' => 's',
        'Ţ' => 'T',
        'Ț' => 'T',
        'Ŧ' => 'T',
        'Þ' => 'TH',
        'ţ' => 't',
        'ț' => 't',
        'ŧ' => 't',
        'þ' => 'th',
        'Ù' => 'U',
        'Ú' => 'U',
        'Û' => 'U',
        'Ũ' => 'U',
        'Ŭ' => 'U',
        'Ű' => 'U',
        'Ų' => 'U',
        'Ư' => 'U',
        'Ǔ' => 'U',
        'Ǖ' => 'U',
        'Ǘ' => 'U',
        'Ǚ' => 'U',
        'Ǜ' => 'U',
        'ù' => 'u',
        'ú' => 'u',
        'û' => 'u',
        'ũ' => 'u',
        'ŭ' => 'u',
        'ű' => 'u',
        'ų' => 'u',
        'ư' => 'u',
        'ǔ' => 'u',
        'ǖ' => 'u',
        'ǘ' => 'u',
        'ǚ' => 'u',
        'ǜ' => 'u',
        'Ŵ' => 'W',
        'ŵ' => 'w',
        'Ý' => 'Y',
        'Ÿ' => 'Y',
        'Ŷ' => 'Y',
        'ý' => 'y',
        'ÿ' => 'y',
        'ŷ' => 'y',
        // Russian
        'Ъ' => '',
        'Ь' => '',
        'А' => 'A',
        'Б' => 'B',
        'Ц' => 'C',
        'Ч' => 'Ch',
        'Д' => 'D',
        'Е' => 'E',
        'Ё' => 'E',
        'Э' => 'E',
        'Ф' => 'F',
        'Г' => 'G',
        'Х' => 'H',
        'И' => 'I',
        'Й' => 'J',
        'Я' => 'Ja',
        'Ю' => 'Ju',
        'К' => 'K',
        'Л' => 'L',
        'М' => 'M',
        'Н' => 'N',
        'О' => 'O',
        'П' => 'P',
        'Р' => 'R',
        'С' => 'S',
        'Ш' => 'Sh',
        'Щ' => 'Shch',
        'Т' => 'T',
        'У' => 'U',
        'В' => 'V',
        'Ы' => 'Y',
        'З' => 'Z',
        'Ж' => 'Zh',
        'ъ' => '',
        'ь' => '',
        'а' => 'a',
        'б' => 'b',
        'ц' => 'c',
        'ч' => 'ch',
        'д' => 'd',
        'е' => 'e',
        'ё' => 'e',
        'э' => 'e',
        'ф' => 'f',
        'г' => 'g',
        'х' => 'h',
        'и' => 'i',
        'й' => 'j',
        'я' => 'ja',
        'ю' => 'ju',
        'к' => 'k',
        'л' => 'l',
        'м' => 'm',
        'н' => 'n',
        'о' => 'o',
        'п' => 'p',
        'р' => 'r',
        'с' => 's',
        'ш' => 'sh',
        'щ' => 'shch',
        'т' => 't',
        'у' => 'u',
        'в' => 'v',
        'ы' => 'y',
        'з' => 'z',
        'ж' => 'zh',
        // German characters
        'Ä' => 'AE',
        'Ö' => 'OE',
        'Ü' => 'UE',
        'ß' => 'ss',
        'ä' => 'ae',
        'ö' => 'oe',
        'ü' => 'ue',
        // Turkish characters
        'Ç' => 'C',
        'Ğ' => 'G',
        'İ' => 'I',
        'Ş' => 'S',
        'ç' => 'c',
        'ğ' => 'g',
        'ı' => 'i',
        'ş' => 's',
        // Latvian
        'Ā' => 'A',
        'Ē' => 'E',
        'Ģ' => 'G',
        'Ī' => 'I',
        'Ķ' => 'K',
        'Ļ' => 'L',
        'Ņ' => 'N',
        'Ū' => 'U',
        'ā' => 'a',
        'ē' => 'e',
        'ģ' => 'g',
        'ī' => 'i',
        'ķ' => 'k',
        'ļ' => 'l',
        'ņ' => 'n',
        'ū' => 'u',
        // Ukrainian
        'Ґ' => 'G',
        'І' => 'I',
        'Ї' => 'Ji',
        'Є' => 'Ye',
        'ґ' => 'g',
        'і' => 'i',
        'ї' => 'ji',
        'є' => 'ye',
        // Czech
        'Č' => 'C',
        'Ď' => 'D',
        'Ě' => 'E',
        'Ň' => 'N',
        'Ř' => 'R',
        'Š' => 'S',
        'Ť' => 'T',
        'Ů' => 'U',
        'Ž' => 'Z',
        'č' => 'c',
        'ď' => 'd',
        'ě' => 'e',
        'ň' => 'n',
        'ř' => 'r',
        'š' => 's',
        'ť' => 't',
        'ů' => 'u',
        'ž' => 'z',
        // Polish
        'Ą' => 'A',
        'Ć' => 'C',
        'Ę' => 'E',
        'Ł' => 'L',
        'Ń' => 'N',
        'Ó' => 'O',
        'Ś' => 'S',
        'Ź' => 'Z',
        'Ż' => 'Z',
        'ą' => 'a',
        'ć' => 'c',
        'ę' => 'e',
        'ł' => 'l',
        'ń' => 'n',
        'ó' => 'o',
        'ś' => 's',
        'ź' => 'z',
        'ż' => 'z',
        // Greek
        'Α' => 'A',
        'Β' => 'B',
        'Γ' => 'G',
        'Δ' => 'D',
        'Ε' => 'E',
        'Ζ' => 'Z',
        'Η' => 'E',
        'Θ' => 'Th',
        'Ι' => 'I',
        'Κ' => 'K',
        'Λ' => 'L',
        'Μ' => 'M',
        'Ν' => 'N',
        'Ξ' => 'X',
        'Ο' => 'O',
        'Π' => 'P',
        'Ρ' => 'R',
        'Σ' => 'S',
        'Τ' => 'T',
        'Υ' => 'Y',
        'Φ' => 'Ph',
        'Χ' => 'Ch',
        'Ψ' => 'Ps',
        'Ω' => 'O',
        'Ϊ' => 'I',
        'Ϋ' => 'Y',
        'ά' => 'a',
        'έ' => 'e',
        'ή' => 'e',
        'ί' => 'i',
        'ΰ' => 'Y',
        'α' => 'a',
        'β' => 'b',
        'γ' => 'g',
        'δ' => 'd',
        'ε' => 'e',
        'ζ' => 'z',
        'η' => 'e',
        'θ' => 'th',
        'ι' => 'i',
        'κ' => 'k',
        'λ' => 'l',
        'μ' => 'm',
        'ν' => 'n',
        'ξ' => 'x',
        'ο' => 'o',
        'π' => 'p',
        'ρ' => 'r',
        'ς' => 's',
        'σ' => 's',
        'τ' => 't',
        'υ' => 'y',
        'φ' => 'ph',
        'χ' => 'ch',
        'ψ' => 'ps',
        'ω' => 'o',
        'ϊ' => 'i',
        'ϋ' => 'y',
        'ό' => 'o',
        'ύ' => 'y',
        'ώ' => 'o',
        'ϐ' => 'b',
        'ϑ' => 'th',
        'ϒ' => 'Y',
        /* Arabic */
        'أ' => 'a',
        'ب' => 'b',
        'ت' => 't',
        'ث' => 'th',
        'ج' => 'g',
        'ح' => 'h',
        'خ' => 'kh',
        'د' => 'd',
        'ذ' => 'th',
        'ر' => 'r',
        'ز' => 'z',
        'س' => 's',
        'ش' => 'sh',
        'ص' => 's',
        'ض' => 'd',
        'ط' => 't',
        'ظ' => 'th',
        'ع' => 'aa',
        'غ' => 'gh',
        'ف' => 'f',
        'ق' => 'k',
        'ك' => 'k',
        'ل' => 'l',
        'م' => 'm',
        'ن' => 'n',
        'ه' => 'h',
        'و' => 'o',
        'ي' => 'y',
        /* Vietnamese */
        'ạ' => 'a',
        'ả' => 'a',
        'ầ' => 'a',
        'ấ' => 'a',
        'ậ' => 'a',
        'ẩ' => 'a',
        'ẫ' => 'a',
        'ằ' => 'a',
        'ắ' => 'a',
        'ặ' => 'a',
        'ẳ' => 'a',
        'ẵ' => 'a',
        'ẹ' => 'e',
        'ẻ' => 'e',
        'ẽ' => 'e',
        'ề' => 'e',
        'ế' => 'e',
        'ệ' => 'e',
        'ể' => 'e',
        'ễ' => 'e',
        'ị' => 'i',
        'ỉ' => 'i',
        'ọ' => 'o',
        'ỏ' => 'o',
        'ồ' => 'o',
        'ố' => 'o',
        'ộ' => 'o',
        'ổ' => 'o',
        'ỗ' => 'o',
        'ờ' => 'o',
        'ớ' => 'o',
        'ợ' => 'o',
        'ở' => 'o',
        'ỡ' => 'o',
        'ụ' => 'u',
        'ủ' => 'u',
        'ừ' => 'u',
        'ứ' => 'u',
        'ự' => 'u',
        'ử' => 'u',
        'ữ' => 'u',
        'ỳ' => 'y',
        'ỵ' => 'y',
        'ỷ' => 'y',
        'ỹ' => 'y',
        'Ạ' => 'A',
        'Ả' => 'A',
        'Ầ' => 'A',
        'Ấ' => 'A',
        'Ậ' => 'A',
        'Ẩ' => 'A',
        'Ẫ' => 'A',
        'Ằ' => 'A',
        'Ắ' => 'A',
        'Ặ' => 'A',
        'Ẳ' => 'A',
        'Ẵ' => 'A',
        'Ẹ' => 'E',
        'Ẻ' => 'E',
        'Ẽ' => 'E',
        'Ề' => 'E',
        'Ế' => 'E',
        'Ệ' => 'E',
        'Ể' => 'E',
        'Ễ' => 'E',
        'Ị' => 'I',
        'Ỉ' => 'I',
        'Ọ' => 'O',
        'Ỏ' => 'O',
        'Ồ' => 'O',
        'Ố' => 'O',
        'Ộ' => 'O',
        'Ổ' => 'O',
        'Ỗ' => 'O',
        'Ờ' => 'O',
        'Ớ' => 'O',
        'Ợ' => 'O',
        'Ở' => 'O',
        'Ỡ' => 'O',
        'Ụ' => 'U',
        'Ủ' => 'U',
        'Ừ' => 'U',
        'Ứ' => 'U',
        'Ự' => 'U',
        'Ử' => 'U',
        'Ữ' => 'U',
        'Ỳ' => 'Y',
        'Ỵ' => 'Y',
        'Ỷ' => 'Y',
        'Ỹ' => 'Y',
        // shopware rules
        ' & ' => '-und-',
        ':' => '-',
        ',' => '-',
        "'" => '-',
        '"' => '-',
        ' ' => '-',
        '+' => '-',
        '&#351;' => 's',
        '&#350;' => 'S',
        '&#287;' => 'g',
        '&#286;' => 'G',
        '&#304;' => 'i',
    );

    /**
     * @var sSystem
     */
    public $sSYSTEM;

    /**
     * @var Enlight_Template_Manager
     */
    protected $template;

    /**
     * @var Smarty_Data
     */
    protected $data;

    /**
     * @var ModelManager
     */
    protected $modelManager;

    /**
     * Database connection which used for each database operation in this class.
     * Injected over the class constructor
     *
     * @var Enlight_Components_Db_Adapter_Pdo_Mysql
     */
    private $db;

    /**
     * Shopware configuration object which used for
     * each config access in this class.
     * Injected over the class constructor
     *
     * @var Shopware_Components_Config
     */
    private $config;

    /**
     * Module manager for core class instances
     *
     * @var Shopware_Components_Modules
     */
    private $moduleManager;

    /**
     * Prepared update PDOStatement for the s_core_rewrite_urls table.
     *
     * @var PDOStatement
     */
    protected $preparedUpdate = null;

    /**
     * Prepared insert PDOStatement for the s_core_rewrite_urls table.
     * @var PDOStatement
     */
    protected $preparedInsert = null;

    /**
     * @param Enlight_Components_Db_Adapter_Pdo_Mysql $db
     * @param Shopware_Components_Config $config
     * @param ModelManager $modelManager
     * @param sSystem $systemModule
     * @param Enlight_Template_Manager $template
     * @param Shopware_Components_Modules $moduleManager
     */
    public function __construct(
        Enlight_Components_Db_Adapter_Pdo_Mysql $db = null,
        Shopware_Components_Config $config = null,
        ModelManager $modelManager = null,
        sSystem $systemModule = null,
        Enlight_Template_Manager $template = null,
        Shopware_Components_Modules $moduleManager = null
    ) {
        $this->db = $db ?: Shopware()->Db();
        $this->config = $config ?: Shopware()->Config();
        $this->modelManager = $modelManager ?: Shopware()->Models();
        $this->sSYSTEM = $systemModule ?: Shopware()->System();
        $this->template = $template ?: Shopware()->Template();
        $this->moduleManager = $moduleManager ?: Shopware()->Modules();
    }

    /**
     * Getter function for retriving last ID from sCreateRewriteTableArticles()
     * @return string|null
     */
    public function getRewriteArticleslastId()
    {
        return $this->rewriteArticleslastId;
    }

    /**
     * Getter function of the prepared insert PDOStatement
     *
     * @return null|PDOStatement
     */
    protected function getPreparedInsert()
    {
        if ($this->preparedInsert === null) {
            $this->preparedInsert = $this->db->prepare('
                INSERT IGNORE INTO s_core_rewrite_urls (org_path, path, main, subshopID)
                VALUES (?, ?, 1, ?)
                ON DUPLICATE KEY UPDATE main=1
            ');
        }
        return $this->preparedInsert;
    }


    /**
     * Getter function of the prepared update PDOStatement
     *
     * @return null|PDOStatement
     */
    protected function getPreparedUpdate()
    {
        if ($this->preparedUpdate === null) {
            $this->preparedUpdate = $this->db->prepare(
                'UPDATE s_core_rewrite_urls
                SET main = 0
                WHERE org_path = ?
                AND path != ?
                AND subshopID = ?
            ');
        }
        return $this->preparedUpdate;
    }

    /**
     * Replace special chars with a URL compliant representation
     *
     * @param string $path
     * @param bool $remove_ds
     * @return string
     */
    public function sCleanupPath($path, $remove_ds = true)
    {
        $path = html_entity_decode($path);
        $path = str_replace(array_keys($this->replaceRules), array_values($this->replaceRules), $path);

        if ($remove_ds) {
            $path = str_replace('/', '-', $path);
        }
        $path = preg_replace('/&[a-z0-9#]+;/i', '', $path);
        $path = preg_replace('#[^0-9a-z-_./]#i', '', $path);
        $path = preg_replace('/-+/', '-', $path);

        return trim($path, '-');
    }

    /**
     * Sets up the environment for seo url calculation
     */
    public function baseSetup()
    {
        MemoryLimit::setMinimumMemoryLimit(1024*1024*512);
        @set_time_limit(0);

        $keys = array_keys($this->template->registered_plugins['function']);
        if (!(in_array('sCategoryPath', $keys))) {
            $this->template->registerPlugin(
                Smarty::PLUGIN_FUNCTION, 'sCategoryPath',
                array($this, 'sSmartyCategoryPath')
            );
        }

        if (!(in_array('createSupplierPath', $keys))) {
            $this->template->registerPlugin(
                Smarty::PLUGIN_FUNCTION, 'createSupplierPath',
                array($this, 'createSupplierPath')
            );
        }

        $this->data = $this->template->createData();

        $this->data->assign('sConfig', $this->config);
        $this->data->assign('sRouter', $this);
        $this->data->assign('sCategoryStart', Shopware()->Shop()->getCategory()->getId());
    }

    /**
     * Main method for re-creating the rewrite table. Triggers all other (more specific) methods
     *
     * @param string $lastUpdate
     * @return string
     */
    public function sCreateRewriteTable($lastUpdate)
    {
        $this->baseSetup();

        $this->sCreateRewriteTableCleanup();
        $this->sCreateRewriteTableStatic();
        $this->sCreateRewriteTableCategories();
        $this->sCreateRewriteTableBlog();
        $this->sCreateRewriteTableCampaigns();
        $lastUpdate = $this->sCreateRewriteTableArticles($lastUpdate);
        $this->sCreateRewriteTableContent();
        $this->sCreateRewriteTableSuppliers(Shopware()->Shop());

        return $lastUpdate;
    }

    /**
     * Cleanup the rewrite table from non-existing resources.
     */
    public function sCreateRewriteTableCleanup()
    {
        // Delete CMS / campaigns
        $this->db->query("
            DELETE ru FROM s_core_rewrite_urls ru
            LEFT JOIN s_cms_static cs
              ON ru.org_path LIKE CONCAT('sViewport=custom&sCustom=', cs.id)
            LEFT JOIN s_cms_support ct
              ON ru.org_path LIKE CONCAT('sViewport=ticket&sFid=', ct.id)
            WHERE (
                ru.org_path LIKE 'sViewport=custom&sCustom=%'
                OR ru.org_path LIKE 'sViewport=ticket&sFid=%'
                OR ru.org_path LIKE 'sViewport=campaign&sCampaign=%'
                OR ru.org_path LIKE 'sViewport=content&sContent=%'
            )
            AND cs.id IS NULL
            AND ct.id IS NULL"
        );

        // delete non-existing blog categories from rewrite table
        $this->db->query("
            DELETE ru FROM s_core_rewrite_urls ru
            LEFT JOIN s_categories c
              ON c.id = REPLACE(ru.org_path, 'sViewport=blog&sCategory=', '')
              AND c.blog = 1
            WHERE ru.org_path LIKE 'sViewport=blog&sCategory=%'
            AND c.id IS NULL"
        );

        // delete non-existing categories
        $this->db->query("
            DELETE ru FROM s_core_rewrite_urls ru
            LEFT JOIN s_categories c
              ON c.id = REPLACE(ru.org_path, 'sViewport=cat&sCategory=', '')
              AND (c.external = '' OR c.external IS NULL)
              AND c.blog = 0
            WHERE ru.org_path LIKE 'sViewport=cat&sCategory=%'
            AND c.id IS NULL"
        );

        // delete non-existing articles
        $this->db->query("
            DELETE ru FROM s_core_rewrite_urls ru
            LEFT JOIN s_articles a
              ON a.id = REPLACE(ru.org_path, 'sViewport=detail&sArticle=', '')
            WHERE ru.org_path LIKE 'sViewport=detail&sArticle=%'
            AND a.id IS NULL"
        );

        // delete all non-existing suppliers
        $this->db->query("
            DELETE ru FROM s_core_rewrite_urls ru
            LEFT JOIN s_articles_supplier s
              ON s.id = REPLACE(ru.org_path, 'sViewport=listing&sAction=manufacturer&sSupplier=', '')
            WHERE ru.org_path LIKE 'sViewport=listing&sAction=manufacturer&sSupplier=%'
            AND s.id IS NULL"
        );
    }

    /**
     * Create the static rewrite rules from config
     */
    public function sCreateRewriteTableStatic()
    {
        $seoStaticUrls = $this->config->get('sSEOSTATICURLS');
        if (empty($seoStaticUrls)) {
            return;
        }
        $static = array();
        $urls = $this->template->fetch('string:' . $seoStaticUrls, $this->data);

        if (!empty($urls)) {
            foreach (explode("\n", $urls) as $url) {
                list($key, $value) = explode(',', trim($url));
                if (empty($key) || empty($value)) {
                    continue;
                }
                $static[$key] = $value;
            }
        }

        foreach ($static as $org_path => $name) {
            $path = $this->sCleanupPath($name, false);
            $this->sInsertUrl($org_path, $path);
        }
    }

    /**
     * Create rewrite rules for categories
     * Default, deprecated method which updates rewrite URLs depending on the current shop
     *
     * @param null $offset
     * @param null $limit
     */
    public function sCreateRewriteTableCategories($offset = null, $limit = null)
    {
        $routerCategoryTemplate = $this->config->get('routerCategoryTemplate');
        if (empty($routerCategoryTemplate)) {
            return;
        }

        $parentId = Shopware()->Shop()->getCategory()->getId();
        $categories = $this->modelManager->getRepository('Shopware\Models\Category\Category')
            ->getActiveChildrenList($parentId);

        if (isset($offset) && isset($limit)) {
            $categories = array_slice($categories, $offset, $limit);
        }

        $template = 'string:' . $routerCategoryTemplate;
        $template = $this->template->createTemplate($template, $this->data);

        foreach ($categories as $category) {
            if (!empty($category['external'])) {
                continue;
            }

            $template->assign('sCategory', $category);
            $path = $template->fetch();
            $path = $this->sCleanupPath($path, false);

            if ($category['blog']) {
                $orgPath = 'sViewport=blog&sCategory=' . $category['id'];
            } else {
                $orgPath = 'sViewport=cat&sCategory=' . $category['id'];
            }

            $this->sInsertUrl($orgPath, $path);
        }
    }

    /**
     * Create rewrite rules for articles
     *
     * @param string $lastUpdate
     * @param int $limit
     * @param int $offset
     * @return string
     */
    public function sCreateRewriteTableArticles($lastUpdate, $limit = 1000, $offset = 0)
    {
        $lastId = null;

        $routerArticleTemplate = $this->config->get('sROUTERARTICLETEMPLATE');
        if (empty($routerArticleTemplate)) {
            return $lastUpdate;
        }

        $this->db->query(
            'UPDATE `s_articles` SET `changetime`= NOW() WHERE `changetime`=?',
            array('0000-00-00 00:00:00')
        );

        $sql = $this->getSeoArticleQuery();
        $sql = $this->db->limit($sql, $limit, $offset);

        $shopFallbackId = (Shopware()->Shop()->getFallback() instanceof \Shopware\Models\Shop\Shop) ? Shopware()->Shop()->getFallback()->getId() : null;

        $result = $this->db->fetchAll(
            $sql,
            array(
                Shopware()->Shop()->get('parentID'),
                Shopware()->Shop()->getId(),
                $shopFallbackId,
                $lastUpdate
            )
        );

        $result = $this->mapArticleTranslationObjectData($result);

        $result = Shopware()->Events()->filter(
            'Shopware_Modules_RewriteTable_sCreateRewriteTableArticles_filterArticles',
            $result,
            array(
                'shop' => Shopware()->Shop()->getId()
            )
        );

        foreach ($result as $row) {
            $this->data->assign('sArticle', $row);
            $path = $this->template->fetch('string:' . $routerArticleTemplate, $this->data);
            $path = $this->sCleanupPath($path, false);

            $orgPath = 'sViewport=detail&sArticle=' . $row['id'];
            $this->sInsertUrl($orgPath, $path);
            $lastUpdate = $row['changed'];
            $lastId = $row['id'];
        }

        if (!empty($lastId)) {
            $this->db->query(
                'UPDATE s_articles
                SET changetime = DATE_ADD(changetime, INTERVAL 1 SECOND)
                WHERE changetime=?
                AND id > ?',
                array($lastUpdate, $lastId)
            );
        }

        $this->rewriteArticleslastId = $lastId;

        return $lastUpdate;
    }

    /**
     * Helper function which returns the sql query for the seo articles.
     * Used in multiple locations
     *
     * @return string
     */
    public function getSeoArticleQuery()
    {
        return "
            SELECT a.*, d.ordernumber, d.suppliernumber, s.name as supplier, datum as date,
                d.releasedate, changetime as changed, metaTitle, ct.objectdata, ctf.objectdata as objectdataFallback, at.attr1, at.attr2,
                at.attr3, at.attr4, at.attr5, at.attr6, at.attr7, at.attr8, at.attr9,
                at.attr10,at.attr11, at.attr12, at.attr13, at.attr14, at.attr15, at.attr16,
                at.attr17, at.attr18, at.attr19, at.attr20
            FROM s_articles a

            INNER JOIN s_articles_categories_ro ac
                ON  ac.articleID = a.id
                AND ac.categoryID = ?
            INNER JOIN s_categories c
                ON  c.id = ac.categoryID
                AND c.active = 1

            JOIN s_articles_details d
                ON d.id = a.main_detail_id

            LEFT JOIN s_articles_attributes at
                ON at.articledetailsID=d.id

            LEFT JOIN s_core_translations ct
                ON ct.objectkey=a.id
                AND ct.objectlanguage=?
                AND ct.objecttype='article'

            LEFT JOIN s_core_translations ctf
                ON ctf.objectkey=a.id
                AND ctf.objectlanguage=?
                AND ctf.objecttype='article'

            LEFT JOIN s_articles_supplier s
                ON s.id=a.supplierID

            WHERE a.active=1
            AND a.changetime > ?
            GROUP BY a.id
            ORDER BY a.changetime, a.id
          ";
    }

    /**
     * Create rewrite rules for blog articles
     * Used in multiple locations
     *
     * @param null $offset
     * @param null $limit
     */
    public function sCreateRewriteTableBlog($offset = null, $limit = null)
    {
        $query = $this->modelManager->getRepository('Shopware\Models\Category\Category')
            ->getBlogCategoriesByParentQuery(Shopware()->Shop()->get('parentID'));
        $blogCategories = $query->getArrayResult();

        //get all blog category ids
        $blogCategoryIds = array();
        foreach ($blogCategories as $blogCategory) {
            $blogCategoryIds[] = $blogCategory["id"];
        }

        /** @var $repository \Shopware\Models\Blog\Repository */
        $blogArticlesQuery = $this->modelManager->getRepository('Shopware\Models\Blog\Blog')
            ->getListQuery($blogCategoryIds, $offset, $limit);
        $blogArticlesQuery->setHydrationMode(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
        $blogArticles = $blogArticlesQuery->getArrayResult();

        $routerBlogTemplate = $this->config->get('routerBlogTemplate');
        foreach ($blogArticles as $blogArticle) {
            $this->data->assign('blogArticle', $blogArticle);
            $path = $this->template->fetch('string:' . $routerBlogTemplate, $this->data);
            $path = $this->sCleanupPath($path, false);

            $org_path = 'sViewport=blog&sAction=detail&sCategory=' . $blogArticle['categoryId'] . '&blogArticle=' . $blogArticle['id'];
            $this->sInsertUrl($org_path, $path);
        }
    }

    /**
     * Create emotion rewrite rules
     * Used in multiple locations
     *
     * @param null $offset
     * @param null $limit
     */
    public function sCreateRewriteTableSuppliers($offset = null, $limit = null)
    {
        $seoSupplier = $this->config->get('sSEOSUPPLIER');
        if (empty($seoSupplier)) {
            return;
        }

        $suppliers = $this->modelManager->getRepository('Shopware\Models\Article\Supplier')
            ->getFriendlyUrlSuppliersQuery($offset, $limit)->getArrayResult();

        $seoSupplierRouteTemplate = $this->config->get('seoSupplierRouteTemplate');
        foreach ($suppliers as $supplier) {
            $this->data->assign('sSupplier', $supplier);
            $path = $this->template->fetch('string:' . $seoSupplierRouteTemplate, $this->data);
            $path = $this->sCleanupPath($path, false);

            $org_path = 'sViewport=listing&sAction=manufacturer&sSupplier=' . (int)$supplier['id'];
            $this->sInsertUrl($org_path, $path);
        }
    }

    /**
     * Create emotion rewrite rules
     *
     * @param null $offset
     * @param null $limit
     */
    public function sCreateRewriteTableCampaigns($offset = null, $limit = null)
    {
        /**@var $repo \Shopware\Models\Emotion\Repository */
        $repo = $this->modelManager->getRepository('Shopware\Models\Emotion\Emotion');
        $queryBuilder = $repo->getListQueryBuilder();

        $languageId = Shopware()->Shop()->getId();
        $fallbackId = null;

        $fallbackShop = Shopware()->Shop()->getFallback();

        if (!empty($fallbackShop)) {
            $fallbackId = $fallbackShop->getId();
        }

        $translator = new Shopware_Components_Translation();

        $queryBuilder
            ->andWhere('emotions.isLandingPage = 1')
            ->andWhere('emotions.parentId IS NULL')
            ->andWhere('emotions.active = 1');

        if ($limit !== null && $offset !== null) {
            $queryBuilder->setFirstResult($offset)->setMaxResults($limit);
        }

        $campaigns = $queryBuilder->getQuery()->getArrayResult();

        $routerCampaignTemplate = $this->config->get('routerCampaignTemplate');

        foreach ($campaigns as $campaign) {
            $translation = $translator->readWithFallback($languageId, $fallbackId, 'emotion', $campaign['id']);

            $campaign = array_merge($campaign, $translation);

            $this->data->assign('campaign', $campaign);

            $path = $this->template->fetch('string:' . $routerCampaignTemplate, $this->data);
            $path = $this->sCleanupPath($path, false);

            $org_path = 'sViewport=campaign&emotionId=' . $campaign['id'];
            $this->sInsertUrl($org_path, $path);
        }
    }

    /**
     * Create CMS rewrite rules
     * Used in multiple locations
     *
     * @param int $offset
     * @param int $limit
     */
    public function sCreateRewriteTableContent($offset = null, $limit = null)
    {
        //form urls
        $this->insertFormUrls($offset, $limit);

        //static pages urls
        $this->insertStaticPageUrls($offset, $limit);
    }

    /**
     * Updates / create a single rewrite URL
     *
     * @param $org_path
     * @param $path
     * @return false|null False on empty args, null otherwise
     */
    public function sInsertUrl($org_path, $path)
    {
        $path = trim($path);
        $path = ltrim($path, '/');
        if (empty($path) || empty($org_path)) {
            return false;
        }

        $update = $this->getPreparedUpdate();
        $update->execute(array(
            $org_path,
            $path,
            Shopware()->Shop()->getId()
        ));

        $insert = $this->getPreparedInsert();
        $insert->execute(array(
            $org_path,
            $path,
            Shopware()->Shop()->getId()
        ));
    }

    /**
     * Returns the supplier name
     * Used internally as a Smarty extension
     *
     * @param array $params
     * @return string|null
     */
    public function createSupplierPath($params)
    {
        $parts = array();
        if (!empty($params['supplierID'])) {
            $parts[] = $this->modelManager->getRepository('Shopware\Models\Article\Supplier')
                ->find($params['supplierID'])->getName();
        }
        if (empty($params['separator'])) {
            $params['separator'] = '/';
        }
        foreach ($parts as &$part) {
            $part = str_replace($params['separator'], '', $part);
        }
        return implode($params['separator'], $parts);
    }

    /**
     * Returns the category path based on the given params
     * Used internally as a Smarty extension
     *
     * @param array $params
     * @return null|string Category path
     */
    public function sSmartyCategoryPath($params)
    {
        if (!empty($params['articleID'])) {
            $parts = $this->sCategoryPathByArticleId(
                $params['articleID'],
                isset($params['categoryID']) ? $params["categoryID"] : null
            );
        } elseif (!empty($params['categoryID'])) {
            $parts = $this->sCategoryPath($params['categoryID']);
        }
        if (empty($params['separator'])) {
            $params['separator'] = '/';
        }
        foreach ($parts as &$part) {
            $part = str_replace($params['separator'], '', $part);
        }
        $parts = implode($params['separator'], $parts);
        return $parts;
    }

    /**
     * Given a category id, returns the category path
     *
     * @param int $categoryId Id of the category
     * @return array Array containing the path parts
     */
    public function sCategoryPath($categoryId)
    {
        $parts = $this->modelManager->getRepository('Shopware\Models\Category\Category')
            ->getPathById($categoryId, 'name');
        $level = Shopware()->Shop()->getCategory()->getLevel();
        $parts = array_slice($parts, $level);

        return $parts;
    }

    /**
     * Returns the category path to which the
     * article belongs, inside the category subtree.
     * Used internally in sSmartyCategoryPath
     *
     * @param int $articleId Id of the article to look for
     * @param int $parentId Category subtree root id. If null, the shop category is used.
     * @return null|array Category path, or null if no category found
     */
    private function sCategoryPathByArticleId($articleId, $parentId = null)
    {
        $categoryId = $this->moduleManager->Categories()->sGetCategoryIdByArticleId(
            $articleId,
            $parentId
        );

        return empty($categoryId) ? null : $this->sCategoryPath($categoryId);
    }

    /**
     * @return Smarty_Data
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Generates and inserts the form seo urls
     *
     * @param $offset
     * @param $limit
     */
    private function insertFormUrls($offset, $limit)
    {
        $formListData = $this->modelManager->getRepository('Shopware\Models\Form\Form')
            ->getListQuery(array(), array(), $offset, $limit)->getArrayResult();

        foreach ($formListData as $form) {
            $org_path = 'sViewport=ticket&sFid=' . $form['id'];
            $this->data->assign('form', $form);
            $path = $this->template->fetch('string:' . $this->config->get('seoFormRouteTemplate'), $this->data);
            $path = $this->sCleanupPath($path, false);
            $this->sInsertUrl($org_path, $path);
        }
    }

    /**
     * Generates and inserts static page urls
     *
     * @param $offset
     * @param $limit
     */
    private function insertStaticPageUrls($offset, $limit)
    {
        $shopId = Shopware()->Shop()->getId();

        $sitesData = $this->modelManager->getRepository('Shopware\Models\Site\Site')
            ->getSitesWithoutLinkQuery($shopId, $offset, $limit)
            ->getArrayResult();

        foreach ($sitesData as $site) {
            $org_path = 'sViewport=custom&sCustom=' . $site['id'];
            $this->data->assign('site', $site);
            $path = $this->template->fetch('string:' . $this->config->get('seoCustomSiteRouteTemplate'), $this->data);
            $path = $this->sCleanupPath($path, false);
            $this->sInsertUrl($org_path, $path);
        }
    }

    /**
     * Maps the translation of the objectdata from the s_core_translations in the article array
     * @param array $articles
     * @return mixed
     */
    public function mapArticleTranslationObjectData($articles)
    {
        foreach ($articles as &$article) {
            if (empty($article['objectdata']) && empty($article['objectdataFallback'])) {
                unset($article['objectdata'], $article['objectdataFallback']);
                continue;
            }

            $objectData = unserialize($article['objectdata']);
            $objectDataFallback = unserialize($article['objectdataFallback']);

            if (empty($objectData)) {
                $objectData = [];
            }

            if (empty($objectDataFallback)) {
                $objectDataFallback = [];
            }

            if (empty($objectData) && empty($objectDataFallback)) {
                continue;
            }

            unset($article['objectdata'], $article['objectdataFallback']);

            $article = $this->mapArticleObjectFields($article, $objectData, $objectDataFallback, [
                'name' => 'txtArtikel',
                'description_long' => 'txtlangbeschreibung',
                'description' => 'txtshortdescription',
                'keywords' => 'keywords',
            ]);
        }

        return $articles;
    }

    /**
     * map article core translation including fallback fields for given article
     *
     * @param array $article
     * @param array $objectData
     * @param array $objectDataFallback
     * @param array $fieldMappings array(articleFieldName => objectDataFieldName)
     * @return array $article
     */
    private function mapArticleObjectFields(
        array $article,
        array $objectData,
        array $objectDataFallback,
        array $fieldMappings
    ) {
        foreach ($fieldMappings as $articleFieldName => $objectDataFieldName) {
            if (!empty($objectData[$objectDataFieldName])) {
                $article[$articleFieldName] = $objectData[$objectDataFieldName];
                continue;
            }

            if (!empty($objectDataFallback[$objectDataFieldName])) {
                $article[$articleFieldName] = $objectDataFallback[$objectDataFieldName];
            }
        }
        return $article;
    }
}
