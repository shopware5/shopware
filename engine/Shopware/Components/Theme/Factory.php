<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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

namespace Shopware\Components\Theme;

use Shopware\Models\Shop\Template;

/**
 * Class to generate shopware themes.
 *
 * @category  Shopware
 * @package   Shopware
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Factory
{
    /**
     * @var Manager
     */
    private $themeManager;

    private $phpSource = <<<'EOD'
<?php

namespace Shopware\Themes\$TEMPLATE$;

class Theme extends \Shopware\Theme
{
    protected $extend = '$PARENT$';

    protected $name = '$NAME$';

    protected $description = '$DESCRIPTION$';

    protected $author = '$AUTHOR$';

    protected $license = '$LICENSE$';

    public function createConfig()
    {
//        todo implement your theme configuration here.

//        $this->createTextField(array(
//            'name' => 'textField',
//            'fieldLabel' => 'Text input'
//        ));
//
//        $this->createCheckboxField(array(
//            'name' => 'checkboxField',
//            'fieldLabel' => 'Activate'
//        ));
//
//        $this->createArticleSelection(array(
//            'name' => 'articleSelection',
//            'fieldLabel' => 'Select article'
//        ));
//
//        $this->createCategorySelection(array(
//            'name' => 'categorySelection',
//            'fieldLabel' => 'Select category'
//        ));
//
//        $this->createColorPicker(array(
//            'name' => 'colorPicker',
//            'fieldLabel' => 'Select color'
//        ));
//
//        $this->createDateField(array(
//            'name' => 'dateField',
//            'fieldLabel' => 'Date input'
//        ));
//
//        $this->createEmField(array(
//            'name' => 'emField',
//            'fieldLabel' => 'EM input'
//        ));
//
//        $this->createMediaSelection(array(
//            'name' => 'mediaSelection',
//            'fieldLabel' => 'Select media'
//        ));
//
//        $this->createPercentField(array(
//            'name' => 'percentField',
//            'fieldLabel' => 'Percent input'
//        ));
//
//        $this->createTextAreaField(array(
//            'name' => 'textAreaField',
//            'fieldLabel' => 'Text input'
//        ));
    }
}
EOD;

    private $structure = array(
        '_private' => array(
            'smarty',
            'snippets'
        )
    );

    function __construct($themeManager)
    {
        $this->themeManager = $themeManager;
    }

    public function generateTheme(array $data, Template $parent = null)
    {
        if (!is_writable($this->themeManager->getDefaultThemeDirectory())) {
            throw new \Exception(
                "Theme directory isn't writable"
            );
        }
        if (!isset($data['template']) || empty($data['template'])) {
            throw new \Exception(
                "Passed data array contains no valid theme name under the array key 'template'."
            );
        }

        //ensure that the first character is upper case.
        //required for the directory structure and php namespace
        $data['template'] = ucfirst($data['template']);

        $this->createThemeDirectory($data['template']);

        $this->generateThemePhp($data, $parent);

        $this->generateStructure(
            $this->structure,
            $this->getThemeDirectory($data['template'])
        );
    }

    /**
     * Generates the theme directory in engine/Shopware/Themes
     * @param $name
     */
    private function createThemeDirectory($name)
    {
        mkdir($this->getThemeDirectory($name));
    }


    /**
     * Helper function to generate the full theme directory name.
     * example: /var/www/engine/Shopware/Themes/MyTheme
     *
     * @param $name
     * @return string
     */
    private function getThemeDirectory($name)
    {
        return $this->themeManager->getDefaultThemeDirectory() . DIRECTORY_SEPARATOR . $name;
    }

    /**
     * Generates the Theme.php file for the theme.
     *
     * @param array $data
     * @param Template $parent
     */
    private function generateThemePhp(array $data, Template $parent = null)
    {
        $source = str_replace('$TEMPLATE$', $data['template'], $this->phpSource);

        if ($parent instanceof Template) {
            $source = str_replace('$PARENT$', $parent->getTemplate(), $source);
        } else {
            $source = str_replace('$PARENT$', 'null', $source);
        }

        $source = $this->replacePlaceholder('name', $data['name'], $source);
        $source = $this->replacePlaceholder('author', $data['author'], $source);
        $source = $this->replacePlaceholder('license', $data['license'], $source);
        $source = $this->replacePlaceholder('description', $data['description'], $source);

        file_put_contents(
            $this->getThemeDirectory($data['template']) . DIRECTORY_SEPARATOR . 'Theme.php',
            $source
        );
    }

    /**
     * Helper function which replace the passed source placeholder with the passed content.
     * If the content isn't set or is empty, the function replace the placeholder with the optional
     * default value.
     *
     * @param string $placeholder Placeholder name, without surrounding '$'
     * @param string $content Content which should be placed into the source
     * @param string $source Source template where the content should be injected.
     * @param string $default Fallback if the passed content is empty or isn't set
     * @return mixed
     */
    private function replacePlaceholder($placeholder, $content, $source, $default = '')
    {
        $placeholder = strtoupper($placeholder);

        if (isset($content) && !empty($content)) {
            return str_replace('$' . $placeholder . '$', $content, $source);
        } else {
            return str_replace('$' . $placeholder . '$', $default, $source);
        }
    }

    /**
     * Generates the whole theme structure tree.
     *
     * @param $directory
     * @param $baseDir
     */
    private function generateStructure($directory, $baseDir)
    {
        foreach ($directory as $key => $value) {
            if (is_array($value)) {
                mkdir($baseDir . DIRECTORY_SEPARATOR . $key);

                $this->generateStructure($value, $baseDir . DIRECTORY_SEPARATOR . $key);
            } else {
                //switch between create file or create directory
                if (strpos($value, '.') !== false) {
                    file_put_contents($baseDir . DIRECTORY_SEPARATOR . $value, '');
                } else {
                    mkdir($baseDir . DIRECTORY_SEPARATOR . $value);
                }
            }
        }
    }
}
