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
use Symfony\Component\Filesystem\Filesystem;

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
     * @var PathResolver
     */
    private $pathResolver;

    /**
     * @var Filesystem
     */
    private $fileSystem;

    /**
     * Source template for the Theme.php of a theme
     *
     * @var string
     */
    private $phpSource = <<<'EOD'
<?php

namespace Shopware\Themes\$TEMPLATE$;

class Theme extends \Shopware\Components\Theme
{
    protected $extend = '$PARENT$';

    protected $name = '$NAME$';

    protected $description = '$DESCRIPTION$';

    protected $author = '$AUTHOR$';

    protected $license = '$LICENSE$';

    public function createConfig()
    {
    }
}
EOD;

    /**
     * Directory / File structure which will be
     * generated for the theme.
     *
     * @var array
     */
    private $structure = array(
        '_private' => array(
            'smarty',
            'snippets'
        )
    );

    function __construct(PathResolver $pathResolver, Filesystem $fileSystem)
    {
        $this->pathResolver = $pathResolver;
        $this->fileSystem = $fileSystem;
    }

    /**
     * Function which generates a new shopware theme
     * into the engine/Shopware/Themes directory.
     *
     * @param array $data
     * @param Template $parent
     * @throws \Exception
     */
    public function generateTheme(array $data, Template $parent = null)
    {
        if (!is_writable($this->pathResolver->getDefaultThemeDirectory())) {
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

        $this->movePreviewImage($this->getThemeDirectory($data['template']));
    }

    /**
     * @param $directory
     */
    private function movePreviewImage($directory)
    {
        $this->fileSystem->copy(
            __DIR__ . '/preview.png',
            $directory . '/preview.png'
        );
    }

    /**
     * Generates the theme directory in engine/Shopware/Themes
     * @param $name
     */
    private function createThemeDirectory($name)
    {
        $this->fileSystem->mkdir(
            $this->getThemeDirectory($name)
        );
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
        return $this->pathResolver->getDefaultThemeDirectory() . DIRECTORY_SEPARATOR . $name;
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

        $output = new \SplFileObject(
            $this->getThemeDirectory($data['template']) . DIRECTORY_SEPARATOR . 'Theme.php',
            "w+"
        );
        $output->fwrite($source);
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

                $this->fileSystem->mkdir($baseDir . DIRECTORY_SEPARATOR . $key);

                $this->generateStructure($value, $baseDir . DIRECTORY_SEPARATOR . $key);

            } else {
                //switch between create file or create directory
                if (strpos($value, '.') !== false) {
                    $output = new \SplFileObject(
                        $baseDir . DIRECTORY_SEPARATOR . $value,
                        "w+"
                    );
                    $output->fwrite('');
                } else {
                    $this->fileSystem->mkdir($baseDir . DIRECTORY_SEPARATOR . $value);
                }
            }
        }
    }
}
