<?php

namespace Shopware\Models\Translation;

use Shopware\Models\Shop\Locale;
use Shopware\Components\Model\ModelEntity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping AS ORM;


/**
 * Class Translation
 * @ORM\Entity
 * @ORM\Table(name="s_core_translations")
 */
class Translation extends ModelEntity
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
     * @var string $name
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="objecttype", type="string", nullable=false)
     */
    private $type;

    /**
     * @var string $name
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="objectdata", type="string", nullable=false)
     */
    private $data;

    /**
     * @var int $key
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="objectkey", type="integer", nullable=false)
     */
    private $key;

    /**
     * Foreign-Key for the local Association.
     * Has no getter and setter function to prevent inconsistent data
     * @var string $iso
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="objectlanguage", type="string", nullable=false)
     */
    private $shopId;

    /**
     * @var Locale
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Shop\Locale", inversedBy="translations")
     * @ORM\JoinColumn(name="objectlanguage", referencedColumnName="id")
     */
    protected $locale;

    /**
     * Class constructor which allows to create a new instance with a data array.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        if (!empty($data)) {
            $this->fromArray($data);
        }
        return $this;
    }

    /**
     * @param string $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * @return int
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param \Shopware\Models\Shop\Locale $locale
     */
    public function setLocale(Locale $locale)
    {
        $this->locale = $locale;
    }

    /**
     * @return \Shopware\Models\Shop\Locale
     */
    public function getLocale()
    {
        return $this->locale;
    }

}