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

namespace Shopware\Models\CommentConfirm;

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;

/**
 * CommentConfirm Model Entity
 *
 * @ORM\Entity(repositoryClass="Repository")
 * @ORM\Table(name="s_core_optin")
 */
class CommentConfirm extends ModelEntity
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="datum", type="datetime", nullable=false)
     */
    private $creationDate;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255, nullable=true)
     */
    private $type = null;

    /**
     * @var string
     *
     * @ORM\Column(name="hash", type="string", length=255, nullable=false)
     */
    private $hash;

    /**
     * @var string
     *
     * @ORM\Column(name="data", type="string", nullable=false)
     */
    private $data;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set hash
     *
     * @param string $hash
     */
    public function setHash($hash)
    {
        $this->hash = $hash;
    }

    /**
     * Get hash
     *
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * Set data
     *
     * @param string $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * Get data
     *
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

	 /**
	  * Verify that the passed string in the passed data argument
	  * is actually PHP serialized code 
	  *
	  * @return boolean true the sring is serialized PHP data.
	  * @return boolean false the string is NOT serialied PHP data.
	  */
    private function stringIsPHPSerialized($data){

        if(!is_string($data)){

            return FALSE;

        }

        $data = trim($data);

        if ( 'N;' == $data ){

            return TRUE;

        }

        if (!preg_match( '/^([adObis]):/', $data, $matches)){

            return FALSE;

        }

        $types = Array('a','O','s','b','i','d');

        if(!in_array($matches[1],$types)){

            return FALSE;

        }

        return preg_match("/^{$matches[1]}:[0-9]+:.*[;}]\$/s", $data) ||
               preg_match("/^{$matches[1]}:[0-9.E-]+;\$/",$data);

    }

	 /**
	  * Allows BC by checking if the string is serialized PHP data.
	  *
	  * Checks if the given data is php serialized, if it is, it will use 
	  * unserialize to allow Backward Compatiblity.
	  *
	  * If it's not, it will try to use json_decode to decode the data.
	  *
	  * @throws \RuntimeException if the data could not be decoded.
	  * @return mixed Depending on the contents of $this->data.
	  */
    public function getUnserializedData(){

        if($this->stringIsPHPSerialized($this->data)){

            return unserialize($this->data);

        }

        $decode = json_decode($this->data,$asArray=TRUE);

		  if(empty($decode)){

			$msg = sprintf(
								'Unable to decode JSON string "%s" | %s',
								$this->data,
								json_last_error_msg()
			);

			throw new \RuntimeException($msg);

		  }

		  return $decode;

    }

    /**
     * Set CreationDate
     *
     * @param \DateTime|string $creationDate
     */
    public function setCreationDate($creationDate)
    {
        if (!$creationDate instanceof \DateTime && strlen($creationDate) > 0) {
            $creationDate = new \DateTime($creationDate);
        }
        $this->creationDate = $creationDate;
    }

    /**
     * Get CreationDate
     *
     * @return string
     */
    public function getCreationDate()
    {
        return $this->creationDate;
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
}
