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

/**
 * Shopware Blog comment Data
 *
 * Provides a well formed structure for a blog article comment data
 *
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */

namespace Shopware\Models\Blog\Comment;

class Data implements \JSONSerializable, \ArrayAccess{

   /**
    * Contains the comment attributes as an array for convenient
    * data handling.
    * @var Array Comment structure
    */

   private $data   =   [
      'name'     => '',
      'headline' => '',
      'comment'  => '',
      'points'   => 0,
      'eMail'    => ''
   ];

   /**
    * Object constructor
    *
    * @param Array $params if parameters are given, the object will
    * try to match the indexes in $params with the indexes of $params
    * calling all of the corresponding setters.
    *
    */

   public function __construct(Array $params=Array()){

      if(empty($parameters)){
        return;
      }

      $keys = array_keys($this->data);

      foreach($keys as $k){
         $this->$k = array_key_exists($k,$params) ? $params[$k] : NULL;
      }

   }

   /**
    * Set the commenter email
    *
    * @param string $email a valid email address
    * @param callable Optional callable function to validate the email, if no
    * callable function is provided, a default closure which implements filter_var
    * will be used.
    *
    * @return $this
    */
   public function setEmail($email,callable $validator=NULL){

      if(!is_string($email)){
        throw new \InvalidArgumentException("Email is not a string",0);
      }

      $email = trim($email);

      if(!$validator){
         $validator = function($email){
            return \filter_var($email,\FILTER_VALIDATE_EMAIL);
         };
      }
      
      if(!$validator($email)){
        throw new \InvalidArgumentException("Invalid email address",1);
      }

      $this->data['eMail'] = $email;

      return $this;

   }

   /**
    * Set the comment headline 
    *
    * @see self::validateString
    * @throws \InvalidArgumentException if the headline is invalid
    * @return $this
    */
   public function setName($name,$minLen=2,$maxLen=60){
      $this->data['name'] = $this->validateString("name",$name,$minLen,$maxLen);
      return $this;
   }

   /**
    * Get the name of the comment owner
    * @return string Name of the comment owner
    */
   public function getName(){
      return $this->data['name'];
   }

   /**
    * Set the comment points (rating)
    *
    * @param int $points Amount of points
    * @param int minimum points (default 1) 
    * @param int maximum points (default 10)
    *
    * @throws \InvalidArgumentException (code 0) if the points given are less than the minimum
    * @throws \InvalidArgumentException (code 1) if the points given are more than the maximum
    *
    * @return $this
    */
   public function setPoints($points,$min=1,$max=10){
      $points = (int)$points;

      $min = (int)$min;
      $max = (int)$max;

      if($points < $min){

         throw new \InvalidArgumentException("Invalid points",0);

      }

      if($points > $max){

         throw new \InvalidArgumentException("Invalid points",1);

      }
      $this->data['points'] = $points;

      return $this;
   }

   /**
    * Get the comment points (rating)
    * @return int comment points (rating)
    */
   public function getPoints(){
      return $this->data['points'];
   }

   /**
    * Set the comment headline 
    *
    * @see self::validateString
    * @throws \InvalidArgumentException if the headline is invalid
    * @return $this
    */
   public function setHeadLine($headLine,$minLen=5,$maxLen=250){
      $this->data['headline'] = $this->validateString("head line",$headLine,$minLen,$maxLen);
      return $this;
   }

   /**
    * Get the comment headline
    * @return string
    */
   public function getHeadLine(){
      return $this->data['headline'];
   }

   /**
    * Set the comment string
    *
    * @see self::validateString
    * @throws \InvalidArgumentException if the comment is invalid
    * @return $this
    */
   public function setComment($comment,$minLen=10,$maxLen=100){
      $this->data['comment']  = $this->validateString("comment",$comment,$minLen,$maxLen);
      return $this;
   }

   /**
    * Get the comment string
    * @return string
    */
   public function getComment(){
      return $this->data['comment'];
   }

   /**
    * Validate a string (avoid code validation duplication)
    * Notice: The passed string will be trimmed.
    *
    * @param string $what description of what is being validated.
    * @param string $string the string to be validated
    * @param int Minimum length of the string (pass 0 for no minumum)
    * @param int Maximum length of the string (pass 0 for unlimited)
    * @throws \InvalidArgumentException (code 0) $what argument is not a string
    * @throws \InvalidArgumentException (code 1) String doesn't complies to the minimum length ($minLen)
    * @throws \InvalidArgumentException (code 2) String doesn't complies to the maximum length ($minLen)
    *
    * @return string The passed string for validation (trimmed)
    */
   private function validateString($what,$string,$minLen,$maxLen){
      if(!is_string($string)){

         throw new \InvalidArgumentException("$what is not a string",0);

      }

      $string  = trim($string);
      $len     = strlen($string); 
      $minLen  = (int)$minLen;
      $maxLen  = (int)$maxLen;

      if($minLen > 0 && $len < $minLen){
         throw new \InvalidArgumentException("Minimum length of $what is $minLen characters",1);
      }

      if($maxLen > 0 && $len > $maxLen){
         throw new \InvalidArgumentException("Maximum length of $what is $maxLen characters",2);
      }

      return $string;
   }

   /** 
    * Magic __set method for being able to set attributes
    * @see self::offsetExists
    * @throws \InvalidArgumentException If the given attribute doesn't exists
    * in the data array.
    */
   public function __set($attr,$value){
    if(!$this->offsetExists($offset)){
        throw new \InvalidArgumentException("Unknown attribute $attr");
    }

    $method = sprintf('set%s',ucwords($attr));
    return $this->$method($value);
   }

   /** 
    * Magic __get method for being able to access attributes 
    * @see self::offsetExists
    * @throws \InvalidArgumentException If the given attribute doesn't exists
    * in the data array.
    */
   public function __get($attr){
    return $this->offsetGet($attr);
   }

   /**
    * Return an array representation of an instance of this class.
    * @return Array object data
    */
   public function toArray(){
    return $this->data;
   }

   /** \JSONSerializable interface **/

   /**
    * This method will be called when the user 
    * performs json_serialize on an instance of this class.
    * 
    * @return string json encoded data
    */
   public function jsonSerialize(){
    return $this->data;
   }

   /** \ArrayAccess interface **/

   /**
    * Verify if an offset exists
    * @param  string  $offset "Offset" name
    * @return boolean true if the offset exists.
    * @return boolean false if the offset doesn't exists.
    */
   public function offsetExists($offset){
    return array_key_exists($offset,$this->data);
   }

   /**
    * Get a given offset
    * @throws \InvalidArgumentException if the offset is invalid
    * @return mixed offset value
    */
   public function offsetGet($offset){
    if(!$this->offsetExists($offset)){
        throw new \InvalidArgumentException("Invalid offset $offset");
    }
    return $this->data[$offset];
   }

   /**
    * Set the value of a given offset
    * @param string $name offset name
    * @param mixed $value offset value
    * @see self::__set
    * @throws \InvalidArgumentException if the offset is invalid
    * @return mixed offset value
    */
   public function offsetSet($name,$value){
    return $this->__set($name,$value);
   }

   /**
    * Unset an offset value (set it to NULL)
    * @throws \InvalidArgumentException if the offset is invalid
    */
   public function offsetUnset($offset){

    if(!$this->offsetExists($offset)){
        throw new \InvalidArgumentException("Invalid offset $offset");
    }
    $this->data[$offset] = NULL; 
   }

   /**
    * Print the data comment
    * @return string 
    */
   public function __toString(){
      return sprintf('%s',$this->getComment());
   }

}

