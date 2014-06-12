<?php

namespace Shopware\Components\Theme\LessCompiler;

use Shopware\Components\Theme\LessCompiler;
use Shopware\Models\Theme\Settings;

class Oyejorge implements LessCompiler
{
    /**
     * @var \Less_Parser
     */
    private $compiler;

    /**
     * @param \Less_Parser $compiler
     */
    function __construct(\Less_Parser $compiler)
    {
        $this->compiler = $compiler;
    }

    /**
     * Allows to set different configurations for the less compiler,
     * like the compress mode or css source maps.
     *
     * @param array $configuration
     * @return void
     */
    public function setConfiguration(array $configuration)
    {
        $this->compiler->SetOptions($configuration);
    }

    /**
     * Allows to define import directories for the less compiler.
     *
     * @param array $directories
     */
    public function setImportDirectories(array $directories)
    {
        $this->compiler->SetImportDirs($directories);
    }

    /**
     * Allows to set variables which can be used
     * in the compiled less files.
     *
     * @param array $variables
     */
    public function setVariables(array $variables)
    {
        $this->compiler->ModifyVars($variables);
    }

    /**
     * @param string $file File which should be compiled.
     * @param string $url Url which is used for css urls
     * @return void
     */
    public function compile($file, $url)
    {
        $this->compiler->parseFile($file, $url);
    }

    /**
     * Returns all compiled less content.
     *
     * @return string
     */
    public function get()
    {
        return $this->compiler->getCss();
    }

    /**
     * Resets all configurations.
     *
     * @return void
     */
    public function reset()
    {
        $this->compiler->Reset();
    }


}