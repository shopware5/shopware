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

namespace Shopware\Bundle\MailBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Enlight_Components_Mail;
use Shopware\Bundle\MailBundle\Service\Filter\MailFilterInterface;
use Shopware\Models\Mail\Log;

class LogService implements LogServiceInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var LogEntryBuilderInterface
     */
    private $entryBuilder;

    /**
     * @var Log[]|array
     */
    private $entries;

    /**
     * @var MailFilterInterface[]
     */
    private $filters;

    public function __construct(EntityManagerInterface $entityManager, LogEntryBuilderInterface $entryBuilder, iterable $filters)
    {
        $this->entityManager = $entityManager;
        $this->entryBuilder = $entryBuilder;
        $this->filters = $filters;
        $this->entries = [];
    }

    /**
     * {@inheritdoc}
     */
    public function log(Enlight_Components_Mail $mail): void
    {
        foreach ($this->filters as $filter) {
            if ($filter->filter($mail) === null) {
                return;
            }
        }

        $this->entries[] = $this->entryBuilder->build($mail);
    }

    /**
     * {@inheritdoc}
     */
    public function flush(): void
    {
        if (empty($this->entries)) {
            return;
        }

        $this->entityManager->beginTransaction();

        try {
            foreach ($this->entries as $entry) {
                $this->entityManager->persist($entry);
            }

            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (\Exception $exception) {
            $this->entityManager->rollback();
            throw $exception;
        }
    }
}
