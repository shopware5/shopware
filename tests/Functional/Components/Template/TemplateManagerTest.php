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

namespace Shopware\tests\Unit\Components\Template;

use PHPUnit\Framework\TestCase;

/**
 * Tests for the template manager
 */
class TemplateManagerTest extends TestCase
{
    /**
     * Tests whether the directories added to a cloned TemplateManager are recognized as secure dirs by SmartySecurity
     */
    public function testCloningTemplateManagerWithEnabledSmartySecurity()
    {
        // Create a dummy file
        $tempDir = Shopware()->Container()->getParameter('kernel.root_dir') . '/media/temp';
        $tempFile = $tempDir . '/template.tpl';
        file_put_contents($tempFile, 'test');

        /** @var \Enlight_Template_Manager $templateManager */
        $templateManager = clone Shopware()->Container()->get('template');
        $templateManager->addTemplateDir($tempDir);
        $renderingResult = $templateManager->fetch('template.tpl');

        // The actual thing to test here is that there is no SmartyException thrown here
        static::assertEquals($renderingResult, 'test');
    }

    /**
     * Tests where invalid file in extends has occurred SmartySecurity errors
     */
    public function testFetchInvalidExtends()
    {
        // Create a dummy file
        $tempDir = Shopware()->Container()->getParameter('kernel.root_dir') . '/media/temp/frontend/detail2/';

        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        $tempFile = $tempDir . 'index.tpl';
        file_put_contents($tempFile, '{extends file="parent:frontent/detail/index.tpl"}');

        /** @var \Enlight_Template_Manager $templateManager */
        $templateManager = clone Shopware()->Container()->get('template');
        $templateManager->addTemplateDir(Shopware()->Container()->getParameter('kernel.root_dir') . '/media/temp/');

        $this->expectException(\SmartyException::class);
        $this->expectExceptionMessage('Unknown path');

        $templateManager->fetch('frontend/detail2/index.tpl');
    }
}
