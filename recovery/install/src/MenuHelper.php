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

namespace Shopware\Recovery\Install;

use Shopware\Recovery\Install\Service\TranslationService;

class MenuHelper
{
    /**
     * @var \Slim\Slim
     */
    private $slim;

    /**
     * @var string[]
     */
    private $entries;

    /**
     * @var string[]
     */
    private $translations;

    public function __construct(\Slim\Slim $slim, TranslationService $translations, array $entries)
    {
        $this->entries = $entries;
        $this->slim = $slim;
        $this->translations = $translations;
    }

    public function printMenu()
    {
        $result = [];
        $complete = true;
        $entries = $this->entries;
        foreach ($entries as $entry) {
            $active = ($entry == current($this->entries));
            if ($active) {
                $complete = false;
            }

            $key = 'menuitem_' . $entry;
            $label = $this->translations->translate($key);

            $result[] = [
                'label' => $label,
                'complete' => $complete,
                'active' => $active,
            ];
        }

        $this->slim->render('/_menu.php', ['entries' => $result]);
    }

    /**
     * @param string $name
     *
     * @throws \Exception
     */
    public function setCurrent($name)
    {
        if (array_search($name, $this->entries) === false) {
            throw new \Exception('could not find entrie');
        }

        reset($this->entries);
        while ($name !== current($this->entries)) {
            next($this->entries);
        }
    }

    /**
     * @param array $params
     *
     * @return string
     */
    public function getNextUrl($params = [])
    {
        $entries = $this->entries;
        $currentEntry = \next($entries);

        return $this->slim->urlFor($currentEntry, $params);
    }

    /**
     * @param array $params
     *
     * @return string
     */
    public function getPreviousUrl($params = [])
    {
        $entries = $this->entries;
        $currentEntry = \prev($entries);

        return $this->slim->urlFor($currentEntry, $params);
    }

    /**
     * @param array $params
     *
     * @return string
     */
    public function getCurrentUrl($params = [])
    {
        $entries = $this->entries;
        $currentEntry = \current($entries);

        return $this->slim->urlFor($currentEntry, $params);
    }
}
