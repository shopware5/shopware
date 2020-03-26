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

namespace Shopware\Components\Template;

class HtmlMinCompressor implements HtmlCompressorInterface
{
    public function minify(string $content): string
    {
        // List of untouchable HTML-tags.
        $unchanged = 'script|pre|textarea';
        // It is assumed that this placeholder could not appear organically in your
        // output. If it can, you may have an XSS problem.
        $placeholder = "@@<'-pLaChLdR-'>@@";

        // Some helper variables.
        $unchangedBlocks  = [];
        $unchangedRegex   = "!<($unchanged)[^>]*?>.*?</\\1>!is";
        $placeholderRegex = "!$placeholder!";

        // Replace all the tags (including their content) with a placeholder, and keep their contents for later.
        $content = preg_replace_callback(
            $unchangedRegex,
            function ($match) use (&$unchangedBlocks, $placeholder) {
                array_push($unchangedBlocks, $match[0]);
                return $placeholder;
            },
            $content
        );

        // Remove whitespace (spaces, newlines and tabs) at the beginning of a line and complete empty lines
        $content = trim(preg_replace('!^[ \t]*\r?\n|^[ \t]+!m', '', $content));

        // Replace the placeholders with the original content.
        $content = preg_replace_callback(
            $placeholderRegex,
            function ($match) use (&$unchangedBlocks) {
                // I am a paranoid.
                if (count($unchangedBlocks) == 0) {
                    throw new \RuntimeException("Found too many placeholders in input string");
                }
                return array_shift($unchangedBlocks);
            },
            $content
        );

        return $content;
    }
}
