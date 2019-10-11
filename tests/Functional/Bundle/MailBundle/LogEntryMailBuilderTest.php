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

use Shopware\Bundle\MailBundle\Service\LogEntryBuilder;
use Shopware\Bundle\MailBundle\Service\LogEntryBuilderInterface;
use Shopware\Bundle\MailBundle\Service\LogEntryMailBuilder;
use Shopware\Bundle\MailBundle\Service\LogEntryMailBuilderInterface;

class LogEntryMailBuilderTest extends \PHPUnit\Framework\TestCase
{
    use MailBundleTestTrait;

    /**
     * @var LogEntryBuilderInterface
     */
    private $entryBuilder;

    /**
     * @var LogEntryMailBuilderInterface
     */
    private $mailBuilder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->entryBuilder = new LogEntryBuilder(Shopware()->Container()->get('models'));
        $this->mailBuilder = new LogEntryMailBuilder(
            Shopware()->Container()->get('shopware.filesystem.private'),
            Shopware()->Container()->get('shopware_media.media_service')
        );
    }

    public function testBuild(): void
    {
        $mail = $this->createSimpleMail();

        $entry = $this->entryBuilder->build($mail);
        $built = $this->mailBuilder->build($entry);

        static::assertNotNull($built);
        static::assertEquals($mail->getFrom(), $built->getFrom());
        static::assertCount(count($mail->getTo()), $built->getTo());

        /*
         * Check if the log-derived mail object contains all original recipient addresses.
         * When using assertEquals or assertArraySubset, the order of the elements is taken into account as well
         * which is unnecessary in this case.
         */
        foreach ($mail->getTo() as $recipient) {
            static::assertContains($recipient, $built->getTo());
        }

        static::assertEquals($mail->getSubject(), $built->getSubject());
        static::assertEquals($mail->getBodyText(), $built->getBodyText());
        static::assertEquals($mail->getBodyHtml(), $built->getBodyHtml());
        static::assertEquals($mail->hasAttachments, $built->hasAttachments);

        $entry->setSender('"AAA\" params injection"@domain');
        $built = $this->mailBuilder->build($entry);

        static::assertEquals(LogEntryMailBuilder::INVALID_SENDER_REPLACEMENT_ADDRESS, $built->getFrom());
    }
}
