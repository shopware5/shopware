<?php
namespace Page\Emotion;

use Element\Emotion\NotePosition;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

class Note extends Page
{
    /**
     * @var string $path
     */
    protected $path = '/note';

    /**
     * Compares the complete note with the given list of articles
     * @param array $notePositions
     * @param array $articles
     */
    public function checkList($notePositions, $articles)
    {
        $this->getPage('Homepage')->assertElementCount($notePositions, count($articles));

        /** @var NotePosition $position */
        foreach ($notePositions as $position) {
            $result = $position->search($articles);

            if ($result !== false) {
                unset($articles[$result]);
            }
        }

        if (!empty($articles)) {
            $messages = array('The following articles were not found:');
            $names = array_column($articles, 'name');
            $messages[] = implode(', ', $names);
            \Helper::throwException($messages);
        }
    }
}
