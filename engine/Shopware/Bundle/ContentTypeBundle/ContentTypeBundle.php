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

namespace Shopware\Bundle\ContentTypeBundle;

use Shopware\Bundle\ContentTypeBundle\DependencyInjection\RegisterDynamicController;
use Shopware\Bundle\ContentTypeBundle\DependencyInjection\RegisterFieldsCompilerPass;
use Shopware\Bundle\ContentTypeBundle\DependencyInjection\RegisterTypeRepositories;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ContentTypeBundle extends Bundle
{
    /**
     * @var \PDO
     */
    private $connection;

    public function __construct(\PDO $connection = null)
    {
        $this->connection = $connection;
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->setParameter('shopware.bundle.content_type.types', $this->loadContentTypes());

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/DependencyInjection'));
        $loader->load('services.xml');

        $container->addCompilerPass(new RegisterFieldsCompilerPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 500);
        $container->addCompilerPass(new RegisterDynamicController());
        $container->addCompilerPass(new RegisterTypeRepositories());
    }

    private function loadContentTypes(): array
    {
        if ($this->connection === null) {
            return [];
        }

        try {
            $contentTypes = $this->connection->query('SELECT internalName, config FROM s_content_types');
        } catch (\Exception $e) {
            return [];
        }

        $result = [];

        try {
            foreach ($contentTypes->fetchAll(\PDO::FETCH_KEY_PAIR) as $key => $type) {
                $result[$key] = json_decode($type, true);
            }
        } catch (\Exception $e) {
        }

        return $result;
    }
}
