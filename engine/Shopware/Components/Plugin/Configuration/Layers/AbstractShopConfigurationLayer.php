<?php

declare(strict_types=1);
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

namespace Shopware\Components\Plugin\Configuration\Layers;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\OptimisticLockException;
use InvalidArgumentException;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Plugin\Configuration\WriterException;
use Shopware\Models\Config\Element;
use Shopware\Models\Config\Form;
use Shopware\Models\Config\Value;
use Shopware\Models\Plugin\Plugin;

abstract class AbstractShopConfigurationLayer implements ConfigurationLayerInterface
{
    /** @var Connection */
    protected $connection;

    /** @var ConfigurationLayerInterface */
    protected $parent;

    /** @var ModelManager */
    protected $modelManager;

    public function __construct(Connection $connection, ModelManager $modelManager, ConfigurationLayerInterface $parent)
    {
        $this->connection = $connection;
        $this->modelManager = $modelManager;
        $this->parent = $parent;
    }

    public function getParent(): ConfigurationLayerInterface
    {
        return $this->parent;
    }

    public function readValues(string $pluginName, ?int $shopId): array
    {
        $builder = $this->connection->createQueryBuilder();

        $builder->from('s_core_config_values', 'coreConfigValues')
            ->innerJoin(
                'coreConfigValues',
                's_core_config_elements',
                'coreConfigElements',
                'coreConfigValues.element_id = coreConfigElements.id'
            )
            ->innerJoin(
                'coreConfigElements',
                's_core_config_forms',
                'coreConfigForms',
                'coreConfigElements.form_id = coreConfigForms.id'
            )
            ->innerJoin(
                'coreConfigForms',
                's_core_plugins',
                'corePlugins',
                'coreConfigForms.plugin_id = corePlugins.id'
            )
        ;

        $builder = $this->configureQuery($builder, $shopId, $pluginName);

        $values = $builder->select([
                'coreConfigElements.name',
                'coreConfigValues.value',
            ])
            ->execute()
            ->fetchAll(\PDO::FETCH_KEY_PAIR)
        ;

        return $this->mergeValues($this->getParent()->readValues($pluginName, $shopId), $this->unserializeArray($values));
    }

    public function writeValues(string $pluginName, ?int $shopId, array $data): void
    {
        if (!$this->isLayerResponsible($shopId)) {
            $this->getParent()->writeValues($pluginName, $shopId, $data);

            return;
        }

        $pluginRepository = $this->modelManager->getRepository(Plugin::class);
        $formRepository = $this->modelManager->getRepository(Form::class);

        /** @var Plugin|null $plugin */
        $plugin = $pluginRepository->findOneBy(['name' => $pluginName]);
        if ($plugin === null) {
            throw new WriterException(sprintf('Plugin by name "%s" not found.', $pluginName));
        }

        /** @var Form|null $form */
        $form = $formRepository->findOneBy(['pluginId' => $plugin->getId()]);
        if ($form === null) {
            throw new WriterException(sprintf('Plugin form by plugin id "%u" not found.', $plugin->getId()));
        }

        $parentValues = $this->getParent()->readValues($pluginName, $shopId);

        foreach ($data as $key => $value) {
            $this->writeValue(
                $shopId,
                $form,
                $key,
                $value,
                $parentValues[$key] ?? null
            );
        }
    }

    /**
     * @throws WriterException
     */
    public function writeValue(?int $shopId, Form $form, string $name, $value, $parentValue)
    {
        $elementRepository = $this->modelManager->getRepository(Element::class);
        $valueRepository = $this->modelManager->getRepository(Value::class);

        /** @var Element|null $element */
        $element = $elementRepository->findOneBy(['form' => $form, 'name' => $name]);
        if ($element === null) {
            throw new WriterException(sprintf('Config element "%s" not found.', $name));
        }

        if ($shopId !== 1 && $element->getScope() === 0) {
            $message = sprintf("Element '%s' is not writeable for shop %u", $element->getName(), $shopId);
            $baseException = new InvalidArgumentException($message);
            throw new WriterException('Element is not valid', 0, $baseException);
        }

        /** @var Value|null $valueModel */
        $valueModel = $valueRepository->findOneBy(['shopId' => $shopId, 'element' => $element]);

        if ($valueModel === null) {
            if ($value === $parentValue || $value === null) {
                return;
            }

            $valueModel = new Value();
            $valueModel->setElement($element);
            $valueModel->setShopId($shopId);
            // serialize done by Doctrine
            $valueModel->setValue($value);

            $this->modelManager->persist($valueModel);
            try {
                $this->modelManager->flush($valueModel);
            } catch (OptimisticLockException $e) {
                throw new WriterException('Failed writing to database', 0, $e);
            }

            return;
        }

        if ($value === $parentValue || $value === null) {
            $this->modelManager->remove($valueModel);
        } else {
            // serialize done by Doctrine
            $valueModel->setValue($value);
        }

        try {
            $this->modelManager->flush($valueModel);
        } catch (OptimisticLockException $e) {
            throw new WriterException('Failed writing to database', 0, $e);
        }
    }

    public static function unserializeArray(array $values): array
    {
        $result = [];

        foreach ($values as $key => $value) {
            $result[$key] = empty($value) ? null : @unserialize($value, ['allowed_classes' => false]);
        }

        return $result;
    }

    protected function mergeValues(array $old, array $new): array
    {
        foreach ($new as $key => $value) {
            if (!\array_key_exists($key, $old) || $value !== null) {
                $old[$key] = $value;
            }
        }

        return $old;
    }

    abstract protected function configureQuery(QueryBuilder $builder, ?int $shopId, string $pluginName): QueryBuilder;

    abstract protected function isLayerResponsible(?int $shopId): bool;
}
