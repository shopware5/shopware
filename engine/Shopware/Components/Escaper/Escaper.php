<?php

declare(strict_types=1);
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

namespace Shopware\Components\Escaper;

use Laminas\Escaper\Escaper as LaminasEscaper;

/**
 * @see https://github.com/laminas/laminas-escaper/blob/2.12.x/src/Escaper.php
 */
class Escaper implements EscaperInterface
{
    private LaminasEscaper $escaper;

    public function __construct(LaminasEscaper $escaper)
    {
        $this->escaper = $escaper;
    }

    /**
     * {@inheritdoc}
     */
    public function escapeHtml($string)
    {
        return $this->escaper->escapeHtml((string) $string);
    }

    /**
     * {@inheritdoc}
     */
    public function escapeHtmlAttr($string)
    {
        return $this->escaper->escapeHtmlAttr((string) $string);
    }

    /**
     * {@inheritdoc}
     */
    public function escapeJs($string)
    {
        return $this->escaper->escapeJs((string) $string);
    }

    /**
     * {@inheritdoc}
     */
    public function escapeCss($string)
    {
        return $this->escaper->escapeCss((string) $string);
    }

    /**
     * {@inheritdoc}
     */
    public function escapeUrl($string)
    {
        return $this->escaper->escapeUrl((string) $string);
    }
}
