<?php
/**
 * Shopware 4
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
 * @category  Shopware
 * @copyright Copyright (c) shopware AG (http://www.shopware.com)
 */
class sRewriteTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var sRewriteTable
     */
    private $rewriteTable;

    public function setUp()
    {
        $this->rewriteTable = Shopware()->Modules()->RewriteTable();
    }

    /**
     * * @dataProvider provider
     */
    public function testRewriteString($string, $result)
    {
        $this->assertEquals($result, $this->rewriteTable->sCleanupPath($string));
    }

    public function provider()
    {
        return array(
            array(' a  b ', 'a-b'),
            array('hello', 'hello'),
            array('Hello', 'Hello'),
            array('Hello World', 'Hello-World'),
            array('Hello-World', 'Hello-World'),
            array('Hello:World', 'Hello-World'),
            array('Hello,World', 'Hello-World'),
            array('Hello;World', 'HelloWorld'),
            array('Hello&World', 'HelloWorld'),
            array('Hello & World', 'Hello-und-World'),
            array('Nguyễn Đăng Khoa', 'Nguyen-Dang-Khoa'),
            array('Ä ä Ö ö Ü ü ß', 'AE-ae-OE-oe-UE-ue-ss'),
            array('Á À á à É È é è Ó Ò ó ò Ñ ñ Ú Ù ú ù', 'A-A-a-a-E-E-e-e-O-O-o-o-N-n-U-U-u-u'),
            array('Â â Ê ê Ô ô Û û', 'A-a-E-e-O-o-U-u'),
            array('Â â Ê ê Ô ô Û 1', 'A-a-E-e-O-o-U-1'),
            array('Привет мир', 'Privet-mir'),
            array('Привіт світ', 'Privit-svit'),
            array('H+e#l1l--o/W§o r.l:d)', 'H-el1l-o-Wo-r.l-d'),
            array(': World', 'World'),
            array('Hello World!', 'Hello-World'),
           array('°¹²³@', '0123at'),
           array('Mórë thån wørds', 'More-than-words'),
           array('Блоґ їжачка', 'Blog-jizhachka'),
           array('фильм', 'film'),
           array('драма', 'drama'),
           array('ελληνικά', 'ellenika'),
           array('C’est du français !', 'Cest-du-francais'),
           array('Één jaar', 'Een-jaar'),
           array('tiếng việt rất khó', 'tieng-viet-rat-kho'),
           array('Nguyễn Đăng Khoa', 'Nguyen-Dang-Khoa')
        );
    }
}
