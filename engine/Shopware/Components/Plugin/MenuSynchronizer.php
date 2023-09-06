<?php
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

namespace Shopware\Components\Plugin;

use Doctrine\DBAL\Connection;
use InvalidArgumentException;
use RuntimeException;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Snippet\Writer\DatabaseWriter;
use Shopware\Models\Menu\Menu;
use Shopware\Models\Menu\Repository;
use Shopware\Models\Plugin\Plugin;
use Shopware\Models\Shop\Locale;

class MenuSynchronizer
{
    private ModelManager $em;

    private Repository $menuRepository;

    public function __construct(ModelManager $em)
    {
        $this->em = $em;
        $this->menuRepository = $this->em->getRepository(Menu::class);
    }

    /**
     * @return void
     */
    public function synchronize(Plugin $plugin, array $menu)
    {
        $menuNames = array_column($menu, 'name');

        $items = [];
        foreach ($menu as $menuItem) {
            if (isset($menuItem['children'])) {
                $childMenuNames = array_column($menuItem['children'], 'name');
                if ($childMenuNames) {
                    $menuNames = array_merge($menuNames, $childMenuNames);
                }
            }

            if ($menuItem['isRootMenu']) {
                $parent = null;
            } else {
                if (!isset($menuItem['parent'])) {
                    throw new InvalidArgumentException('Root Menu Item must provide parent element');
                }
                $parent = $this->menuRepository->findOneBy($menuItem['parent']);
                if (!$parent) {
                    throw new InvalidArgumentException(sprintf('Unable to find parent for query %s', print_r($menuItem['parent'], true)));
                }
            }

            $items[] = $this->createMenuItem($plugin, $parent, $menuItem);
        }

        $this->em->flush($items);
        $this->removeNotExistingEntries($plugin->getId(), $menuNames);
    }

    private function saveMenuTranslation(array $labels, string $name): void
    {
        $databaseWriter = new DatabaseWriter($this->em->getConnection());
        foreach ($labels as $locale => $text) {
            if ($locale === 'en') {
                $locale = 'en_GB';
            }

            if ($locale === 'de') {
                $locale = 'de_DE';
            }

            $locale = Shopware()->Models()->getRepository(Locale::class)->findOneBy(['locale' => $locale]);
            if (!$locale instanceof Locale) {
                continue;
            }

            $databaseWriter->write([$name => $text], 'backend/index/view/main', $locale->getId(), 1);
        }
    }

    private function createMenuItem(Plugin $plugin, ?Menu $parent, array $menuItem): Menu
    {
        $item = null;

        if ($plugin->getId()) {
            $item = $this->menuRepository->findOneBy([
                'pluginId' => $plugin->getId(),
                'label' => $menuItem['name'],
            ]);
        }

        if (!$item) {
            $item = new Menu();
        }

        $item->setParent($parent);
        $item->setPlugin($plugin);

        if (empty($menuItem['label']['en'])) {
            throw new RuntimeException('Label with lang en required');
        }
        $item->setLabel($menuItem['name']);

        $item->setController($menuItem['controller'] ?? null);

        $item->setAction($menuItem['action'] ?? null);

        $item->setOnclick($menuItem['onclick'] ?? null);

        $item->setClass($menuItem['class'] ?? null);

        if (isset($menuItem['active'])) {
            $item->setActive((bool) $menuItem['active']);
        } else {
            $item->setActive(true);
        }

        $item->setPosition(
            isset($menuItem['position']) ? (int) $menuItem['position'] : 0
        );

        if (isset($menuItem['controller'])) {
            $name = $menuItem['controller'];

            // Index actions aren't appended to the name of the snippet, they are an exemption from the rule
            if ($menuItem['action'] !== 'Index') {
                $name .= '/' . $menuItem['action'];
            }

            $this->saveMenuTranslation($menuItem['label'], $name);
        }

        if (isset($menuItem['children'])) {
            foreach ($menuItem['children'] as $child) {
                $this->createMenuItem($plugin, $item, $child);
            }
        }

        $this->em->persist($item);

        return $item;
    }

    private function removeNotExistingEntries(int $pluginId, array $menuNames): void
    {
        $builder = $this->em->getConnection()->createQueryBuilder();
        $builder->delete('s_core_menu');
        $builder->where('name NOT IN (:menuNames)');
        $builder->andWhere('pluginID = :pluginId');
        $builder->setParameter(':menuNames', $menuNames, Connection::PARAM_STR_ARRAY);
        $builder->setParameter(':pluginId', $pluginId);
        $builder->execute();
    }
}
