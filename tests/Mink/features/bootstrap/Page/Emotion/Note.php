<?php
namespace Emotion;

use SensioLabs\Behat\PageObjectExtension\PageObject\Page,
    Behat\Behat\Context\Step;

class Note extends Page
{
    /**
     * @var string $path
     */
    protected $path = '/note';

    /**
     * Counts the articles on the note
     * If the number is not equal to $count, the helper function will throw an exception $message.
     * @param array $notePositions
     * @param int $count
     */
    public function countArticles($notePositions, $count = 0)
    {
        if ($count !== count($notePositions)) {
            $message = sprintf('There are %d articles on the note (should be %d)', count($notePositions), $count);
            \Helper::throwException($message);
        }
    }

    /**
     * Compares the complete note with the given list of articles
     * @param array $notePositions
     * @param array $articles
     */
    public function checkList($notePositions, $articles)
    {
        $this->countArticles($notePositions, count($articles));

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
