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

namespace Shopware\Components\License\Service;

use Shopware\Components\License\Struct\LicenseUnpackRequest;
use Shopware\Components\License\Struct\ShopwareEdition;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Plugin\License;
use Shopware\Models\Shop\Repository as ShopRepository;
use Shopware\Models\Shop\Shop;

class ShopwareEditionService implements ShopwareEditionServiceInterface
{
    /**
     * @var ModelManager
     */
    private $models;

    /**
     * @var LicenseUnpackServiceInterface
     */
    private $licenseUnpackService;

    public function __construct(ModelManager $models, LicenseUnpackServiceInterface $licenseUnpackService)
    {
        $this->models = $models;
        $this->licenseUnpackService = $licenseUnpackService;
    }

    public function getProductEdition(): string
    {
        $licenseRepository = $this->models->getRepository(License::class);

        $license = $licenseRepository->findOneBy([
            'active' => 1,
            'module' => 'SwagCommercial',
        ]);

        if (!$license instanceof License) {
            return ShopwareEdition::CE;
        }

        /** @var ShopRepository $shopRepository */
        $shopRepository = $this->models->getRepository(Shop::class);
        $host = $shopRepository->getActiveDefault()->getHost();

        try {
            return $this->licenseUnpackService->evaluateLicense(
                new LicenseUnpackRequest($license->getLicense(), $host)
            )->edition;
        } catch (\RuntimeException $e) {
            return ShopwareEdition::CE;
        }
    }
}
