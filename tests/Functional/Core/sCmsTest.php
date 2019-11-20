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

class sCmsTest extends Enlight_Components_Test_Controller_TestCase
{
    /**
     * @var sCms
     */
    private $module;

    public function setUp(): void
    {
        $this->Front()->setRequest($this->Request());
        $this->module = Shopware()->Modules()->Cms();
    }

    /**
     * @covers \sCms::sGetStaticPage
     */
    public function testsGetStaticPage()
    {
        // Without argument, returns false
        static::assertFalse($this->module->sGetStaticPage());

        // Non-existent id returns false
        static::assertFalse($this->module->sGetStaticPage(0));

        $pageIds = Shopware()->Db()->fetchCol('SELECT id FROM s_cms_static  LIMIT 10');

        foreach ($pageIds as $pageId) {
            $page = $this->module->sGetStaticPage($pageId);

            static::assertArrayHasKey('id', $page);
            static::assertArrayHasKey('description', $page);
            static::assertArrayHasKey('html', $page);
            static::assertArrayHasKey('grouping', $page);
            static::assertArrayHasKey('position', $page);
            static::assertArrayHasKey('link', $page);
            static::assertArrayHasKey('page_title', $page);
            static::assertArrayHasKey('meta_keywords', $page);
            static::assertArrayHasKey('meta_description', $page);

            if (!empty($page['parentID'])) {
                static::assertArrayHasKey('siblingPages', $page);
                foreach ($page['siblingPages'] as $siblingPage) {
                    static::assertArrayHasKey('id', $siblingPage);
                    static::assertArrayHasKey('description', $siblingPage);
                    static::assertArrayHasKey('link', $siblingPage);
                    static::assertArrayHasKey('target', $siblingPage);
                    static::assertArrayHasKey('active', $siblingPage);
                    static::assertArrayHasKey('page_title', $siblingPage);
                }
                static::assertArrayHasKey('parent', $page);
                if (count($page['parent']) > 0) {
                    static::assertArrayHasKey('id', $page['parent']);
                    static::assertArrayHasKey('description', $page['parent']);
                    static::assertArrayHasKey('link', $page['parent']);
                    static::assertArrayHasKey('target', $page['parent']);
                    static::assertArrayHasKey('page_title', $page['parent']);
                }
            } else {
                static::assertArrayHasKey('subPages', $page);
                foreach ($page['subPages'] as $subPage) {
                    static::assertArrayHasKey('id', $subPage);
                    static::assertArrayHasKey('description', $subPage);
                    static::assertArrayHasKey('link', $subPage);
                    static::assertArrayHasKey('target', $subPage);
                    static::assertArrayHasKey('page_title', $subPage);
                }
            }
        }
    }
}
