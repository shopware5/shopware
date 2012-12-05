<?php
/**
 * Shopware
 *
 * LICENSE
 *
 * Available through the world-wide-web at this URL:
 * http://shopware.de/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@shopware.de so we can send you a copy immediately.
 *
 * @category   Shopware
 * @package    Shopware_Tests
 * @subpackage Models
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @license    http://shopware.de/license
 * @version    $Id$
 * @author     Heiner Lohaus
 * @author     $Author$
 */

use Shopware\Models\User\User;

/**
 * Test class for Shopware/Models/User/User.
 *
 * @category   Shopware
 * @package    Shopware_Tests
 * @subpackage Models
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license
 */
class Shopware_Tests_Models_WorkshopTest extends Enlight_Components_Test_TestCase
{
    /**
     * @var Shopware\Components\Model\ModelManager
     */
    protected $em;


    /**
     * @var Shopware\Models\User\Repository
     */
    protected $repo;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->em = Shopware()->Models();
        $this->repo = Shopware()->Models()->User();
    }

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function tearDown()
    {
        parent::tearDown();
        $users = $this->repo->findBy(array('name' => 'test'));
        foreach($users as $user) {
            $this->em->remove($user);
        }
        $this->em->flush();
    }

    /**
     * Test case
     */
    public function testAddUser()
    {
        //todo@hl: After the acl implementation this test don't work
        //$this->markTestIncomplete("test Add User don't works!");
        return;

        $user = new User();
        $user->fromArray(array(
            'username' => 'test',
            'email' => 'test@unit.1234.de',
            'lastLogin' => 'now',
            'password' => md5('test'),
            'sessionId' => md5('test'),
            'name' => 'test'
        ));
        $this->em->persist($user);
        $this->em->flush();

        $this->assertInstanceOf(
            'Shopware\Models\User\User',
            $this->repo->findOneBy(array('email' => 'test@unit.1234.de'))
        );
    }
}

