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

namespace Shopware\Models\Theme;

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;

/**
 * @ORM\Table(name="s_core_theme_settings")
 * @ORM\Entity()
 */
class Settings extends ModelEntity
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var bool
     *
     * @ORM\Column(name="compiler_force", type="boolean", nullable=false)
     */
    private $forceCompile = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="compiler_create_source_map", type="boolean", nullable=false)
     */
    private $createSourceMap = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="compiler_compress_css", type="boolean", nullable=false)
     */
    private $compressCss = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="compiler_compress_js", type="boolean", nullable=false)
     */
    private $compressJs = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="force_reload_snippets", type="boolean", nullable=false)
     */
    private $reloadSnippets = false;

    /**
     * @param bool $compressCss
     */
    public function setCompressCss($compressCss)
    {
        $this->compressCss = $compressCss;
    }

    /**
     * @return bool
     */
    public function getCompressCss()
    {
        return $this->compressCss;
    }

    /**
     * @param bool $compressJs
     */
    public function setCompressJs($compressJs)
    {
        $this->compressJs = $compressJs;
    }

    /**
     * @return bool
     */
    public function getCompressJs()
    {
        return $this->compressJs;
    }

    /**
     * @param bool $createSourceMap
     */
    public function setCreateSourceMap($createSourceMap)
    {
        $this->createSourceMap = $createSourceMap;
    }

    /**
     * @return bool
     */
    public function getCreateSourceMap()
    {
        return $this->createSourceMap;
    }

    /**
     * @param bool $forceCompile
     */
    public function setForceCompile($forceCompile)
    {
        $this->forceCompile = $forceCompile;
    }

    /**
     * @return bool
     */
    public function getForceCompile()
    {
        return $this->forceCompile;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param bool $reloadSnippets
     */
    public function setReloadSnippets($reloadSnippets)
    {
        $this->reloadSnippets = $reloadSnippets;
    }

    /**
     * @return bool
     */
    public function getReloadSnippets()
    {
        return $this->reloadSnippets;
    }
}
