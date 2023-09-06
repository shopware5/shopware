<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Components\Theme\LessCompiler;

use Less_Parser;
use Less_Tree_Quoted;
use Shopware\Components\Theme\LessCompiler;

class Oyejorge implements LessCompiler
{
    /**
     * @var Less_Parser
     */
    private $compiler;

    public function __construct(Less_Parser $compiler)
    {
        $this->compiler = $compiler;
    }

    /**
     * Allows to set different configurations for the less compiler,
     * like the compress mode or css source maps.
     */
    public function setConfiguration(array $configuration)
    {
        $this->compiler->SetOptions($configuration);
        $this->compiler->registerFunction('swhash', function (Less_Tree_Quoted $filename) {
            $absPath = $filename->currentFileInfo['currentDirectory'] . $filename->value;
            $shopwareRevision = $this->compiler->findValueOf('shopware-revision');
            $filename->value = md5($shopwareRevision . md5_file($absPath));

            return $filename;
        });
    }

    /**
     * Allows to define import directories for the less compiler.
     */
    public function setImportDirectories(array $directories)
    {
        $this->compiler->SetImportDirs($directories);
    }

    /**
     * Allows to set variables which can be used
     * in the compiled less files.
     */
    public function setVariables(array $variables)
    {
        $this->compiler->ModifyVars($variables);
    }

    /**
     * @param string $file file which should be compiled
     * @param string $url  Url which is used for css urls
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
     */
    public function reset()
    {
        $this->compiler->Reset();
    }
}
