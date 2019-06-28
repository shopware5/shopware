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

namespace Shopware\Bundle\ContentTypeBundle\Services;

use Doctrine\DBAL\Connection;

class ContentTypeCleanupService implements ContentTypeCleanupServiceInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var AclSynchronizerInterface
     */
    private $aclSynchronizer;

    public function __construct(Connection $connection, AclSynchronizerInterface $aclSynchronizer)
    {
        $this->connection = $connection;
        $this->aclSynchronizer = $aclSynchronizer;
    }

    public function deleteContentType(string $contentTypeName): void
    {
        // Remove acl group
        $this->aclSynchronizer->remove(strtolower('custom' . $contentTypeName));

        // Remove given seo urls
        $this->removeSeoUrls($contentTypeName);
    }

    private function removeSeoUrls(string $contentTypeName): void
    {
        $this->connection->executeQuery('DELETE FROM s_core_rewrite_urls WHERE org_path = :normalController or org_path LIKE :actionOrgPath', [
            'normalController' => 'sViewport=custom' . $contentTypeName,
            'actionOrgPath' => 'sViewport=custom' . $contentTypeName . '&sAction=detail%',
        ]);
    }
}
