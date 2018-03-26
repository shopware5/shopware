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

namespace  Shopware\Tests\Mink\Page;

use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Shopware\Tests\Mink\Element\NotePosition;
use Shopware\Tests\Mink\Helper;

class Note extends Page
{
    /**
     * @var string
     */
    protected $path = '/note';

    /**
     * Verify if we're on an expected page. Throw an exception if not.
     *
     * @throws \Exception
     */
    public function verifyPage()
    {
        $info = Helper::getPageInfo($this->getSession(), ['controller']);

        if ($info['controller'] === 'note') {
            return;
        }

        $message = ['You are not on the note!', 'Current URL: ' . $this->getSession()->getCurrentUrl()];
        Helper::throwException($message);
    }

    /**
     * @param array $items
     */
    public function fillNoteWithProducts(array $items)
    {
        $originalPath = $this->path;

        foreach ($items as $item) {
            $this->path = sprintf('/note/add/ordernumber/%s', $item['number']);
            $this->open();
        }

        $this->path = $originalPath;
    }

    /**
     * @param NotePosition $notePositions
     * @param array        $items
     */
    public function checkNoteProducts(NotePosition $notePositions, array $items)
    {
        foreach ($items as &$item) {
            if (array_key_exists('description', $item)) {
                unset($item['description']);
            }
        }

        Helper::assertElementCount($notePositions, count($items));
        $result = Helper::searchElements($items, $notePositions);

        if ($result !== true) {
            $messages = ['The following articles were not found:'];
            foreach ($result as $product) {
                $messages[] = $product['number'] . ' - ' . $product['name'];
            }
            Helper::throwException($messages);
        }
    }
}
