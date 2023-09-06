<?php

declare(strict_types=1);
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

namespace Shopware\Components\Api\Exception;

use Enlight_Exception;
use Shopware\Components\Model\ModelEntity;
use Symfony\Component\HttpFoundation\Response;

class NonUniqueIdentifierUsedException extends Enlight_Exception implements ApiException
{
    /**
     * @var list<int>
     */
    private array $alternativeIds;

    /**
     * @param class-string<ModelEntity> $model
     * @param list<int>                 $alternativeIds
     */
    public function __construct(string $identifier, string $identifierValue, string $model, array $alternativeIds = [])
    {
        $this->alternativeIds = $alternativeIds;

        $message = sprintf("Identifier '%s' with value '%s' for entity '%s' is not unique.", $identifier, $identifierValue, $model);
        parent::__construct($message, Response::HTTP_CONFLICT);
    }

    /**
     * @return list<int>
     */
    public function getAlternativeIds(): array
    {
        return $this->alternativeIds;
    }
}
