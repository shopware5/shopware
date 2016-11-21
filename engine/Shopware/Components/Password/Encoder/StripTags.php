<?php

class StripTags implements PasswordEncoderInterface
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'StripTags';
    }

    /**
     * @return boolean
     */
    public function isCompatible()
    {
        return version_compare(PHP_VERSION, '5.3.7', '>=');
    }

    /**
     * @param  string $password
     * @param  string $hash
     * @return bool
     */
    public function isPasswordValid($password, $hash)
    {
        return password_verify(strip_tags($password), $hash);
    }

    /**
     * @param  string $password
     * @return string
     */
    public function encodePassword($password)
    {
        throw new Exception('Shoud never be called.');
    }

    /**
     * @param  string $hash
     * @return bool
     */
    public function isReencodeNeeded($hash)
    {
        return true;
    }
}
