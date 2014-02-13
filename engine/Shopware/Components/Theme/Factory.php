<?php

namespace Shopware\Components\Theme;

use Shopware\Models\Shop\Template;

class Factory
{
    /**
     * @var Manager
     */
    private $themeManager;

    private $phpSource = <<<'EOD'
<?php

namespace Shopware\Themes\$$NAME$$;

class Theme extends \Shopware\Theme
{
    protected $extend = $$PARENT$$;

    protected $name = '$$NAME$$';

    protected $description = null;

    protected $author = null;

    protected $license = null;

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

    public function generateTheme($name, Template $parent = null)
    {
        if (!is_writable($this->themeManager->getDefaultThemeDirectory())) {
            throw new \Exception(
                "Theme directory isn't writable"
            );
        }

        //ensure that the first character is upper case.
        //required for the directory structure and php namespace
        $name = ucfirst($name);

        $this->createThemeDirectory($name);

        $this->generateThemePhp($name, $parent);

        $this->generateStructure(
            $this->structure,
            $this->getThemeDirectory($name)
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
     * @param $name
     * @param Template $parent
     */
    private function generateThemePhp($name, Template $parent = null)
    {
        $source = str_replace('$$NAME$$', $name, $this->phpSource);

        if ($parent instanceof Template) {
            $source = str_replace('$$PARENT$$', "'" . $parent->getTemplate(). "'", $source);
        } else {
            $source = str_replace('$$PARENT$$', 'null', $source);
        }

        file_put_contents(
            $this->getThemeDirectory($name) . DIRECTORY_SEPARATOR . 'Theme.php',
            $source
        );
    }

    /**
     * Generates the whole theme structure tree.
     *
     * @param $directory
     * @param $baseDir
     */
    private function generateStructure($directory, $baseDir)
    {
        foreach($directory as $key => $value) {
            if (is_array($value)) {
                mkdir($baseDir . DIRECTORY_SEPARATOR . $key);

                $this->generateStructure($value, $baseDir . DIRECTORY_SEPARATOR . $key);
            } else {
                //switch between create file or create directory
                if (strpos($value, '.') !== false) {
                    file_put_contents($baseDir . DIRECTORY_SEPARATOR. $value, '');
                } else {
                    mkdir($baseDir . DIRECTORY_SEPARATOR . $value);
                }
            }
        }
    }
}
