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

namespace Shopware\Components\Plugin;

use Shopware\Components\Model\ModelManager;
use Shopware\Models\Config\Element;
use Shopware\Models\Config\Form;
use Shopware\Models\Config\Value;
use Shopware\Models\Plugin\Plugin;
use Shopware\Models\Shop\Shop;

class ConfigWriter
{
    /**
     * @var ModelManager
     */
    private $em;

    /**
     * @var \Doctrine\ORM\EntityRepository
     */
    private $elementRepository;

    /**
     * @var \Doctrine\ORM\EntityRepository
     */
    private $formRepository;

    /**
     * @var \Doctrine\ORM\EntityRepository
     */
    private $valueRepository;

    public function __construct(ModelManager $em)
    {
        $this->em = $em;

        $this->elementRepository = $this->em->getRepository(Element::class);
        $this->formRepository = $this->em->getRepository(Form::class);
        $this->valueRepository = $this->em->getRepository(Value::class);
    }

    /**
     * @param array $elements
     */
    public function savePluginConfig(Plugin $plugin, $elements, Shop $shop)
    {
        foreach ($elements as $name => $value) {
            $this->saveConfigElement($plugin, $name, $value, $shop);
        }
    }

    /**
     * @param string $name
     *
     * @throws \Exception
     */
    public function saveConfigElement(Plugin $plugin, $name, $value, Shop $shop)
    {
        /** @var Form $form */
        $form = $this->formRepository->findOneBy(['pluginId' => $plugin->getId()]);

        /** @var Element|null $element */
        $element = $this->elementRepository->findOneBy(['form' => $form, 'name' => $name]);
        if (!$element) {
            throw new \Exception(sprintf('Config element "%s" not found.', $name));
        }

        if ($element->getScope() == 0 && $shop->getId() !== 1) {
            throw new \InvalidArgumentException(sprintf("Element '%s' is not writeable for shop %s", $element->getName(), $shop->getId()));
        }

        $defaultValue = $element->getValue();

        /** @var Value|null $valueModel */
        $valueModel = $this->valueRepository->findOneBy(['shop' => $shop, 'element' => $element]);

        if (!$valueModel) {
            if ($value == $defaultValue || $value === null) {
                return;
            }

            $valueModel = new Value();
            $valueModel->setElement($element);
            $valueModel->setShop($shop);
            $valueModel->setValue($value);

            $this->em->persist($valueModel);
            $this->em->flush($valueModel);

            return;
        }

        if ($value == $defaultValue || $value === null) {
            $this->em->remove($valueModel);
        } else {
            $valueModel->setValue($value);
        }
        $this->em->flush($valueModel);
    }
}
