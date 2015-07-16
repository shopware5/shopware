<?php
namespace  Shopware\Tests\Mink\Page\Emotion;

use Shopware\Tests\Mink\Element\Emotion\NotePosition;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Shopware\Tests\Mink\Helper;

class Note extends Page
{
    /**
     * @var string $path
     */
    protected $path = '/note';

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
        if(count($notePositions) !== count($items)) {
            $message = sprintf(
                'There are %d products on the note! (should be %d)',
                count($notePositions),
                count($items)
            );
            Helper::throwException($message);
        }

        $result = Helper::searchElements($items, $notePositions);

        if($result !== true) {
            $messages = array('The following articles were not found:');
            foreach ($result as $product) {
                $messages[] = $product['number'] . ' - ' . $product['name'];
            }
            Helper::throwException($messages);
        }
    }
}
