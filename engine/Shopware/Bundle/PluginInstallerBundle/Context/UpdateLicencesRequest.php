<?php

namespace Shopware\Bundle\PluginInstallerBundle\Context;

use Shopware\Bundle\PluginInstallerBundle\Struct\AccessTokenStruct;

class UpdateLicencesRequest extends BaseRequest
{
    /**
     * @var string
     */
    private $domain;

    /**
     * @var AccessTokenStruct
     */
    private $token;

    /**
     * @param string $shopwareVersion
     * @param string $locale
     * @param string $domain
     * @param AccessTokenStruct $token
     */
    public function __construct($shopwareVersion, $locale, $domain, AccessTokenStruct $token)
    {
        $this->domain = $domain;
        $this->token = $token;
        parent::__construct($locale, $shopwareVersion);
    }

    /**
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @return AccessTokenStruct
     */
    public function getToken()
    {
        return $this->token;
    }
}
