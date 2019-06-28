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

namespace Shopware\Models\Article\Image;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;
use Shopware\Models\Article\Image;

/**
 * Shopware Article Image Mapping model.
 * The Image Mapping contains the configuration for the variant images. One mapping contains one or many
 * rule sets which contains the configured configurator options.
 * Based on the image mapping, the variant images will be extended from the main image of the article.
 *
 * @ORM\Entity()
 * @ORM\Table(name="s_article_img_mappings")
 */
class Mapping extends ModelEntity
{
    /**
     * OWNING SIDE
     *
     * @var Image
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Article\Image", inversedBy="mappings")
     * @ORM\JoinColumn(name="image_id", referencedColumnName="id")
     */
    protected $image;

    /**
     * @var ArrayCollection<\Shopware\Models\Article\Image\Rule>
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Article\Image\Rule", mappedBy="mapping", orphanRemoval=true, cascade={"persist"})
     */
    protected $rules;

    /**
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
     * @ORM\Column(name="image_id", type="integer", nullable=false)
     */
    private $imageId;

    public function __construct()
    {
        $this->rules = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Image
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param Image $image
     */
    public function setImage($image)
    {
        $this->image = $image;
    }

    /**
     * @return ArrayCollection<\Shopware\Models\Article\Image\Rule>
     */
    public function getRules()
    {
        return $this->rules;
    }

    /**
     * @param ArrayCollection<\Shopware\Models\Article\Image\Rule>|Rule[] $rules
     */
    public function setRules($rules)
    {
        $this->setOneToMany($rules, Rule::class, 'rules', 'mapping');
    }
}
