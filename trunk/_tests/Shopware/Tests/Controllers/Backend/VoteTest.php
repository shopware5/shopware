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
 * @subpackage Controllers
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @license    http://shopware.de/license
 * @version    $Id$
 * @author     Heiner Lohaus
 * @author     $Author$
 */

class Shopware_Tests_Controllers_Backend_VoteTest extends Enlight_Components_Test_Controller_TestCase
{
    /**
     * Standard set up for every test - just disable auth
     */
    public function setUp()
    {
        parent::setUp();

        // disable auth and acl
        Shopware()->Plugins()->Backend()->Auth()->setNoAuth();
        Shopware()->Plugins()->Backend()->Auth()->setNoAcl();
    }

    /**
     * Test method to test the getVotesAction-method, which gets all article-votes
     * @return array Contains the article, which is created in this method
     */
    public function testGetVotes()
    {
        $sql= "DELETE FROM s_articles_vote";
        Shopware()->Db()->query($sql, array());
        $sql = "INSERT INTO s_articles_vote (`articleID`, `name`, `headline`, `comment`, `points`, `datum`, `active`, `email`, `answer`, `answer_date`)
                VALUES ('3', 'Patrick', 'Super!', 'Gutes Produkt!', '4', '2012-03-04 16:30:43', '1', 'ps@shopware.de', '', '')";
        Shopware()->Db()->query($sql, array());

        $sql = "SELECT * FROM s_articles_vote WHERE articleID = 3 AND name='Patrick'";
        $data = Shopware()->Db()->fetchRow($sql, array());

        /** @var Enlight_Controller_Response_ResponseTestCase */
        $this->dispatch('backend/vote/getVotes');
        $this->assertTrue($this->View()->success);

        $this->assertNotNull($this->View()->data);
        $this->assertNotNull($this->View()->total);

        //Testing the search-function
        $filter = array('filter' => Zend_Json::encode(array(array('value' => 'test'))));
        $this->Request()->setMethod('POST')->setPost($filter);
        $this->dispatch('backend/premium/getPremiumArticles');

        $this->assertTrue($this->View()->success);
        $this->assertNotNull($this->View()->data);
        $this->assertNotNull($this->View()->total);

        return $data;
    }

    /**
     * Test method to test the answerVoteAction-method, which sets an answer to a vote
     * @depends testGetVotes
     * @param $data Contains the article, which is created in testGetVotes
     */
    public function testAnswerVote($data)
    {
        $data['answer'] = "Test";
        $data['answer_datum'] = date("Y-m-d H:i:s");
        $this->Request()->setMethod('POST')->setPost($data);

        $this->dispatch('backend/vote/editVote');

        $this->assertTrue($this->View()->success);
        $this->assertNotNull($this->View()->data);
    }

    /**
     * Test method to test the acceptVoteAction-method, which sets the active-property to 1, so the vote is enabled
     * in the frontend
     * @depends testGetVotes
     * @param $data Contains the article, which is created in testGetVotes
     */
    public function testAcceptVote($data)
    {
        $sql = "UPDATE s_articles_vote SET active=0 WHERE id=?";
        Shopware()->Db()->query($sql, array($data['id']));

        $data['active'] = 1;

        $this->Request()->setMethod('POST')->setPost($data);

        $this->dispatch('backend/vote/editVote');

        $this->assertTrue($this->View()->success);
        $this->assertNotNull($this->View()->data);
    }

    /**
     * Test method to test the deleteVoteAction-method, which deletes the article created in the testGetVotes-method
     * @depends testGetVotes
     * @param $data Contains the article, which is created in testGetVotes
     */
    public function testDeleteVote($data){
        $this->Request()->setMethod('POST')->setPost($data);

        $this->dispatch('backend/vote/deleteVote');

        $this->assertTrue($this->View()->success);
        $this->assertNotNull($this->View()->data);
    }
}