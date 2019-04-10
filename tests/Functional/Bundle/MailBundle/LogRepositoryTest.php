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

use DateInterval;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use Shopware\Bundle\MailBundle\Service\LogEntryBuilder;
use Shopware\Bundle\MailBundle\Service\LogEntryBuilderInterface;
use Shopware\Models\Mail\Log;
use Shopware\Models\Mail\LogRepositoryInterface;

class LogRepositoryTest extends \PHPUnit\Framework\TestCase
{
    use MailBundleTestTrait;

    /**
     * @var string
     */
    private const PAST_DATE = '2019-01-01T00:00:00+0000';

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var LogEntryBuilderInterface
     */
    private $builder;

    /**
     * @var LogRepositoryInterface
     */
    private $repository;

    /**
     * @var Log[]|array
     */
    private $testEntries;

    /**
     * @var DateTimeInterface
     */
    private $pastDate;

    /**
     * @var DateTimeInterface
     */
    private $currentDate;

    /**
     * @var DateTimeInterface
     */
    private $pastDatePlusOneDay;

    /**
     * @var DateTimeInterface
     */
    private $currentDateMinusOneDay;

    protected function setUp()
    {
        parent::setUp();

        $this->entityManager = Shopware()->Container()->get('models');
        $this->builder = new LogEntryBuilder($this->entityManager);
        $this->repository = $this->entityManager->getRepository(Log::class);

        $oneDay = new DateInterval(sprintf('P%dD', 1));
        $this->pastDate = new DateTime($this::PAST_DATE);
        $this->pastDatePlusOneDay = (new DateTime($this::PAST_DATE))->add($oneDay);
        $this->currentDate = new DateTime('now');
        $this->currentDateMinusOneDay = (new DateTime('now'))->sub($oneDay);

        $this->deleteLogEntries();
        $this->createLogEntries();
    }

    protected function tearDown()
    {
        $this->deleteLogEntries();

        parent::tearDown();
    }

    public function testFindByDate(): void
    {
        $entries = $this->repository->findByDate($this->pastDate, $this->currentDate);
        static::assertNotEmpty($entries);
        static::assertCount(2, $entries);

        $entries = $this->repository->findByDate($this->pastDatePlusOneDay, $this->currentDate);
        static::assertCount(1, $entries);

        $entry = array_pop($entries);
        static::assertInstanceOf(Log::class, $entry);
        static::assertEquals($this->currentDate, $entry->getSentAt());

        $entries = $this->repository->findByDate($this->pastDate, $this->currentDateMinusOneDay);
        static::assertCount(1, $entries);

        $entry = array_pop($entries);
        static::assertInstanceOf(Log::class, $entry);
        static::assertEquals($this->pastDate, $entry->getSentAt());
    }

    public function testDeleteByDate(): void
    {
        $this->repository->deleteByDate($this->pastDatePlusOneDay, $this->currentDate);

        $entries = $this->repository->findByDate($this->pastDate, $this->currentDate);
        static::assertCount(1, $entries);

        $entry = array_pop($entries);
        static::assertInstanceOf(Log::class, $entry);
        static::assertEquals($this->pastDate, $entry->getSentAt());

        $this->repository->deleteByDate($this->pastDate, $this->currentDate);

        $entries = $this->repository->findByDate($this->pastDate, $this->currentDate);
        static::assertEmpty($entries);
    }

    protected function createLogEntries(): void
    {
        $oldEntry = $this->builder->build($this->createSimpleMail());
        $oldEntry->setSentAt($this->pastDate);

        $newEntry = $this->builder->build($this->createSimpleMail());
        $newEntry->setSentAt($this->currentDate);

        $this->testEntries['oldEntry'] = $oldEntry;
        $this->testEntries['newEntry'] = $newEntry;

        foreach ($this->testEntries as $entry) {
            $this->entityManager->persist($entry);
        }

        $this->entityManager->flush();
    }

    protected function deleteLogEntries(): void
    {
        foreach ($this->repository->findAll() as $entry) {
            $this->entityManager->remove($entry);
        }

        $this->entityManager->flush();
    }
}
