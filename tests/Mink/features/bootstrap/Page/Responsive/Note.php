<?php
namespace Page\Responsive;

use Element\Emotion\NotePosition;

class Note extends \Page\Emotion\Note
{
    /**
     * @param NotePosition $notePositions
     * @param array[] $items
     */
    public function checkNoteProducts(NotePosition $notePositions, array $items)
    {
        foreach ($items as &$item) {
            if(array_key_exists('description', $item)) {
                unset($item['description']);
            }
        }

        parent::checkNoteProducts($notePositions, $items);
    }
}
