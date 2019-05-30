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

namespace Shopware\Components\Emotion;

use Shopware\Components\Model\ModelManager;
use Shopware\Models\Emotion\Library\Component;
use Shopware\Models\Plugin\Plugin;

class ComponentInstaller
{
    const COMPONENT_DEFAULTS = [
        'convertFunction' => null,
        'description' => '',
        'cls' => '',
        'xtype' => 'emotion-components-base',
    ];

    /**
     * @var ModelManager
     */
    private $em;

    public function __construct(ModelManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param string $pluginName
     * @param string $componentName
     *
     * @throws \Exception
     *
     * @return Component
     */
    public function createOrUpdate($pluginName, $componentName, array $data)
    {
        $repo = $this->em->getRepository(Plugin::class);
        /** @var Plugin|null $plugin */
        $plugin = $repo->findOneBy(['name' => $pluginName]);

        if (!$plugin) {
            throw new \Exception(sprintf('Plugin by name %s not found', $pluginName));
        }

        $repo = $this->em->getRepository(Component::class);
        /** @var Component|null $component */
        $component = $repo->findOneBy([
            'name' => $componentName,
            'pluginId' => $plugin->getId(),
        ]);
        if (!$component) {
            $component = new Component();
        }

        $config = array_merge(self::COMPONENT_DEFAULTS, $data);
        $component->fromArray($config);
        $component->setPluginId($plugin->getId());
        $component->setPlugin($plugin);
        $plugin->getEmotionComponents()->add($component);

        return $component;
    }
}
