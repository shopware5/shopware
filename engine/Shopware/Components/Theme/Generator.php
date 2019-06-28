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

namespace Shopware\Components\Theme;

use Shopware\Models\Shop\Template;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class to generate shopware themes.
 */
class Generator
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
     * @var \Enlight_Event_EventManager
     */
    private $eventManager;

    /**
     * Source template for the Theme.php of a theme
     *
     * @var string
     */
    private $phpSource = <<<'EOD'
<?php

namespace Shopware\Themes\$TEMPLATE$;

use Shopware\Components\Form as Form;

class Theme extends \Shopware\Components\Theme
{
    protected $extend = '$PARENT$';

    protected $name = <<<'SHOPWARE_EOD'
$NAME$
SHOPWARE_EOD;

    protected $description = <<<'SHOPWARE_EOD'
$DESCRIPTION$
SHOPWARE_EOD;

    protected $author = <<<'SHOPWARE_EOD'
$AUTHOR$
SHOPWARE_EOD;

    protected $license = <<<'SHOPWARE_EOD'
$LICENSE$
SHOPWARE_EOD;

    public function createConfig(Form\Container\TabContainer $container)
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
    private $structure = [
        '_private' => [
            'smarty',
            'snippets',
        ],
        'documents',
        'frontend' => [
            '_public' => [
                'src' => [
                    'css',
                    'fonts',
                    'img' => [
                        'icons',
                        'logos',
                    ],
                    'js' => [
                        'vendors',
                    ],
                    'less' => [
                        '_components',
                        '_mixins',
                        '_modules',
                        '_variables',
                    ],
                ],
            ],
            '_includes',
            'account',
            'address',
            'blog' => [
                'comment',
            ],
            'campaign',
            'checkout' => [
                'items',
            ],
            'compare',
            'custom',
            'detail' => [
                'comment',
                'tabs',
            ],
            'error',
            'forms',
            'home',
            'index',
            'listing' => [
                'actions',
                'filter',
                'product-box',
            ],
            'newsletter',
            'note',
            'paypal',
            'plugins' => [
                'compare',
                'index',
                'notification',
                'payment',
                'seo',
            ],
            'register',
            'robots_txt',
            'search',
            'sitemap',
            'sitemap_xml',
            'tellafriend',
        ],
        'newsletter' => [
            'alt',
            'container',
            'index',
        ],
        'widgets' => [
            'checkout',
            'compare',
            'emotion' => [
                'components',
            ],
            'index',
            'listing',
            'recommendation',
        ],
    ];

    public function __construct(PathResolver $pathResolver, Filesystem $fileSystem, \Enlight_Event_EventManager $eventManager)
    {
        $this->pathResolver = $pathResolver;
        $this->fileSystem = $fileSystem;
        $this->eventManager = $eventManager;
    }

    /**
     * Function which generates a new shopware theme
     * into the engine/Shopware/Themes directory.
     *
     * @param Template $parent
     *
     * @throws \Exception
     */
    public function generateTheme(array $data, Template $parent = null)
    {
        if (!is_writable($this->pathResolver->getFrontendThemeDirectory())) {
            throw new \Exception(
                sprintf('Theme directory %s isn\'t writable', $this->pathResolver->getFrontendThemeDirectory())
            );
        }
        if (!isset($data['template']) || empty($data['template'])) {
            throw new \Exception(
                'Passed data array contains no valid theme name under the array key "template".'
            );
        }

        // Ensure that the first character is upper case.
        // Required for the directory structure and php namespace
        $data['template'] = ucfirst($data['template']);

        $this->createThemeDirectory($data['template']);

        $this->generateThemePhp($data, $parent);

        $directory = $this->getThemeDirectory($data['template']);
        $this->generateStructure(
            $this->structure,
            $directory
        );

        $this->eventManager->notify('Theme_Generator_Structure_Generated', [
            'data' => $data,
            'directory' => $directory,
        ]);

        $this->movePreviewImage(
            $this->getThemeDirectory($data['template'])
        );
    }

    /**
     * @param string $directory
     */
    private function movePreviewImage($directory)
    {
        $this->fileSystem->copy(
            __DIR__ . '/preview.png',
            $directory . '/preview.png'
        );

        $this->eventManager->notify('Theme_Generator_Preview_Image_Created', [
            'directory' => $directory,
        ]);
    }

    /**
     * Generates the theme directory in engine/Shopware/Themes
     *
     * @param string $name
     */
    private function createThemeDirectory($name)
    {
        $directory = $this->getThemeDirectory($name);
        $this->fileSystem->mkdir($directory);

        $this->eventManager->notify('Theme_Generator_Theme_Directory_Created', [
            'name' => $name,
            'directory' => $directory,
        ]);
    }

    /**
     * Helper function to generate the full theme directory name.
     * example: /var/www/engine/Shopware/Themes/MyTheme
     *
     * @param string $name
     *
     * @return string
     */
    private function getThemeDirectory($name)
    {
        return $this->pathResolver->getFrontendThemeDirectory() . DIRECTORY_SEPARATOR . $name;
    }

    /**
     * Generates the Theme.php file for the theme.
     *
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
            'w+'
        );

        $source = $this->eventManager->filter('Theme_Generator_Theme_Source_Generated',
            $source,
            ['data' => $data, 'parent' => $parent]
        );

        $output->fwrite($source);
    }

    /**
     * Helper function which replace the passed source placeholder with the passed content.
     * If the content isn't set or is empty, the function replace the placeholder with the optional
     * default value.
     *
     * @param string $placeholder Placeholder name, without surrounding '$'
     * @param string $content     Content which should be placed into the source
     * @param string $source      source template where the content should be injected
     * @param string $default     Fallback if the passed content is empty or isn't set
     */
    private function replacePlaceholder($placeholder, $content, $source, $default = '')
    {
        $placeholder = strtoupper($placeholder);

        if (empty($content)) {
            $content = $default;
        }

        $content = addslashes($content);
        $content = str_replace('SHOPWARE_EOD', '', $content);

        return str_replace('$' . $placeholder . '$', $content, $source);
    }

    /**
     * Generates the whole theme structure tree.
     *
     * @param string[]|array<string[]> $directory
     * @param string                   $baseDir
     */
    private function generateStructure($directory, $baseDir)
    {
        foreach ($directory as $key => $value) {
            if (is_array($value)) {
                $this->fileSystem->mkdir($baseDir . DIRECTORY_SEPARATOR . $key);

                $this->generateStructure($value, $baseDir . DIRECTORY_SEPARATOR . $key);
            } else {
                // Switch between create file or create directory
                if (strpos($value, '.') !== false) {
                    $output = new \SplFileObject(
                        $baseDir . DIRECTORY_SEPARATOR . $value,
                        'w+'
                    );
                    $output->fwrite('');
                } else {
                    $this->fileSystem->mkdir($baseDir . DIRECTORY_SEPARATOR . $value);
                }
            }
        }
    }
}
