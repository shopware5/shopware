<?php declare(strict_types=1);
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

namespace Shopware\Bundle\CookieBundle\Services;

use Shopware\Bundle\CookieBundle\CookieCollection;
use Shopware\Bundle\CookieBundle\CookieGroupCollection;
use Shopware\Bundle\CookieBundle\Exceptions\InvalidCookieGroupItemException;
use Shopware\Bundle\CookieBundle\Exceptions\InvalidCookieItemException;
use Shopware\Bundle\CookieBundle\Structs\CookieGroupStruct;
use Shopware\Bundle\CookieBundle\Structs\CookieStruct;

class CookieCollector implements CookieCollectorInterface
{
    /**
     * @var \Enlight_Event_EventManager
     */
    private $eventManager;

    /**
     * @var \Shopware_Components_Snippet_Manager
     */
    private $snippetManager;

    /**
     * @var \Shopware_Components_Config
     */
    private $config;

    public function __construct(\Enlight_Event_EventManager $eventManager, \Shopware_Components_Snippet_Manager $snippetManager, \Shopware_Components_Config $config)
    {
        $this->eventManager = $eventManager;
        $this->snippetManager = $snippetManager;
        $this->config = $config;
    }

    public function collect(): CookieGroupCollection
    {
        $cookieGroupsCollection = $this->collectCookieGroups();

        $cookieCollection = new CookieCollection();
        $this->addDefaultCookies($cookieCollection);

        $this->eventManager->collect(
            'CookieCollector_Collect_Cookies',
            $cookieCollection
        );

        if (!$cookieCollection->isValid()) {
            throw new InvalidCookieItemException(sprintf('Found item inside cookie collection, which is not of type \Shopware\Bundle\CookieBundle\Structs\CookieStruct'));
        }

        $cookieCollection = $this->sortCookies($cookieCollection);
        $this->assignCookiesToGroups($cookieCollection, $cookieGroupsCollection);

        $cookieGroupsCollection = $this->eventManager->filter('CookieCollector_Filter_Collected_Cookies', $cookieGroupsCollection);

        return $cookieGroupsCollection;
    }

    public function collectCookieGroups(): CookieGroupCollection
    {
        $cookieGroupCollection = new CookieGroupCollection();

        $this->addDefaultGroups($cookieGroupCollection);

        $this->eventManager->collect(
            'CookieCollector_Collect_Cookie_Groups',
            $cookieGroupCollection
        );

        if (!$cookieGroupCollection->isValid()) {
            throw new InvalidCookieGroupItemException('Found item inside cookie group collection, which is not of type \Shopware\Bundle\CookieBundle\Structs\CookieGroupStruct');
        }

        return $cookieGroupCollection;
    }

    private function assignCookiesToGroups(CookieCollection $cookieCollection, CookieGroupCollection $cookieGroupCollection): void
    {
        /** @var CookieStruct $cookie */
        foreach ($cookieCollection as $cookie) {
            $cookieGroup = $cookieGroupCollection->getGroupByName($cookie->getGroupName());

            $cookie->setGroup($cookieGroup);
            $cookieGroup->addCookie($cookie);
        }
    }

    private function addDefaultGroups(CookieGroupCollection $cookieGroupCollection): void
    {
        $snippetNamespace = $this->snippetManager->getNamespace('frontend/cookie_consent/groups');

        $cookieGroupCollection->add(new CookieGroupStruct(CookieGroupStruct::TECHNICAL, $snippetNamespace->get('technical/title'), $snippetNamespace->get('technical/description'), true));
        $cookieGroupCollection->add(new CookieGroupStruct(CookieGroupStruct::COMFORT, $snippetNamespace->get('comfort/title'), $snippetNamespace->get('comfort/description')));
        $cookieGroupCollection->add(new CookieGroupStruct(CookieGroupStruct::PERSONALIZATION, $snippetNamespace->get('personalization/title')));
        $cookieGroupCollection->add(new CookieGroupStruct(CookieGroupStruct::STATISTICS, $snippetNamespace->get('statistics/title')));
        $cookieGroupCollection->add(new CookieGroupStruct(CookieGroupStruct::OTHERS, $snippetNamespace->get('others/title')));
    }

    private function addDefaultCookies(CookieCollection $cookieCollection): void
    {
        $snippetNamespace = $this->snippetManager->getNamespace('frontend/cookie_consent/cookies');

        $cookieCollection->add(new CookieStruct('session', '/^session\-[0-9]+$/', $snippetNamespace->get('session'), CookieGroupStruct::TECHNICAL));
        $cookieCollection->add(new CookieStruct('csrf_token', '/^__csrf_token\-[0-9]+$/', $snippetNamespace->get('csrf'), CookieGroupStruct::TECHNICAL));
        $cookieCollection->add(new CookieStruct('shop', '/^shop(\-[0-9]+)?$/', $snippetNamespace->get('shop'), CookieGroupStruct::TECHNICAL));
        $cookieCollection->add(new CookieStruct(CookieHandler::PREFERENCES_COOKIE_NAME, '/^cookiePreferences$/', $snippetNamespace->get('preferences'), CookieGroupStruct::TECHNICAL));
        $cookieCollection->add(new CookieStruct('allowCookie', '/^allowCookie$/', $snippetNamespace->get('allow'), CookieGroupStruct::TECHNICAL));
        $cookieCollection->add(new CookieStruct('cookieDeclined', '/^cookieDeclined$/', $snippetNamespace->get('decline'), CookieGroupStruct::TECHNICAL));
        $cookieCollection->add(new CookieStruct('x-ua-device', '/^x\-ua\-device$/', $snippetNamespace->get('device'), CookieGroupStruct::STATISTICS));
        $cookieCollection->add(new CookieStruct('sUniqueID', '/^sUniqueID$/', $snippetNamespace->get('note'), CookieGroupStruct::COMFORT));
        $cookieCollection->add(new CookieStruct('partner', '/^partner$/', $snippetNamespace->get('partner'), CookieGroupStruct::STATISTICS));
        $cookieCollection->add(new CookieStruct('currency', '/^currency$/', $snippetNamespace->get('currency'), CookieGroupStruct::TECHNICAL));
        $cookieCollection->add(new CookieStruct('x-cache-context-hash', '/^x\-cache\-context\-hash$/', $snippetNamespace->get('context_hash'), CookieGroupStruct::TECHNICAL));
        $cookieCollection->add(new CookieStruct('nocache', '/^nocache$/', $snippetNamespace->get('no_cache'), CookieGroupStruct::TECHNICAL));

        if ($this->config->get('useSltCookie')) {
            $cookieCollection->add(new CookieStruct('slt', '/^slt$/', $snippetNamespace->get('slt'), CookieGroupStruct::TECHNICAL));
        }
    }

    private function sortCookies(CookieCollection $cookieCollection): CookieCollection
    {
        $cookieIterator = $cookieCollection->getIterator();
        $cookieIterator->uasort(static function (CookieStruct $firstCookie, CookieStruct $secondCookie) {
            return strcmp($firstCookie->getLabel(), $secondCookie->getLabel());
        });

        return new CookieCollection(\iterator_to_array($cookieIterator, false));
    }
}
