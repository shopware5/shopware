<?php

use Page\Emotion\Homepage;
use Behat\Gherkin\Node\TableNode;

require_once 'SubContext.php';

class ShopwareContext extends SubContext
{
    /**
     * @When /^I received the search-results for "(?P<searchTerm>[^"]*)"$/
     */
    public function iReceivedTheSearchResultsFor($searchTerm)
    {
        $this->getPage('Homepage')->receiveSearchResultsFor($searchTerm);
    }

    /**
     * @Then /^The comparison should look like this:$/
     */
    public function theComparisonShouldLookLikeThis(TableNode $articles)
    {
        $articles = $articles->getHash();

        $this->getPage('Homepage')->checkComparison($articles);
    }

    /**
     * @Then /^the cart should contain (?P<quantity>\d+) articles with a value of "(?P<amount>[^"]*)"$/
     */
    public function theCartShouldContainArticlesWithAValueOf($quantity, $amount)
    {
        $this->getElement('HeaderCart')->checkCart($quantity, $amount);
    }

    /**
     * @When /^I subscribe to the newsletter with "(?P<email>[^"]*)"$/
     * @When /^I subscribe to the newsletter with "(?P<email>[^"]*)" :$/
     */
    public function iSubscribeToTheNewsletterWith($email, TableNode $additionalData = null)
    {
        /** @var Homepage $page */
        $page = $this->getPage('Homepage');
        $controller = $page->getController();

        $data = array(
            array(
                'field' => 'newsletter',
                'value' => $email
            )
        );

        if($controller === 'newsletter') {
            $page = $this->getPage('Newsletter');

            if($additionalData) {
                $data = array_merge($data, $additionalData->getHash());
            }
        }

        $page->subscribeNewsletter($data);
    }

    /**
     * @When /^I unsubscribe the newsletter$/
     * @When /^I unsubscribe the newsletter with "(?P<email>[^"]*)"$/
     */
    public function iUnsubscribeTheNewsletter($email = null)
    {
        $data = array();

        if($email) {
            $data = array(
                array(
                    'field' => 'newsletter',
                    'value' => $email
                )
            );
        }

        $this->getPage('Newsletter')->unsubscribeNewsletter($data);
    }

    /**
     * @When /^I click the link in my latest email$/
     */
    public function iConfirmTheLinkInTheEmail()
    {
        $sql = 'SELECT hash FROM s_core_optin ORDER BY id DESC LIMIT 1';
        $hash = $this->getContainer()->get('db')->fetchOne($sql);
        $session = $this->getSession();
        $link = $session->getCurrentUrl();
        $query = parse_url($link, PHP_URL_QUERY);

        $mask = empty($query) ? '%s/%s/%s' : '%s&%s=%s';
        $link = sprintf($mask, $link, 'sConfirmation', $hash);
        $session->visit($link);
    }
}
