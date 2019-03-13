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

namespace Shopware\Components\Filesystem;

use IteratorAggregate;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Filesystem;
use Shopware\Bundle\MediaBundle\Adapters\AdapterFactoryInterface;
use Shopware\Components\Filesystem\Exception\AdapterFactoryNotFoundException;
use Shopware\Components\Filesystem\Exception\DuplicateFilesystemFactoryException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilesystemFactory implements FilesystemFactoryInterface
{
    /**
     * @var AdapterFactoryInterface[]
     */
    private $adapterFactories;

    public function __construct(IteratorAggregate $adapterFactories)
    {
        $adapterFactoryArray = iterator_to_array($adapterFactories, false);
        $this->checkDuplicates($adapterFactoryArray);
        $this->adapterFactories = $adapterFactoryArray;
    }

    /**
     * {@inheritdoc}
     */
    public function factory(array $config)
    {
        $config = $this->resolveFilesystemConfig($config);
        $factory = $this->findAdapterFactory($config['type']);

        return new Filesystem(
            $factory->create($config['config']),
            ['visibility' => $config['visibility']]
        );
    }

    /**
     * @param string $type
     *
     * @throws AdapterFactoryNotFoundException
     *
     * @return AdapterFactoryInterface
     */
    private function findAdapterFactory($type)
    {
        foreach ($this->adapterFactories as $factory) {
            if ($factory->getType() === $type) {
                return $factory;
            }
        }

        throw AdapterFactoryNotFoundException::fromAdapterType($type);
    }

    /**
     * @param AdapterFactoryInterface[] $adapterFactories
     *
     * @throws DuplicateFilesystemFactoryException
     */
    private function checkDuplicates(array $adapterFactories)
    {
        $dupes = [];
        foreach ($adapterFactories as $adapter) {
            $type = strtolower($adapter->getType());
            if (array_key_exists($type, $dupes)) {
                throw DuplicateFilesystemFactoryException::fromAdapterType($type);
            }

            $dupes[$type] = 1;
        }
    }

    /**
     * @return array
     */
    private function resolveFilesystemConfig(array $config)
    {
        $options = new OptionsResolver();

        $options->setRequired(['type']);
        $options->setDefined(['config', 'visibility', 'disable_asserts']);

        $options->setDefault('config', []);
        $options->setDefault('visibility', AdapterInterface::VISIBILITY_PUBLIC);
        $options->setDefault('disable_asserts', false);

        $options->setAllowedTypes('type', 'string');
        $options->setAllowedTypes('config', 'array');
        $options->setAllowedTypes('disable_asserts', 'bool');

        $options->setAllowedValues('visibility', [AdapterInterface::VISIBILITY_PUBLIC, AdapterInterface::VISIBILITY_PRIVATE]);

        return $options->resolve($config);
    }
}
