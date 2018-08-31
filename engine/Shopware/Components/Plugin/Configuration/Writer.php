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

namespace Shopware\Components\Plugin\Configuration;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Config\Element;
use Shopware\Models\Config\Form;
use Shopware\Models\Config\Value;
use Shopware\Models\Plugin\Plugin;

class Writer implements WriterInterface
{
    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @var EntityRepository
     */
    private $elementRepository;

    /**
     * @var EntityRepository
     */
    private $formRepository;

    /**
     * @var EntityRepository
     */
    private $valueRepository;

    /**
     * @var EntityRepository
     */
    private $pluginRepository;

    public function __construct(ModelManager $modelManager)
    {
        $this->modelManager = $modelManager;

        $this->elementRepository = $this->modelManager->getRepository(Element::class);
        $this->formRepository = $this->modelManager->getRepository(Form::class);
        $this->valueRepository = $this->modelManager->getRepository(Value::class);
        $this->pluginRepository = $this->modelManager->getRepository(Plugin::class);
    }

    /**
     * {@inheritdoc}
     */
    public function setByPluginName($pluginName, array $elements, $shopId = 1)
    {
        $plugin = $this->pluginRepository->findOneBy([
            'name' => $pluginName,
        ]);

        foreach ($elements as $name => $value) {
            $this->saveConfigElement($plugin->getId(), $name, $value, $shopId);
        }
    }

    /**
     * @param int    $pluginId
     * @param string $name
     * @param mixed  $value
     * @param int    $shopId
     *
     * @throws WriterException
     */
    protected function saveConfigElement($pluginId, $name, $value, $shopId)
    {
        /** @var $form Form */
        $form = $this->formRepository->findOneBy(['pluginId' => $pluginId]);

        /** @var $element Element */
        $element = $this->elementRepository->findOneBy(['form' => $form, 'name' => $name]);
        if (!$element) {
            throw new WriterException(sprintf('Config element "%s" not found.', $name));
        }

        if ($element->getScope() == 0 && $shopId !== 1) {
            throw new WriterException('Element is not valid', 0, new \InvalidArgumentException(sprintf("Element '%s' is not writeable for shop %s", $element->getName(), $shopId)));
        }

        $defaultValue = $element->getValue();

        /** @var Value $valueModel */
        $valueModel = $this->valueRepository->findOneBy(['shopId' => $shopId, 'element' => $element]);

        if (!$valueModel) {
            if ($value == $defaultValue || $value === null) {
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

        if ($value == $defaultValue || $value === null) {
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
}
