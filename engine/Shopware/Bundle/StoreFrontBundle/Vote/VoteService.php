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

namespace Shopware\Bundle\StoreFrontBundle\Vote;

use Shopware\Bundle\StoreFrontBundle\Context\ShopContextInterface;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class VoteService implements VoteServiceInterface
{
    /**
     * @var \Shopware\Bundle\StoreFrontBundle\Vote\VoteGateway
     */
    private $voteGateway;

    /**
     * @var \Shopware\Bundle\StoreFrontBundle\Vote\VoteAverageGateway
     */
    private $voteAverageGateway;

    /**
     * @param VoteGateway                                               $voteGateway
     * @param \Shopware\Bundle\StoreFrontBundle\Vote\VoteAverageGateway $voteAverageGateway
     */
    public function __construct(
        VoteGateway $voteGateway,
        VoteAverageGateway $voteAverageGateway
    ) {
        $this->voteGateway = $voteGateway;
        $this->voteAverageGateway = $voteAverageGateway;
    }

    /**
     * {@inheritdoc}
     */
    public function getList($products, ShopContextInterface $context)
    {
        return $this->voteGateway->getList($products, $context->getTranslationContext());
    }

    /**
     * {@inheritdoc}
     */
    public function getAverages($products, ShopContextInterface $context)
    {
        return $this->voteAverageGateway->getList($products, $context->getTranslationContext());
    }
}
