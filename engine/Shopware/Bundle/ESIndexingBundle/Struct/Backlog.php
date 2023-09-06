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

namespace Shopware\Bundle\ESIndexingBundle\Struct;

use DateTime;
use DateTimeInterface;

class Backlog
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $event;

    /**
     * @var array
     */
    private $payload;

    /**
     * @var DateTimeInterface
     */
    private $time;

    /**
     * @param string   $event
     * @param array    $payload
     * @param string   $time
     * @param int|null $id
     */
    public function __construct($event, $payload, $time = 'now', $id = null)
    {
        $this->id = $id;
        $this->event = $event;
        $this->payload = $payload;
        $this->time = new DateTime($time);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @return array
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * @return DateTimeInterface
     */
    public function getTime()
    {
        return $this->time;
    }
}
