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

namespace Shopware\Tests\Functional\Bundle\MailBundle;

use Doctrine\ORM\EntityManagerInterface;
use Shopware\Bundle\MailBundle\Service\LogEntryBuilder;
use Shopware\Bundle\MailBundle\Service\LogService;
use Shopware\Bundle\MailBundle\Service\LogServiceInterface;
use Shopware\Models\Mail\Log;

class LogServiceTest extends \PHPUnit\Framework\TestCase
{
    use MailBundleTestTrait;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var LogServiceInterface
     */
    private $logService;

    protected function setUp(): void
    {
        parent::setUp();

        $entityManager = Shopware()->Container()->get('models');

        $this->entityManager = $entityManager;
        $this->logService = new LogService(
            $entityManager->getConnection(),
            new LogEntryBuilder($entityManager),
            []
        );
    }

    protected function tearDown(): void
    {
        foreach ($this->entityManager->getRepository(Log::class)->findAll() as $entry) {
            $this->entityManager->remove($entry);
        }

        $this->entityManager->flush();

        parent::tearDown();
    }

    public function testLog(): void
    {
        $repo = $this->entityManager->getRepository(Log::class);
        $count = $repo->count([]);

        $this->logService->log($this->createSimpleMail());
        $this->logService->flush();

        static::assertEquals($count + 1, $repo->count([]));
    }
}
