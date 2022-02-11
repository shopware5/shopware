<?php

declare(strict_types=1);
/**
 * Enlight
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://enlight.de/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@shopware.de so we can send you a copy immediately.
 *
 * @category   Enlight
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */

/**
 * Enlight resource to compile snippets.
 *
 * The Enlight_Components_Snippet_Resource is a template resource with the ability to process snippets.
 *
 * @category   Enlight
 *
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
class Enlight_Components_Snippet_Resource extends Smarty_Internal_Resource_Extends
{
    /**
     * @var Enlight_Components_Snippet_Manager Snippet manager which has to be set in the constructor
     */
    protected $snippetManager;

    /**
     * @var bool The config options provided in the global config.php file
     */
    protected $showSnippetPlaceholder;

    /**
     * Class constructor, sets snippet manager
     *
     * @param bool $showSnippetPlaceholder
     */
    public function __construct(Enlight_Components_Snippet_Manager $snippetManager, $showSnippetPlaceholder = false)
    {
        $this->snippetManager = $snippetManager;
        $this->showSnippetPlaceholder = $showSnippetPlaceholder;
    }

    /**
     * populate Source Object with meta data from Resource
     *
     * @param Smarty_Template_Source   $source    source object
     * @param Smarty_Internal_Template $_template template object
     *
     * @return void
     */
    public function populate(Smarty_Template_Source $source, Smarty_Internal_Template $_template = null)
    {
        if (!isset($source->smarty->registered_plugins[Smarty::PLUGIN_BLOCK]['snippet'])) {
            $source->smarty->registerPlugin(Smarty::PLUGIN_BLOCK, 'snippet', [__CLASS__, 'compileSnippetBlock']);
        }
        if (!isset($source->smarty->registered_plugins[Smarty::PLUGIN_MODIFIER]['snippet'])) {
            $source->smarty->registerPlugin(Smarty::PLUGIN_MODIFIER, 'snippet', [$this, 'compileSnippetModifier']);
        }
        $default_resource = $source->smarty->default_resource_type;
        $source->smarty->default_resource_type = 'file';
        parent::populate($source, $_template);
        $source->smarty->default_resource_type = $default_resource;
    }

    /**
     * Compiles the given snippet block if the content parameter is filled.
     *
     * @param array<string, mixed> $params
     * @param string|null          $content
     *
     * @return string
     */
    public static function compileSnippetBlock($params, $content, ?Smarty_Internal_TemplateBase $template = null)
    {
        if ($content === null) {
            return '';
        }

        if (!empty($params['tag']) && !empty($params['namespace'])) {
            if (!empty($params['class'])) {
                $params['class'] .= ' ' . str_replace('/', '_', $params['namespace']);
            } else {
                $params['class'] = str_replace('/', '_', $params['namespace']);
            }
        }

        if (!empty($params['tag'])) {
            $params['tag'] = strtolower($params['tag']);

            $attr = '';
            foreach ($params as $key => $param) {
                if (\in_array($key, ['name', 'tag', 'assign', 'name', 'namespace', 'default', 'force'])) {
                    continue;
                }
                $attr .= ' ' . $key . '="' . htmlentities($param, ENT_COMPAT, mb_internal_encoding(), false) . '"';
            }

            $content = htmlentities($content, ENT_COMPAT, mb_internal_encoding(), false);
            $content = "<{$params['tag']}$attr>" . $content . "</{$params['tag']}>";
        }

        if (isset($params['assign'])) {
            if ($template !== null) {
                $template->assign($params['assign'], $content);
            }

            return '';
        }

        return $content;
    }

    /**
     * Compiles the snippet modifier
     *
     * @param string                                           $content
     * @param string|null                                      $name
     * @param string|Enlight_Components_Snippet_Namespace|null $namespace
     * @param bool                                             $force
     *
     * @return string
     */
    public function compileSnippetModifier($content, $name = null, $namespace = null, $force = false)
    {
        if (\is_string($namespace)) {
            $namespace = $this->snippetManager->getNamespace($namespace);
        } elseif (!$namespace instanceof Enlight_Config) {
            return $content;
        }

        $name = $name ?? $content;
        $result = $namespace->get($name);

        if ($result === null || $force) {
            $namespace->set($name, $content)->write();
        } else {
            $content = $result;
        }

        return $content;
    }

    /**
     * Load template's source from files into current template object
     *
     * @param Smarty_Template_Source $source source object
     *
     * @throws SmartyException if source cannot be loaded
     *
     * @return string template source
     */
    public function getContent(Smarty_Template_Source $source)
    {
        foreach ($source->components as $_component) {
            $_component->content = $this->getSnippetContent($_component);
        }
        $this->snippetManager->write();

        return parent::getContent($source);
    }

    /**
     * Returns the snippet content of the passed smarty template source instance.
     *
     * @throws SmartyException
     *
     * @return string
     */
    public function getSnippetContent(Smarty_Template_Source $source)
    {
        $_rdl = preg_quote($source->smarty->right_delimiter);
        $_ldl = preg_quote($source->smarty->left_delimiter);

        $_block_namespace = $this->getSnippetNamespace($source);

        $pattern = "!{$_ldl}s(e?)(\s.+?)?{$_rdl}(.*?){$_ldl}/se?{$_rdl}!msi";
        while (preg_match($pattern, $source->content, $_block_match, PREG_OFFSET_CAPTURE)) {
            $_block_editable = !empty($_block_match[1][0]);
            $_block_args = $_block_match[2][0];
            $_block_default = $_block_match[3][0];
            list($_block_tag, $_block_start) = $_block_match[0];
            $_block_length = \strlen($_block_tag);
            if (!preg_match("!(.?)(name=)(.*?)(?=(\s|$))!", $_block_args, $_match) && empty($_block_default)) {
                throw new SmartyException('"' . $_block_tag . '" missing name attribute');
            }
            $_block_force = (bool) preg_match('#[\s]force#', $_block_args);
            $_block_json = (bool) preg_match('#[\s]json=["\']true["\']\W#', $_block_args);
            $_block_name = !empty($_match[3]) ? trim($_match[3], '\'"') : $_block_default;
            if (preg_match("!(.?)(namespace=)(.*?)(?=(\s|$))!", $_block_args, $_match)) {
                $_namespace = trim($_match[3], '\'"');
            } else {
                $_namespace = $_block_namespace;
            }
            $_block_args = str_replace('"', '\'', $_block_args);

            $_block_content = $this->getSnippet($_namespace, $_block_name, $_block_default, $_block_force);

            if ($_block_json) {
                $_block_content = json_encode($_block_content);
            }

            if (!empty($_block_default)) {
                $_block_args .= ' default=' . var_export($_block_default, true);
            }
            if (!empty($_block_namespace)) {
                $_block_args .= ' namespace=' . var_export($_block_namespace, true);
            }
            if (!empty($_block_editable)) {
                $_block_args .= ' tag=\'span\'';
            }
            if (!empty($_block_force)) {
                $_block_args = str_replace('force', 'force=true', $_block_args);
            }

            $_rdl = $source->smarty->right_delimiter;
            $_ldl = $source->smarty->left_delimiter;

            if (empty($_block_content) && !empty($_block_name) && $this->showSnippetPlaceholder) {
                $_block_content = '#' . $_block_name . '#';
            } else {
                $_block_content = "{$_ldl}snippet$_block_args{$_rdl}{$_block_content}{$_ldl}/snippet{$_rdl}";
            }

            $source->content = substr_replace($source->content, $_block_content, $_block_start, $_block_length);
        }

        return $source->content;
    }

    /**
     * Returns the snippet namespace class for the given smarty template source.
     *
     * @throws Enlight_Exception
     *
     * @return string|null
     */
    public function getSnippetNamespace(Smarty_Template_Source $source)
    {
        $_rdl = preg_quote($source->smarty->right_delimiter);
        $_ldl = preg_quote($source->smarty->left_delimiter);

        if (preg_match("!{$_ldl}namespace(\s.+?)?{$_rdl}!msi", $source->content, $_namespace_match)) {
            $source->content = str_replace($_namespace_match[0], '', $source->content);
            if (preg_match("!.?name=(.*?)(?=(\s|$))!", $_namespace_match[1], $_name_match)) {
                $_name_match[1] = trim($_name_match[1], '"\' ');

                return $_name_match[1];
            }

            if (str_contains($_namespace_match[1], 'ignore')) {
                return null;
            }
            throw new Enlight_Exception('Missing name attribute in namespace block');
        }
        $path = Enlight_Loader::realpath($source->filepath);
        $templateDirs = $source->smarty->getTemplateDir();
        if (\is_array($templateDirs)) {
            foreach ($templateDirs as $template_dir) {
                $template_dir = Enlight_Loader::realpath($template_dir);
                if (\is_string($path) && \is_string($template_dir) && str_starts_with($path, $template_dir)) {
                    $namespace = substr($path, \strlen($template_dir));
                    $namespace = strtr($namespace, DIRECTORY_SEPARATOR, '/');
                    $namespace = \dirname($namespace) . '/' . pathinfo($namespace, PATHINFO_FILENAME);

                    return trim($namespace, '/');
                }
            }
        }

        return null;
    }

    /**
     * Returns the snippet content for the given snippet namespace and name.
     * If the force parameter is set to true, the default value will be set and returned.
     *
     * @param string|null $namespace
     * @param string      $name
     * @param string      $default
     * @param bool        $force
     *
     * @return string
     */
    protected function getSnippet($namespace, $name, $default, $force = false)
    {
        $snippet = $this->snippetManager->getNamespace($namespace);
        $content = $snippet->get($name);
        if ($content === null || $force) {
            $snippet->set($name, $default);

            return $default;
        }

        return $content;
    }
}
