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

namespace Shopware\Models\MultiEdit;

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;

/**
 * Shopware SwagMultiEdit Plugin - QueueArticle Model
 *
 *
 *
 * @ORM\Entity(repositoryClass="Repository")
 * @ORM\Table(name="s_multi_edit_queue_articles")
 */
class QueueArticle extends ModelEntity
{
    /**
     * OWNING SIDE
     *
     * @var \Shopware\Models\Article\Detail
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Article\Detail")
     * @ORM\JoinColumn(name="detail_id", referencedColumnName="id")
     */
    protected $detail;

    /**
     * OWNING SIDE
     *
     * @var \Shopware\Models\MultiEdit\Queue
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\MultiEdit\Queue", inversedBy="articleDetails")
     * @ORM\JoinColumn(name="queue_id", referencedColumnName="id")
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
     * @param \Shopware\Models\Article\Detail $detail
     */
    public function setDetail($detail)
    {
        $this->detail = $detail;
    }

    /**
     * @return \Shopware\Models\Article\Detail
     */
    public function getDetail()
    {
        return $this->detail;
    }

    /**
     * @param \Shopware\Models\MultiEdit\Queue $queue
     */
    public function setQueue($queue)
    {
        $this->queue = $queue;
    }

    /**
     * @return \Shopware\Models\MultiEdit\Queue
     */
    public function getQueue()
    {
        return $this->queue;
    }
}
