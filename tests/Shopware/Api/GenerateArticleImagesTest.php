<?php
class Shopware_Tests_Api_GenerateArticleImagesTest extends PHPUnit_Framework_TestCase
{
    public $apiBaseUrl = '';

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();

        $helper = TestHelper::Instance();

        $hostname = $helper->Shop()->getHost();
        if (empty($hostname)) {
            $this->markTestSkipped(
                'Hostname is not available.'
            );
        }

        $this->apiBaseUrl =  'http://' . $hostname . $helper->Shop()->getBasePath() . '/api';
        Shopware()->Db()->query('UPDATE s_core_auth SET apiKey = ? WHERE username LIKE "demo"', array(sha1('demo')));
    }

    /**
     * @return Zend_Http_Client
     */
    public function getHttpClient()
    {
        $username = 'demo';
        $password = sha1('demo');

        $adapter = new Zend_Http_Client_Adapter_Curl();
        $adapter->setConfig(array(
            'curloptions' => array(
                CURLOPT_HTTPAUTH    => CURLAUTH_DIGEST,
                CURLOPT_USERPWD     => "$username:$password"
            )
        ));

        $client = new Zend_Http_Client();
        $client->setAdapter($adapter);

        return $client;
    }

    public function testBatchDeleteShouldFail()
    {
        $client = $this->getHttpClient()->setUri($this->apiBaseUrl . '/generateArticleImages');

        $response = $client->request('DELETE');

        $this->assertEquals('application/json', $response->getHeader('Content-Type'));
        $this->assertEquals(null, $response->getHeader('Set-Cookie'));
        $this->assertEquals(405, $response->getStatus());

        $result = $response->getBody();
        $result = Zend_Json::decode($result);

        $this->assertArrayHasKey('success', $result);
        $this->assertFalse($result['success']);
        $this->assertEquals('This resource has no support for batch operations.', $result['message']);
    }

    public function testBatchPutShouldFail()
    {
        $client = $this->getHttpClient()->setUri($this->apiBaseUrl . '/generateArticleImages');

        $response = $client->request('PUT');

        $this->assertEquals('application/json', $response->getHeader('Content-Type'));
        $this->assertEquals(null, $response->getHeader('Set-Cookie'));
        $this->assertEquals(405, $response->getStatus());

        $result = $response->getBody();
        $result = Zend_Json::decode($result);

        $this->assertArrayHasKey('success', $result);
        $this->assertFalse($result['success']);
        $this->assertEquals('This resource has no support for batch operations.', $result['message']);
    }
}
