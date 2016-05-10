<?php
namespace  Shopware\Tests\Mink\Page;

use Shopware\Tests\Mink\Element\NotePosition;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Shopware\Tests\Mink\Helper;

class Note extends Page
{
    /**
     * @var string $path
     */
    protected $path = '/note';

    /**
     * Verify if we're on an expected page. Throw an exception if not.
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
     * @param array $items
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
