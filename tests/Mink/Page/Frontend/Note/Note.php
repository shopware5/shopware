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

namespace Shopware\Tests\Mink\Page\Frontend\Note;

use Exception;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Shopware\Tests\Mink\Page\Frontend\Note\Elements\NotePosition;
use Shopware\Tests\Mink\Tests\General\Helpers\Helper;

class Note extends Page
{
    /**
     * @var string
     */
    protected $path = '/note';

    /**
     * Verify if we're on an expected page. Throw an exception if not.
     *
     * @throws Exception
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

    public function fillNoteWithProducts(array $items)
    {
        $originalPath = $this->path;

        foreach ($items as $item) {
            $this->path = sprintf('/note/add/ordernumber/%s', $item['number']);
            $this->open();
        }

        $this->path = $originalPath;
    }

    public function checkNoteProducts(NotePosition $notePositions, array $items)
    {
        foreach ($items as &$item) {
            if (\array_key_exists('description', $item)) {
                unset($item['description']);
            }
        }
        unset($item);

        Helper::assertElementCount($notePositions, \count($items));
        $result = Helper::searchElements($items, $notePositions);

        if ($result !== true) {
            $messages = ['The following articles were not found:'];
            foreach ($result as $product) {
                $messages[] = $product['number'] . ' - ' . $product['name'];
            }
            Helper::throwException($messages);
        }
    }

    /**
     * Its ok to be on the note index page, we're being redirected here.
     * {@inheritdoc}
     */
    protected function verifyUrl(array $urlParameters = [])
    {
        if (strpos($this->getDriver()->getCurrentUrl(), '/note') !== false) {
            return;
        }

        parent::verifyUrl($urlParameters);
    }
}
