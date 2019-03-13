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

interface LessCompiler
{
    /**
     * Resets all configurations.
     */
    public function reset();

    /**
     * Allows to set different configurations for the less compiler,
     * like the compress mode or css source maps.
     */
    public function setConfiguration(array $configuration);

    /**
     * Allows to define import directories for the less compiler.
     */
    public function setImportDirectories(array $directories);

    /**
     * Allows to set variables which can be used
     * in the compiled less files.
     */
    public function setVariables(array $variables);

    /**
     * @param string $file file which should be compiled
     * @param string $url  Url which is used for css urls
     */
    public function compile($file, $url);

    /**
     * Returns all compiled less content.
     *
     * @return string
     */
    public function get();
}
