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

namespace Shopware\Models\MultiEdit;

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;
use Shopware\Models\Article\Detail;

/**
 * Shopware SwagMultiEdit Plugin - QueueArticle Model
 *
 * @ORM\Entity(repositoryClass="Repository")
 * @ORM\Table(name="s_multi_edit_queue_articles")
 */
class QueueArticle extends ModelEntity
{
    /**
     * OWNING SIDE
     *
     * @var Detail
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Article\Detail")
     * @ORM\JoinColumn(name="detail_id", referencedColumnName="id", nullable=false)
     */
    protected $detail;

    /**
     * OWNING SIDE
     *
     * @var Queue
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\MultiEdit\Queue", inversedBy="articleDetails")
     * @ORM\JoinColumn(name="queue_id", referencedColumnName="id", nullable=false)
     */
    protected $queue;

    /**
     * Unique identifier
     *
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="detail_id", type="integer", nullable=false)
     */
    private $detailId;

    /**
     * @var int
     *
     * @ORM\Column(name="queue_id", type="integer", nullable=false)
     */
    private $queueId;

    /**
     * @param Detail $detail
     */
    public function setDetail($detail)
    {
        $this->detail = $detail;
    }

    /**
     * @return Detail
     */
    public function getDetail()
    {
        return $this->detail;
    }

    /**
     * @param Queue $queue
     */
    public function setQueue($queue)
    {
        $this->queue = $queue;
    }

    /**
     * @return Queue
     */
    public function getQueue()
    {
        return $this->queue;
    }
}
