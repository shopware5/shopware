<?php

namespace Shopware\Components\Theme;

use Shopware\Models\Theme\Settings;

interface LessCompiler
{
    /**
     * Resets all configurations.
     *
     * @return void
     */
    public function reset();

    /**
     * Allows to set different configurations for the less compiler,
     * like the compress mode or css source maps.
     *
     * @param array $configuration
     * @return void
     */
    public function setConfiguration(array $configuration);

    /**
     * Allows to define import directories for the less compiler.
     *
     * @param array $directories
     */
    public function setImportDirectories(array $directories);

    /**
     * Allows to set variables which can be used
     * in the compiled less files.
     *
     * @param array $variables
     */
    public function setVariables(array $variables);

    /**
     * @param string $file File which should be compiled.
     * @param string $url Url which is used for css urls
     * @return void
     */
    public function compile($file, $url);

    /**
     * Returns all compiled less content.
     *
     * @return string
     */
    public function get();
}