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

namespace Shopware\Bundle\ContentTypeBundle\Services;

use Doctrine\DBAL\Connection;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Snippet\Writer\DatabaseWriter;
use Shopware\Models\Menu\Menu;
use Shopware\Models\Menu\Repository as MenuRepository;
use Shopware\Models\Shop\Locale;

class MenuSynchronizer implements MenuSynchronizerInterface
{
    /**
     * @var ModelManager
     */
    private $em;

    /**
     * @var MenuRepository
     */
    private $menuRepository;

    public function __construct(ModelManager $em)
    {
        $this->em = $em;
        $this->menuRepository = $this->em->getRepository(Menu::class);
    }

    /**
     * {@inheritdoc}
     */
    public function synchronize(array $menu): void
    {
        $contentTypes = array_column($menu, 'contentType');

        $items = [];
        foreach ($menu as $menuItem) {
            if ($menuItem['isRootMenu']) {
                $parent = null;
            } else {
                if (!isset($menuItem['parent'])) {
                    throw new \InvalidArgumentException('Root Menu Item must provide parent element');
                }

                /** @var Menu $parent */
                $parent = $this->menuRepository->findOneBy($menuItem['parent']);

                if (!is_object($parent)) {
                    throw new \InvalidArgumentException(sprintf('Unable to find parent for query %s', print_r($menuItem['parent'], true)));
                }
            }

            $items[] = $this->createMenuItem($menuItem, $parent);
        }

        $this->em->flush($items);
        $this->removeNotExistingEntries($contentTypes);
        $this->cleanupContentTypeEntries();
    }

    protected function createMenuItem(array $menuItem, Menu $parent = null): Menu
    {
        $item = null;

        /** @var Menu $item */
        $item = $this->menuRepository->findOneBy([
            'contentType' => $menuItem['contentType'],
            'label' => $menuItem['name'],
        ]);

        if (!is_object($item)) {
            $item = new Menu();
        }

        $item->setParent($parent);
        $item->setContentType($menuItem['contentType']);
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

        $item->setPosition(isset($menuItem['position']) ? (int) $menuItem['position'] : 0);

        if (isset($menuItem['controller'])) {
            $name = $menuItem['controller'];

            // Index actions aren't appended to the name of the snippet, they are an exemption from the rule
            if ($menuItem['action'] !== 'Index') {
                $name .= '/' . $menuItem['action'];
            }

            $this->saveMenuTranslation($menuItem['label'], $name);
        }

        $this->em->persist($item);

        return $item;
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

            $databaseWriter->write([$name => $text], 'backend/index/view/main', $locale->getId(), 1);
        }
    }

    private function removeNotExistingEntries(array $contentTypes): void
    {
        $builder = $this->em->getConnection()->createQueryBuilder();
        $builder->delete('s_core_menu');
        $builder->andWhere('content_type IS NOT NULL');

        if (!empty($contentTypes)) {
            $builder->andWhere('content_type NOT IN (:contentTypes)');
            $builder->setParameter(':contentTypes', $contentTypes, Connection::PARAM_STR_ARRAY);
        }

        $builder->execute();
    }

    private function cleanupContentTypeEntries(): void
    {
        $ids = $this->em->getConnection()->createQueryBuilder()->from('s_core_menu', 'menu')
            ->select('id')
            ->andWhere('content_type IS NOT NULL')
            ->groupBy('content_type')
            ->having('COUNT(content_type) > 1')
            ->execute()
            ->fetchAll(\PDO::FETCH_COLUMN);

        if (empty($ids)) {
            return;
        }

        $builder = $this->em->getConnection()->createQueryBuilder();
        $builder->delete('s_core_menu');
        $builder->andWhere('id IN (:ids)');
        $builder->setParameter('ids', $ids, Connection::PARAM_INT_ARRAY);
        $builder->execute();
    }
}
