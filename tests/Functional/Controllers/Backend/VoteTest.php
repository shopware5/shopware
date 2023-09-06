<?php

declare(strict_types=1);
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Tests\Functional\Controllers\Backend;

use Enlight_Components_Test_Controller_TestCase;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;

class VoteTest extends Enlight_Components_Test_Controller_TestCase
{
    use DatabaseTransactionBehaviour;

    /**
     * Standard set up for every test - just disable auth
     */
    public function setUp(): void
    {
        parent::setUp();

        // Disable auth and acl
        Shopware()->Plugins()->Backend()->Auth()->setNoAuth();
        Shopware()->Plugins()->Backend()->Auth()->setNoAcl();
    }

    /**
     * Test method to test the getVotesAction-method, which gets all product-votes
     *
     * @return array<string, mixed> Contains the product, which is created in this method
     */
    public function testGetVotes(): array
    {
        $sql = 'DELETE FROM s_articles_vote';
        Shopware()->Db()->query($sql);
        $sql = "INSERT INTO s_articles_vote (`articleID`, `name`, `headline`, `comment`, `points`, `datum`, `active`, `email`, `answer`, `answer_date`)
                VALUES ('3', 'Patrick', 'Super!', 'Gutes Produkt!', '4', '2012-03-04 16:30:43', '1', 'test@example.com', '', '')";
        Shopware()->Db()->query($sql);

        $sql = "SELECT * FROM s_articles_vote WHERE articleID = 3 AND name='Patrick'";
        $data = Shopware()->Db()->fetchRow($sql);

        $this->dispatch('backend/vote/list');
        static::assertTrue($this->View()->getAssign('success'));

        static::assertNotNull($this->View()->getAssign('data'));
        static::assertNotNull($this->View()->getAssign('total'));

        // Testing the search-function
        $filter = ['filter' => json_encode([['value' => 'test']])];
        $this->Request()->setMethod('POST')->setPost($filter);
        $this->dispatch('backend/premium/getPremiumArticles');

        static::assertTrue($this->View()->getAssign('success'));
        static::assertNotNull($this->View()->getAssign('data'));
        static::assertNotNull($this->View()->getAssign('total'));

        return $data;
    }

    /**
     * Test method to test the answerVoteAction-method, which sets an answer to a vote
     */
    public function testAnswerVote(): void
    {
        $data = $this->testGetVotes();
        $this->resetRequest()->resetResponse();

        $data['answer'] = 'Test';
        $this->Request()->setMethod('POST')->setPost($data);

        $this->dispatch('backend/vote/update');

        static::assertTrue($this->View()->getAssign('success'));
        static::assertIsArray($this->View()->getAssign('data'));
        static::assertSame('Test', $this->View()->getAssign('data')['answer']);
    }

    /**
     * Test method to test the acceptVoteAction-method, which sets the active-property to 1, so the vote is enabled
     * in the frontend
     */
    public function testAcceptVote(): void
    {
        $data = $this->testGetVotes();
        $this->resetRequest()->resetResponse();

        $sql = 'UPDATE s_articles_vote SET active=0 WHERE id=?';
        Shopware()->Db()->query($sql, [$data['id']]);

        $data['active'] = 1;

        $this->Request()->setMethod('POST')->setPost($data);

        $this->dispatch('backend/vote/update');

        static::assertTrue($this->View()->getAssign('success'));
        static::assertNotNull($this->View()->getAssign('data'));
    }

    /**
     * Test method to test the deleteVoteAction-method, which deletes the product created in the testGetVotes-method
     */
    public function testDeleteVote(): void
    {
        $data = $this->testGetVotes();
        $this->resetRequest()->resetResponse();

        $this->Request()->setMethod('POST')->setPost($data);
        $this->dispatch('backend/vote/delete');
        static::assertTrue($this->View()->getAssign('success'));
    }
}
