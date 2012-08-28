<?php
/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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

namespace Shopware\Models\Attribute;

use Doctrine\ORM\Mapping as ORM,
    Shopware\Components\Model\ModelEntity;

/**
 * Shopware\Models\Attribute\Form
 *
 * @ORM\Table(name="s_cms_support_attributes")
 * @ORM\Entity
 */
class Form extends ModelEntity
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var integer $formId
     *
     * @ORM\Column(name="cmsSupportID", type="integer", nullable=true)
     */
    private $formId = null;

    /**
     * @var Shopware\Models\Form\Form
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Form\Form", inversedBy="attribute")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="cmsSupportID", referencedColumnName="id")
     * })
     */
    private $form;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set form
     *
     * @param Shopware\Models\Form\Form $form
     * @return Form
     */
    public function setForm(\Shopware\Models\Form\Form $form = null)
    {
        $this->form = $form;
        return $this;
    }

    /**
     * Get form
     *
     * @return Shopware\Models\Form\Form
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * Set formId
     *
     * @param integer $formId
     * @return Form
     */
    public function setFormId($formId)
    {
        $this->formId = $formId;
        return $this;
    }

    /**
     * Get formId
     *
     * @return integer
     */
    public function getFormId()
    {
        return $this->formId;
    }
}
