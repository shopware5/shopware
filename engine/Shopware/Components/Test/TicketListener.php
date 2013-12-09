<?php
/**
 * Shopware 4
 * Copyright © shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

/**
 * Shopware test listener
 */
class Shopware_Components_Test_TicketListener extends PHPUnit_Extensions_TicketListener
{
	protected $client;
	protected $serverAddress;
	protected $printTicketStateChanges;
	protected $notifyTicketStateChanges;

	/**
	 * Constructor method
	 *
	 * @param string|array $options
	 */
	public function __construct($serverAddress, $printTicketStateChanges=false , $notifyTicketStateChanges=false)
    {
    	$this->serverAddress = $serverAddress;
    	$this->printTicketStateChanges = $printTicketStateChanges;
    	$this->notifyTicketStateChanges = $notifyTicketStateChanges;
    }

    /**
     * Get the status of a ticket message
     *
     * @param  integer $ticketId The ticket ID
     * @return array('status' => $status) ($status = new|closed|unknown_ticket)
     */
    public function getTicketInfo($ticketId = null)
    {
    	if (!is_numeric($ticketId)) {
    		return array('status' => 'invalid_ticket_id');
    	}
    	try {
    		$info = $this->getClient()->call('ticket.get', (int) $ticketId);
    		switch ($info[3]['jenkins']) {
    			case '':
    			case 'Test erfolgreich':
    				return array('status' => 'closed');
    			case 'Kein Test':
    			case 'Test fehlgeschlagen':
    				return array('status' => 'new');
    			default:
    				return array('status' => 'unknown_ticket');
    		}
    	}
    	catch (Exception $e) {
    		return array('status' => 'unknown_ticket');
    	}
    }

    /**
     * Update a ticket with a new status
     *
     * @param string $ticketId   The ticket number of the ticket under test (TUT).
     * @param string $statusToBe The status of the TUT after running the associated test.
     * @param string $message    The additional message for the TUT.
     * @param string $resolution The resolution for the TUT.
     */
    protected function updateTicket($ticketId, $statusToBe, $message, $resolution)
    {
    	$statusText = $statusToBe=='closed' ? 'Test erfolgreich' : 'Test fehlgeschlagen';

        $this->getClient()->call('ticket.update', array(
        	(int) $ticketId,
        	$message,
        	null,
        	null,
        	array(
        		'jenkins_date' => Zend_Date::now()->toString('YYYY-MM-dd HH:mm:ss'),
        		'jenkins' => $statusText,
        		'resolution' => $resolution
        	),
        	$this->notifyTicketStateChanges
    	));

    	if ($this->printTicketStateChanges) {
    		printf(
	    		"\nUpdating Trac issue #%d, status: %s\n",
	    		$ticketId,
	    		$statusText
    		);
    	}
    }

    /**
     * Returns xml rpc client
     *
     * @return Zend_XmlRpc_Client
     */
    protected function getClient()
    {
    	if($this->client === null) {
    		$this->client = new Zend_XmlRpc_Client($this->serverAddress);
    		if (extension_loaded('curl')) {
				$adapter = new Zend_Http_Client_Adapter_Curl();
				$adapter->setCurlOption(CURLOPT_SSL_VERIFYPEER, false);
				$adapter->setCurlOption(CURLOPT_SSL_VERIFYHOST, false);
				$this->client->getHttpClient()->setAdapter($adapter);
			}
    	}
        return $this->client;
    }

    /**
     * Adds an error to the list of errors.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  Exception              $e
     * @param  float                  $time
     */
    public function addError(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
    	$ifStatus   = array('closed');
        $newStatus  = 'reopened';
        $message    = 'Automatically reopened by PHPUnit (test failed).';
        $resolution = '';
        $cumulative = FALSE;
        $adjustTicket = TRUE;

        $message .= "\n".$e->getMessage();

        if ($e instanceof PHPUnit_Framework_ExpectationFailedException) {
        //	$message .= "\n".$e->getCustomMessage();
        }

        $message = str_replace("\n", "\n[[BR]]", $message);

        $name = $test->getName(false);
        $tickets = PHPUnit_Util_Test::getTickets(get_class($test), $name);

        foreach ($tickets as $ticket) {
           $ticketInfo = $this->getTicketInfo($ticket);

            if ($adjustTicket && in_array($ticketInfo['status'], $ifStatus)) {
                $this->updateTicket($ticket, $newStatus, $message, $resolution);
            }
        }
    }

    /**
     * Adds a failure to the list of failures.
     * The passed in exception caused the failure.
     *
     * @param  PHPUnit_Framework_Test                 $test
     * @param  PHPUnit_Framework_AssertionFailedError $e
     * @param  float                                  $time
     */
    public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time)
    {
    	$this->addError($test, $e, $time);
    }

    /**
     * A test ended method
     *
     * @param PHPUnit_Framework_Test $test
     * @param float $time
     */
    public function endTest(PHPUnit_Framework_Test $test, $time)
    {
        if ($test instanceof PHPUnit_Framework_Warning) {
        	return;
        } elseif ($test->getStatus() != PHPUnit_Runner_BaseTestRunner::STATUS_PASSED) {
        	return;
        }

        $ifStatus   = array('assigned', 'new', 'reopened');
        $newStatus  = 'closed';
        $message    = 'Automatically closed by PHPUnit (test passed).';
        $resolution = 'fixed';
        $cumulative = TRUE;

        $name = $test->getName(false);
        $tickets = PHPUnit_Util_Test::getTickets(get_class($test), $name);

        foreach ($tickets as $ticket) {
            // Remove this test from the totals (if it passed).
            if ($test->getStatus() == PHPUnit_Runner_BaseTestRunner::STATUS_PASSED) {
                unset($this->ticketCounts[$ticket][$name]);
            }

            // Only close tickets if ALL referenced cases pass
            // but reopen tickets if a single test fails.
            if (count($this->ticketCounts[$ticket]) > 0) {
            	// There exist remaining test cases with this reference.
            	$adjustTicket = FALSE;
            } else {
            	// No remaining tickets, go ahead and adjust.
            	$adjustTicket = TRUE;
            }

            $ticketInfo = $this->getTicketInfo($ticket);

            if ($adjustTicket && in_array($ticketInfo['status'], $ifStatus)) {
                $this->updateTicket($ticket, $newStatus, $message, $resolution);
            }
        }
    }
}
