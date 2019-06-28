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

use Shopware\Models\Article\Article;
use Shopware\Models\Article\Configurator\PriceVariation;
use Shopware\Models\Article\Configurator\Set;

class Shopware_Controllers_Backend_ArticlePriceVariation extends Shopware_Controllers_Backend_ExtJs
{
    /**
     * Used for the product backend module to load the product data into
     * the module. This function selects only some fragments for the whole product
     * data. The full product data stack is defined in the
     * Shopware_Controller_Backend_Article::getArticle function
     *
     * @param int $configuratorSetId
     *
     * @return array
     */
    public function getArticlePriceVariations($configuratorSetId)
    {
        $variationRules = Shopware()->Models()
            ->getRepository(Article::class)
            ->getConfiguratorPriceVariationsQuery($configuratorSetId)
            ->getArrayResult();

        foreach ($variationRules as &$variationRule) {
            $variationRule = $this->explodePriceVariation($variationRule);
        }

        return $variationRules;
    }

    public function createPriceVariationAction()
    {
        try {
            $data = $this->Request()->getPost();

            /** @var PriceVariation $priceVariation */
            $priceVariation = new PriceVariation();

            $data = $this->implodePriceVariation($data);

            $priceVariation->fromArray($data);
            $modelManager = $this->get('models');
            /** @var Set|null $configuratorSet */
            $configuratorSet = $modelManager
                    ->getRepository(Set::class)
                    ->find($data['configuratorSetId']);
            $priceVariation->setConfiguratorSet($configuratorSet);

            $modelManager->persist($priceVariation);
            $modelManager->flush();

            $data['id'] = $priceVariation->getId();
            $data = $this->explodePriceVariation($data);

            $this->View()->assign([
                'success' => true,
                'data' => $data,
            ]);
        } catch (Exception $e) {
            $this->View()->assign([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function updatePriceVariationAction()
    {
        try {
            $data = $this->Request()->getPost();

            /** @var PriceVariation $priceVariation */
            $priceVariation = Shopware()->Models()
                ->getRepository(PriceVariation::class)
                ->find($data['id']);

            unset($data['options'], $data['option_names']);

            $priceVariation->fromArray($data);

            /** @var Set $configuratorSet */
            $configuratorSet = Shopware()->Models()
                ->getRepository(Set::class)
                ->find($data['configuratorSetId']);
            $priceVariation->setConfiguratorSet($configuratorSet);

            Shopware()->Models()->persist($priceVariation);
            Shopware()->Models()->flush();

            $data['id'] = $priceVariation->getId();
            $data = $this->explodePriceVariation($data);

            $this->View()->assign([
                'success' => true,
                'data' => $data,
            ]);
        } catch (Exception $e) {
            $this->View()->assign([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function deletePriceVariationAction()
    {
        try {
            $postData = $this->Request()->getPost();

            // If we get a request to delete only one element, wrap it in an array, then iterate
            if (!empty($postData['id'])) {
                $postData = [$postData];
            }

            foreach ($postData as $data) {
                /** @var PriceVariation $priceVariation */
                $priceVariation = Shopware()->Models()
                    ->getRepository(PriceVariation::class)
                    ->find($data['id']);

                Shopware()->Models()->remove($priceVariation);
                Shopware()->Models()->flush();

                $this->View()->assign([
                    'success' => true,
                ]);
            }
        } catch (Exception $e) {
            $this->View()->assign([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function getPriceVariationsAction()
    {
        $configuratorSetId = $this->Request()->get('configuratorSetId');

        if (!$configuratorSetId) {
            $this->View()->assign([
                'success' => false,
                'message' => 'Configurator set id is required',
            ]);

            return;
        }

        $data = $this->getArticlePriceVariations($configuratorSetId);

        $this->View()->assign([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Coverts a Price Variation's options from a squashed array into its details
     *
     * @param array $variation
     *
     * @return array
     */
    private function explodePriceVariation($variation)
    {
        if (empty($variation['options'])) {
            return $variation;
        }

        $optionIds = explode('|', trim($variation['options'], '|'));

        $options = Shopware()->Models()
            ->getRepository(Article::class)
            ->getAllConfiguratorOptionsIndexedByIdQuery(['options.id' => $optionIds])
            ->getResult();

        $variation['option_names'] = [];

        foreach ($options as $option) {
            $variation['option_names'][] = [
                'group' => $option->getGroup()->getName(),
                'option' => $option->getName(),
            ];
        }

        unset($variation['options']);

        return $variation;
    }

    /**
     * Coverts a Price Variation's options from detailed options into the squashed array
     *
     * @param array $variation
     *
     * @return array
     */
    private function implodePriceVariation($variation)
    {
        $variation['options'] = array_map(function ($option) {
            return $option['id'];
        }, $variation['options']);
        asort($variation['options']);

        if (!empty($variation['options'])) {
            $variation['options'] = '|' . implode('|', $variation['options']) . '|';
        }

        return $variation;
    }
}
