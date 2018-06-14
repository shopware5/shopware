<?php


namespace Shopware\Tests\Functional\Plugins\Core\Router;

use Enlight_Components_Test_Controller_TestCase;

class BootstrapTest extends Enlight_Components_Test_Controller_TestCase
{
    /**
     * @dataProvider getRedirectLinks
     * @param string $oldUrl
     * @param string $newUrl
     * @param int $newSubshopId
     */
    public function testRedirect($oldUrl, $newUrl, $newSubshopId)
    {
        $this->Request()->setPost('__shop', $newSubshopId);
        $response = $this->dispatch($oldUrl);

        $this->assertEquals($newUrl, $this->getRedirectFromResponse($response));
    }

    public function getRedirectLinks()
    {
        return [
            [
                '/sommerwelten/beachwear/178/strandtuch-ibiza',
                '/en/summertime/beachwear/178/beach-towel-ibiza',
                2
            ],
            [
                '/en/summertime/beachwear/178/beach-towel-ibiza',
                '/sommerwelten/beachwear/178/strandtuch-ibiza',
                1
            ],
            [ // Is not available in english subshop
                '/freizeitwelten/vintage/137/fahrerbrille-chronos',
                null,
                1
            ]
        ];
    }

    private function getRedirectFromResponse(\Enlight_Controller_Response_ResponseTestCase $response)
    {
        return parse_url($response->getHeader('Location'))['path'];
    }
}
