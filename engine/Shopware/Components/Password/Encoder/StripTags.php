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
        $passwordManager = new \Shopware\Components\Password\Manager(
            Shopware()->Config()
        );

        $password = strip_tags($password);

        return $passwordManager->isPasswordValid($password, $hash, 'bcrypt') ||
            $passwordManager->isPasswordValid($password, $hash, 'sha256') ||
            $passwordManager->isPasswordValid($password, $hash, 'md5');
    }

    /**
     * @param  string $password
     * @return string
     */
    public function encodePassword($password)
    {
        throw new RuntimeException('Should never be called.');
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
