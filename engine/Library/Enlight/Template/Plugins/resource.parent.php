<?php
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

class Smarty_Resource_Parent extends Smarty_Internal_Resource_File
{
    protected $index;

    /**
     * build template filepath by traversing the template_dir array
     *
     * @param Smarty_Template_Source   $source    source object
     * @param Smarty_Internal_Template $_template template object
     * @return string fully qualified filepath
     * @throws SmartyException if default template handler is registered but not callable
     */
    protected function buildFilepath(Smarty_Template_Source $source, Smarty_Internal_Template $_template = null)
    {
        $this->index++;
        $file = $source->name;
        $hit = false;

        foreach ($source->smarty->getTemplateDir() as $_directory) {
            $_filePath = Enlight_Loader::realpath($_directory . $file);
            if ($this->fileExists($source, $_filePath)) {
                if ($hit) {
                    return $_filePath;
                }
                if ($_template->parent->source->filepath == $_filePath) {
                    $hit = true;
                }
            }
        }

        return parent::buildFilepath($source, $_template);
    }

    /**
     * modify resource_name according to resource handlers specifications
     *
     * @param Smarty $smarty        Smarty instance
     * @param string $resource_name resource_name to make unique
     * @return string unique resource name
     */
    protected function buildUniqueResourceName(Smarty $smarty, $resource_name)
    {
        $resource_name .= $this->index;
        return get_class($this) . '#' . $smarty->joined_template_dir . '#' . $resource_name;
    }

    /**
     * populate Source Object with meta data from Resource
     *
     * @param Smarty_Template_Source   $source    source object
     * @param Smarty_Internal_Template $_template template object
     */
    public function populate(Smarty_Template_Source $source, Smarty_Internal_Template $_template=null)
    {
        $filePath = $this->buildFilepath($source, $_template);
        $s = Smarty_Resource::source(null, $source->smarty, $filePath);

        $source->components = $s;
        $source->filepath = $s->filepath;
        $source->uid = $s->uid;
        if ($_template && $_template->smarty->compile_check) {
            $source->timestamp = $s->timestamp;
            $source->exists = $s->exists;
        }
        $source->template = $_template;
    }

    /**
     * Load template's source from files into current template object
     *
     * @param Smarty_Template_Source $source source object
     * @return string template source
     * @throws SmartyException if source cannot be loaded
     */
    public function getContent(Smarty_Template_Source $source)
    {
        return $source->components->handler->getContent($source->components);
    }
}
