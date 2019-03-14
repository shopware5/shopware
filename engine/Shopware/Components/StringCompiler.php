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

/**
 * Shopware TemplateMail Component
 */
class Shopware_Components_StringCompiler
{
    /**
     * @var \Enlight_Template_Manager
     */
    protected $view;

    /**
     * Whether or not support for old syntax "{varName}" is enabled.
     * New Syntax is "{$varName}" (Smarty)
     *
     * @var bool
     */
    protected $isCompatibilityMode = true;

    /**
     * @var array
     */
    protected $context = [];

    /**
     * @param array $context
     */
    public function __construct(\Enlight_Template_Manager $view, $context = null)
    {
        $this->setView($view);

        if ($context !== null) {
            $this->setContext($context);
        }
    }

    /**
     * @param array $context
     *
     * @return Shopware_Components_StringCompiler
     */
    public function setContext($context)
    {
        $this->context = $context;

        return $this;
    }

    /**
     * @return array
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Return View Object
     *
     * @return \Enlight_Template_Manager
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * @param Enlight_Template_Manager $view
     *
     * @return Shopware_Components_StringCompiler
     */
    public function setView(\Enlight_Template_Manager $view)
    {
        $this->view = $view;

        return $this;
    }

    /**
     * @param bool $isCompatibilityMode
     *
     * @return Shopware_Components_StringCompiler
     */
    public function setIsCompatibilityMode($isCompatibilityMode = true)
    {
        $this->isCompatibilityMode = $isCompatibilityMode;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsCompatibilityMode()
    {
        return $this->isCompatibilityMode;
    }

    /**
     * Convenience method
     *
     * Abstracts optional $context and compatibilityMode
     *
     * @param string     $value
     * @param array|null $context
     *
     * @return string
     */
    public function compileString($value, $context = null)
    {
        if (strlen($value) == 0) {
            return $value;
        }

        if ($context === null) {
            $context = $this->getContext();
        }

        // First replace legacy vars ({sSomething})
        if ($this->isCompatibilityMode) {
            $value = $this->compileCompatibilityMode($value, $context);
        }

        $value = $this->compileSmartyString($value, $context);

        return $value;
    }

    /**
     * @param string $value
     * @param array  $context
     *
     * @throws Enlight_Exception
     *
     * @return string
     */
    public function compileSmartyString($value, $context)
    {
        $templateEngine = $this->getView();

        try {
            $template = $templateEngine->createTemplate('string:' . $value);
            $template->assign($context);
            $template = $template->fetch();
        } catch (SmartyCompilerException $e) {
            $errorMessage = $e->getMessage();

            if (stripos($errorMessage, 'Syntax Error in template') === 0) {
                // Strip away filepath which is a md5sum
                $errorMessage = sprintf('Syntax Error %s', substr($errorMessage, 69));
            }

            throw new \Enlight_Exception($errorMessage, 0, $e);
        }

        return $template;
    }

    /**
     * @param string $value
     * @param array  $context
     *
     * @return string
     */
    public function compileCompatibilityMode($value, $context)
    {
        foreach ($context as $key => $replacement) {
            if (!is_string($replacement)) {
                continue;
            }
            $value = str_replace('{' . $key . '}', '{$' . $key . '}', $value);
        }

        return $value;
    }
}
