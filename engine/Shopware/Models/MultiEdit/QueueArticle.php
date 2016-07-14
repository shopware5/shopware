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
 * @category  Shopware
 * @package   Shopware\Plugins\SwagMultiEdit\Models
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 *
 * @ORM\Entity(repositoryClass="Repository")
 * @ORM\Table(name="s_multi_edit_queue_articles")
 */
class QueueArticle extends ModelEntity
{
    /**
     * Unique identifier
     *
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var integer $detailId
     *
     * @ORM\Column(name="detail_id", type="integer", nullable=false)
     */
    private $detailId;

    /**
     * @var integer $queueId
     *
     * @ORM\Column(name="queue_id", type="integer", nullable=false)
     */
    private $queueId;

    /**
     * OWNING SIDE
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Article\Detail")
     * @ORM\JoinColumn(name="detail_id", referencedColumnName="id")
     */
    protected $detail;

    /**
     * OWNING SIDE
     * @ORM\ManyToOne(targetEntity="Shopware\Models\MultiEdit\Queue", inversedBy="articleDetails")
     * @ORM\JoinColumn(name="queue_id", referencedColumnName="id")
     */
    protected $queue;

    /**
     * @param mixed $detail
     */
    public function setDetail($detail)
    {
        $this->detail = $detail;
    }

    /**
     * @return mixed
     */
    public function getDetail()
    {
        return $this->detail;
    }

    /**
     * @param mixed $queue
     */
    public function setQueue($queue)
    {
        $this->queue = $queue;
    }

    /**
     * @return mixed
     */
    public function getQueue()
    {
        return $this->queue;
    }
}
