<?php
/**
 * Enlight
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://enlight.de/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@shopware.de so we can send you a copy immediately.
 *
 * @category   Enlight
 * @package    Enlight_Test
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 * @version    $Id$
 * @author     Heiner Lohaus
 * @author     $Author$
 */

/**
 * Grants a way to update trac tickets automatically.
 *
 * The Enlight_Components_Test_TicketListener_Trac updates the stored Trac tickets for the test case automatically.
 *
 * @category   Enlight
 * @package    Enlight_Test
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
class Enlight_Components_Test_TicketListener_Trac extends PHPUnit_Extensions_TicketListener
{
    /**
     * @var string Address of the server. Set in the constructor.
     */
    protected $serverAddress;

    /**
     * @var bool If set to true, output a formatted string when the ticket updated.
     */
    protected $printTicketStateChanges;

    /**
     * @var bool Will passed to the Zend_XmlRpc_Client::call function
     */
    protected $notifyTicketStateChanges;

    /**
     * Constructor method
     *
     * @param string $serverAddress
     * @param bool   $printTicketStateChanges
     * @param bool   $notifyTicketStateChanges
     */
    public function __construct($serverAddress, $printTicketStateChanges = false, $notifyTicketStateChanges = false)
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
            switch ($info[3]['status']) {
                case 'closed':
                case 'testing':
                case 'testingExt':
                    return array('status' => 'closed');
                    break;
                case 'assigned':
                case 'new':
                case 'reopened':
                    return array('status' => 'new');
                    break;
                default:
                    return array('status' => 'unknown_ticket');
            }
        } catch (Exception $e) {
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
        $this->getClient()->call(
            'ticket.update',
            array(
                (int) $ticketId,
                $message,
                null,
                null,
                array('status' => $statusToBe == 'closed' ? 'testing' : 'reopened', 'resolution' => $resolution),
                $this->notifyTicketStateChanges
            )
        );

        if ($this->printTicketStateChanges) {
            printf("\nUpdating Trac issue #%d, status: %s\n", $ticketId, $statusToBe);
        }
    }

    /**
     * Returns xml rpc client
     *
     * @return Zend_XmlRpc_Client
     */
    protected function getClient()
    {
        return new Zend_XmlRpc_Client($this->serverAddress);
    }
}
