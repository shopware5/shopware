<?php
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

namespace Shopware\Components\Log\Handler;

use Enlight_Components_Mail;
use Exception;
use Monolog\Handler\MailHandler;
use Monolog\Logger;
use Psr\Log\LogLevel;
use Shopware\Bundle\MailBundle\Service\Filter\AdministrativeMailFilter;

/**
 * @phpstan-import-type Level from \Monolog\Logger
 * @phpstan-import-type LevelName from \Monolog\Logger
 */
class EnlightMailHandler extends MailHandler
{
    /**
     * @var Enlight_Components_Mail
     */
    protected $mailer;

    /**
     * @param Enlight_Components_Mail     $mailer The mailer to use
     * @param Level|LevelName|LogLevel::* $level  The minimum logging level at which this handler will be triggered
     * @param bool                        $bubble Whether the messages that are handled can bubble up the stack or not
     */
    public function __construct(Enlight_Components_Mail $mailer, $level = Logger::ERROR, $bubble = true)
    {
        parent::__construct($level, $bubble);
        $this->mailer = $mailer;
    }

    /**
     * {@inheritdoc}
     */
    protected function send($content, array $records): void
    {
        $mailer = clone $this->mailer;

        try {
            $mailer->setBodyHtml($content);
            $mailer->setBodyText($content);
            $mailer->setAssociation(AdministrativeMailFilter::ADMINISTRATIVE_MAIL, true);
            $mailer->send();
        } catch (Exception $e) {
            // empty catch intended to prevent recursion
        }
    }
}
