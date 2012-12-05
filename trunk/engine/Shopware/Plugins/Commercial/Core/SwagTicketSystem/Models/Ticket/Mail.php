<?php
namespace   Shopware\CustomModels\Ticket;
use         Shopware\Components\Model\ModelEntity,
            Doctrine\ORM\Mapping AS ORM;

/**
 * History Model represent the s_ticket_support_mails table
 *
 * @ORM\Entity
 * @ORM\Table(name="s_ticket_support_mails")
 */
class Mail extends ModelEntity
{
    /**
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", nullable=false)
     */
    private $name;

    /**
     * @var string $description
     *
     * @ORM\Column(name="description", type="string", nullable=false)
     */
    private $description;

    /**
     * @var string $fromMail
     *
     * @ORM\Column(name="frommail", type="string", nullable=false)
     */
    private $fromMail;

    /**
     * @var string $fromName
     *
     * @ORM\Column(name="fromname", type="string", nullable=false)
     */
    private $fromName;

    /**
     * @var string $subject
     *
     * @ORM\Column(name="subject", type="string", nullable=false)
     */
    private $subject;

    /**
     * @var string $content
     *
     * @ORM\Column(name="content", type="string", nullable=false)
     */
    private $content;

    /**
     * @var string $contentHTML
     *
     * @ORM\Column(name="contentHTML", type="string", nullable=false)
     */
    private $contentHTML;


    /**
     * @var integer $isHTML
     *
     * @ORM\Column(name="ishtml", type="boolean", nullable=false)
     */
    private $isHTML = false;

    /**
     * @var string $attachment
     *
     * @ORM\Column(name="attachment", type="string", nullable=false)
     */
    private $attachment;

    /**
     * @var integer $systemDependent
     *
     * @ORM\Column(name="sys_dependent", type="boolean", nullable=false)
     */
    private $systemDependent = false;

    /**
     * @var string $isoCode
     *
     * @ORM\Column(name="isocode", type="string", nullable=false)
     */
    private $isoCode;

    /**
    * @var integer $shopId
    *
    * @ORM\Column(name="shop_id", type="integer", nullable=false)
    */
    private $shopId;

    /**
     * OWNING SIDE - UNI DIRECTIONAL
     * @var \Shopware\Models\Shop\Shop
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Shop\Shop")
     * @ORM\JoinColumn(name="shop_id", referencedColumnName="id")
     */
    protected $shop;

    /**
     * Returns the id
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $attachment
     */
    public function setAttachment($attachment)
    {
        $this->attachment = $attachment;
    }

    /**
     * @return string
     */
    public function getAttachment()
    {
        return $this->attachment;
    }

    /**
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $contentHTML
     */
    public function setContentHTML($contentHTML)
    {
        $this->contentHTML = $contentHTML;
    }

    /**
     * @return string
     */
    public function getContentHTML()
    {
        return $this->contentHTML;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $fromMail
     */
    public function setFromMail($fromMail)
    {
        $this->fromMail = $fromMail;
    }

    /**
     * @return string
     */
    public function getFromMail()
    {
        return $this->fromMail;
    }

    /**
     * @param string $fromName
     */
    public function setFromName($fromName)
    {
        $this->fromName = $fromName;
    }

    /**
     * @return string
     */
    public function getFromName()
    {
        return $this->fromName;
    }

    /**
     * @param int $isHTML
     */
    public function setIsHTML($isHTML)
    {
        $this->isHTML = $isHTML;
    }

    /**
     * @return int
     */
    public function getIsHTML()
    {
        return $this->isHTML;
    }

    /**
     * @param string $isoCode
     */
    public function setIsoCode($isoCode)
    {
        $this->isoCode = $isoCode;
    }

    /**
     * @return string
     */
    public function getIsoCode()
    {
        return $this->isoCode;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $subject
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param int $systemDependent
     */
    public function setSystemDependent($systemDependent)
    {
        $this->systemDependent = $systemDependent;
    }

    /**
     * @return int
     */
    public function getSystemDependent()
    {
        return $this->systemDependent;
    }

    /**
     * @param \Shopware\Models\Shop\Shop $shop
     */
    public function setShop($shop)
    {
        $this->shop = $shop;
    }

    /**
     * @return \Shopware\Models\Shop\Shop
     */
    public function getShop()
    {
        return $this->shop;
    }
}