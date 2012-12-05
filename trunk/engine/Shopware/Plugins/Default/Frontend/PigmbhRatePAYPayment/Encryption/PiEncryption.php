<?php

/**
 * En- and decrypts given string with crypt, blowfish and base64 with private key that is created randomly
 */

require_once("Blowfish/blowfish.class.php");


/**
 * Class to encrypt and decrypt Strings with blowfish and salted hash with private key
 */
class Encryption_PiEncryption
{
    /**
     * Encrypts given string with crypt, blowfish and base64 with private key
     * 
     * @param   String $stringToSalt      String to encode  
     * @return  String $encodedString     encoded String
     */
    public function getEncodedString($stringToSalt)
    {           
        $cipherKey = crypt($stringToSalt, $this->getPrivateKey());
        $bf = new Blowfish($cipherKey.$this->getPrivateKey());
        $encodedString = base64_encode($bf->encrypt($stringToSalt)); 
        return $encodedString;
    }
    
    /**
     * Decrypts given string with crypt, blowfish and base64 with private key
     * 
     * @param   String $stringToDesalt    String to encode  
     * @return  String $decodedString     encoded String
     */
    public function getDecodedString($stringToDesalt)
    {
        $cipherKey = crypt(base64_decode($stringToDesalt), $this->getPrivateKey());
        $bf = new Blowfish($cipherKey.$this->getPrivateKey());
        $decodedString = $bf->decrypt(base64_decode($stringToDesalt));    
        return $decodedString;
    }
    
    /**
     * Gets private key from piPrivateKey.php
     * 
     * @return  String PI_PRIVATE_KEY     private key
     */
    private function getPrivateKey()
    {
        $filename = dirname(__FILE__) . '/piPrivateKey.php'; 
        if (!file_exists($filename)) {
            $this->createPrivateKey();
        }
        require $filename;  
        return PI_PRIVATE_KEY;
    }
    
    /**
     * Creates file with random private key
     */    
    private function createPrivateKey()
    {
        $datei = fopen(dirname(__FILE__) . '/piPrivateKey.php', w);
        fwrite($datei, '<?php if(!defined("PI_PRIVATE_KEY")) DEFINE ("PI_PRIVATE_KEY", "'
            . $this->createRandomString(10, false, true) 
            . $this->createRandomString(20, false, false)
            . $this->createRandomString(70, true, true) . '");' 
            .' ?>');
    }
    
  /**
   * Generate a random string with variable length and optional
   * number and/or special characters
   *
   * @param int $length
   * @param bool $useNumbers
   * @param bool $useSpecialChars
   *
   * @return string
   */
    public function createRandomString($length, $useNumbers = false, $useSpecialChars = false)
    {
        $secret = '';
        $key = 0;
        $lastKey = -1; 
        $chars = range ('a', 'z');
        $numbers = range ( 2 , 9 );
        $special = array('?','!','=');
        if ($useNumbers) {
            $chars = array_merge($chars, $numbers);
        }
        if ($useSpecialChars) {
            $chars = array_merge($chars, $special);
        }
        shuffle($chars);
        for ($index = 1; $index <= (int) $length; $index++) {
            $key = array_rand($chars, 1);
            if ($key == $lastKey) {
                continue;
            } 
            if (0 == ($key % 2)) {
                $secret .= $chars[$key];
            } else {
                $secret .= strtoupper($chars[$key]);
            }
            $lastKey = $key;
        }
        return (string) $secret;
    }    
}