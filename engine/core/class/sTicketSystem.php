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
 * Deprecated Shopware Class that provide crm features to shopware
 */
class sTicketSystem
{
    /**
    * Pointer to Shopware-Core-Functions
    */
    public $sSYSTEM;


    /**
     * Sets the default DB connector (mysql|adodb)
     */
    public $sDbType;


    /**
     * Class-constructor
     */
    public function sTicketSystem()
    {
        //Set the Database default typ
        $this->sDbType = "mysql";
    }


    /**
     * Returns all support tickets as an associative array
     *
     * @access public
     * @author Dennis Scharfenberg
     * @version 1.0
     *
     * @param string $sort the first part of the SQL-LIMIT
     * @param string $dir
     * @param int $start the start value of SQL-LIMIT
     * @param int $limit the limit value of SQL-LIMIT
     * @param string $search Search
     * @param string $where Additional SQL-Where
     * @return array
     */
    public function getTicketSupportStore($sort="receipt", $dir="DESC", $start=0, $limit=25, $search="", $where="", $aFilter="")
    {

        /*
         * ESCAPE
         */
        $start = intval($start);
        $limit = intval($limit);
        $search = mysql_real_escape_string(stripcslashes($search));
        foreach ($aFilter as $filterKey => $filterVal) {
                $aFilter[$filterKey] = mysql_real_escape_string(stripcslashes($aFilter[$filterKey]));
        }

        /*
         * SORTFILED MAPPING
         */

        switch ($sort) {
            case "receipt":
                $sort = "ts.receipt";
            break;
            case "last_contact":
                $sort = "ts.last_contact";
            break;
            case "id":
                $sort = "ts.id";
            break;
            case "ticketID":
                $sort = "ts.id";
            break;
            case "contact":
                $sort = "ub.lastname";
            break;
            case "company":
                $sort = "ub.company";
            break;
            case "ticket_type":
                $sort = "tst.name";
            break;
        }

        /*
         * FILTER OPTIONS
         */
        $filter_add = "";
        if (!empty($aFilter['filter_status']) && $aFilter['filter_status'] != -1) {
            $filter_add .= "AND ts.statusID = '{$aFilter['filter_status']}'";
        }
        if ($aFilter['filter_employee'] != "" && $aFilter['filter_employee'] != -1) {
            $filter_add .= "AND ts.employeeID = '{$aFilter['filter_employee']}'";
        }

        //create where statement for hide all closed status
        $hide_closed_status = "";

        if ($aFilter['filter_status'] != -1) {
            if ($aFilter['filter_status'] == 4) {
                $sql_clo = "SELECT `id` , `description`
                            FROM `s_ticket_support_status`
                            WHERE `closed` = 0";
            } else {
                $sql_clo = "SELECT `id` , `description`
                            FROM `s_ticket_support_status`
                            WHERE `closed` = 1";
            }

            $q_clo = mysql_query($sql_clo);
            if (mysql_num_rows($q_clo) != 0) {
                $hide_closed_status = "AND ts.statusID NOT IN (";
                $st_ids = array();
                while ($st = mysql_fetch_assoc($q_clo)) {
                    if ($st['id'] != $aFilter['filter_status']) {
                        $st_ids[] = $st['id'];
                    }
                }
                $hide_closed_status = sprintf("AND ts.statusID NOT IN (%s)", implode(",", $st_ids));
            }
        }


        /*
         * SEARCH SETTINGS
         */
        $search = trim($search);
        if (!empty($search)) {
            $search_qr = "AND (ub.lastname LIKE '%{$search}%'
                            OR ub.firstname LIKE '%{$search}%'
                            OR CONCAT_WS(', ',ub.lastname, ub.firstname) LIKE '%{$search}%'
                            OR CONCAT_WS(' ',ub.firstname, ub.lastname) LIKE '%{$search}%'
                            OR ts.id LIKE '%{$search}%'
                            OR CONCAT('#',ts.id) LIKE '%{$search}%'
                            OR ts.message LIKE '%{$search}%'
                            OR ub.company LIKE '%{$search}%'
                            )";
        }

        /*
         * FETCH TICKET ARRAY
         */

        //seconds sort param
        if ($sort == "statusID") {
            $secSort = ", `receipt` DESC";
        }

        $sql = "
        SELECT
            ts.*,
            DATE_FORMAT(ts.receipt, '%d.%m.%Y - %H:%i') AS receipt_f,
            DATE_FORMAT(ts.last_contact, '%d.%m.%Y - %H:%i') AS last_contact_f,
            CONCAT_WS(', ',ub.lastname, ub.firstname) AS contact,
            ub.company ,
            st.description as status,
            st.color as status_color,
            tst.name as tickettyp_name,
            tst.gridcolor as tickettyp_gridcolor
        FROM `s_ticket_support` AS ts
        LEFT JOIN `s_user_billingaddress` AS ub ON(ub.userID = ts.userID)
        LEFT JOIN `s_ticket_support_status` AS st ON(st.id = ts.statusID)
        LEFT JOIN `s_ticket_support_types` AS tst ON(tst.id = ts.ticket_typeID)
        WHERE 1=1
        {$hide_closed_status}
        {$where}
        {$search_qr}
        {$filter_add}
        ORDER BY {$sort} {$dir} {$secSort}
        LIMIT {$start},{$limit}";

        if ($this->sDbType == "mysql") {
            $result = mysql_query($sql);
            $fetchTickets = array();
            while ($fetch = mysql_fetch_assoc($result)) {
                $fetchTickets[] = $fetch;
            }
        } elseif ($this->sDbType == "adodb") {
            $fetchTickets = $this->sSYSTEM->sDB_CONNECTION->GetAll($sql);
        }

        $aResults = array();
        $aResults['data'] = array();

        foreach ($fetchTickets as $fetch) {
            $data = array();
            $data['ticketID'] = $fetch['id'];

            /*
             * Highlighht Search
             */
            if (!empty($search)) {
                $search_highlight = sprintf("<span style='background-color:yellow'>%s</span>", $search);
                $fetch['message'] = str_replace($search, $search_highlight, $fetch['message']);
                $fetch['contact'] = str_replace($search, $search_highlight, $fetch['contact']);

                $data['ticketID'] = str_replace($search, $search_highlight, $data['ticketID']);
            }

            $data['id'] = $fetch['id'];
            $data['userID'] = $fetch['userID'];
            $data['employeeID'] = $fetch['employeeID'];
            $data['ticket_type'] = utf8_encode($fetch['tickettyp_name']);
            $data['ticket_typeID'] = $fetch['ticket_type'];
            $data['ticket_gridcolor'] = "<div style=\"background-color:{$fetch['tickettyp_gridcolor']};\">&nbsp;</div>";
            $data['contact'] = utf8_encode($fetch['contact']);
            $data['email'] = $fetch['email'];
            $data['status'] = $fetch['status'];
            $data['statusID'] = $fetch['statusID'];
            $data['status_color'] = $fetch['status_color'];
            $data['company'] = utf8_encode($fetch['company']);
            $data['message'] = utf8_encode($fetch['message']);
            $data['receipt'] = $fetch['receipt_f'];
            $data['last_contact'] = $fetch['last_contact_f'];
            $data['isocode'] = strtoupper($fetch['isocode']);

            if (!empty($data['contact'])) {
                $data['display_name'] = $data['contact'];
            } else {
                $data['display_name'] = $data['email'];
            }


            $aResults['data'][] = $data;
        }

        /*
         * FETCH TOTAL COUNT
         */

        $sql_total = "
        SELECT
            COUNT(*) AS total
        FROM `s_ticket_support` AS ts
        LEFT JOIN `s_user_billingaddress` AS ub ON(ub.userID = ts.userID)
        LEFT JOIN `s_ticket_support_status` AS st ON(st.id = ts.statusID)
        WHERE 1=1
        {$where}
        {$hide_closed_status}
        {$search_qr}
        {$filter_add}";


        $result_total = mysql_query($sql_total);
        $aResults['total'] = mysql_result($result_total, 0, "total");

        return $aResults;
    }

    public function getTicketCountries()
    {
        $sqlQ = mysql_query("SELECT DISTINCT tsm.`isocode`, m.name
                            FROM `s_ticket_support_mails` AS tsm
                            LEFT JOIN `s_core_multilanguage` AS m
                            ON tsm.isocode = m.isocode
                            ORDER BY tsm.`isocode`");
        $aResults = array();
        $aResults['data'] = array();

        while ($fetch = mysql_fetch_assoc($sqlQ)) {
            $data = array();
            $data['iso'] = strtoupper($fetch["isocode"]);
            $data['name'] = $fetch["name"];
            $aResults['data'][] = $data;
        }
        return $aResults;
    }

    public function getTicketMissingCountries()
    {
        $sqlQ = mysql_query("SELECT `isocode`, name
                            FROM `s_core_multilanguage`
                            WHERE `isocode` NOT
                            IN (

                            SELECT DISTINCT `isocode`
                            FROM `s_ticket_support_mails`
                            )
                            ORDER BY `isocode`");
        $aResults = array();
        $aResults['data'] = array();

        while ($fetch = mysql_fetch_assoc($sqlQ)) {
            $data = array();
            $data['iso'] = strtoupper($fetch["isocode"]);
            $data['name'] = utf8_decode($fetch["name"]);
            $aResults['data'][] = $data;
        }
        return $aResults;
    }

    /**
     * Deletes a ticket and its historys entries by ID
     *
     * @param int $ticketID s_ticket_support.id
     */
    public function deleteTicketByID($ticketID)
    {
        //Escape
        $ticketID = intval($ticketID);

        $sql1 = "DELETE FROM `s_ticket_support` WHERE `id` = '{$ticketID}' LIMIT 1";
        mysql_query($sql1);

        $sql2 = "DELETE FROM `s_ticket_support_history` WHERE `ticketID` = '{$ticketID}'";
        mysql_query($sql2);
    }

    public function deleteTicketTypeByID($typeID)
    {
        //Escape
        $typeID = intval($typeID);

        $sql1 = "DELETE FROM `s_ticket_support_types` WHERE `id` = '{$typeID}' LIMIT 1";
        return mysql_query($sql1);
    }

    /**
     * Returns all TicketMails in an ExtJS store format
     *
     * @return array
     */
    public function getTicketMailStore($ticketID)
    {
        //Escape
        $ticketID = intval($ticketID);

        $data = array();
        $standard = array();

        $sql = "
            SELECT * FROM `s_ticket_support_mails`
            WHERE `isocode` =
            (
                SELECT isocode FROM `s_ticket_support` WHERE id = '{$ticketID}'
            )
            ORDER BY `description`";

        $query = mysql_query($sql);
        while ($fetch = mysql_fetch_array($query)) {

            $fetch['subject'] = $this->replaceTicketMailBB($ticketID, $fetch['subject']);
            $fetch['content'] = $this->replaceTicketMailBB($ticketID, $fetch['content']);
            $fetch['contentHTML'] = $this->replaceTicketMailBB($ticketID, $fetch['contentHTML']);

            $fetch['subject'] = utf8_encode($fetch['subject']);
            $fetch['content'] = nl2br($fetch['content']);
            $fetch['contentHTML'] = $fetch['contentHTML'];

            if ($fetch['name'] == "sSTANDARD") {
                $standard = $fetch;
                $fetch['standard'] = 1;
            }

            $data[$fetch['id']] = $fetch;
        }

        return array("data"=>$data, "standard"=>$standard);
    }

    public function getTicketMailItem($id, $ticketID)
    {
        //Escape
        $id = intval($id);
        $ticketID = intval($ticketID);

        $sql = "SELECT * FROM `s_ticket_support_mails` WHERE `id` = '{$id}'";

        if ($this->sDbType == "adodb") {
            $fetch = $this->sSYSTEM->sDB_CONNECTION->GetRow($sql);
        } else {
            $query = mysql_query($sql);
            $fetch = mysql_fetch_array($query);
        }


        $fetch['subject'] = $this->replaceTicketMailBB($ticketID, $fetch['subject']);
        $fetch['content'] = $this->replaceTicketMailBB($ticketID, $fetch['content']);
        $fetch['contentHTML'] = $this->replaceTicketMailBB($ticketID, $fetch['contentHTML']);

        $fetch['subject'] = utf8_encode($fetch['subject']);
        $fetch['content'] = utf8_encode($fetch['content']);
        $fetch['contentHTML'] = utf8_encode($fetch['contentHTML']);

        $fetch['content'] = nl2br($fetch['content']);

        return $fetch;
    }

    public function replaceTicketMailBB($ticketID, $string)
    {

        //Escape
        $ticketID = intval($ticketID);

        if (empty($this->sSYSTEM->sCONFIG)) {
            //Load Config Data
            $query = mysql_query("SELECT `name`, `value` FROM `s_core_config`");
            $sCONFIG = array();
            while ($confData = mysql_fetch_assoc($query)) {
                $sCONFIG[$confData['name']] = $confData['value'];
            }
        } else {
            $sCONFIG = $this->sSYSTEM->sCONFIG;
        }

        //SSL CHECK
        $sCONFIG['sUSESSL'] == 1 ? $http = "https://" : $http = "http://";

        $ticketData = $this->getTicketSupportById($ticketID);
        $string = str_replace("{sTicketID}", "#".$ticketID, $string);

        //Get Shopshop URL
        $sql = "SELECT *
                FROM `s_core_multilanguage`
                WHERE `isocode` LIKE '{$ticketData['isocode']}'";

        if ($this->sDbType == "adodb") {
            $result = $this->sSYSTEM->sDB_CONNECTION->GetAll($sql);
            $url = $result[0];
        } else {
            $result = mysql_query($sql);
            $url = mysql_fetch_assoc($result);
        }

        //iso missing
        if (empty($url['domainaliase'])) {
            $sql = "SELECT *
                    FROM `s_core_multilanguage`
                    WHERE `default` = 1";
            if ($this->sDbType == "adodb") {
                $result = $this->sSYSTEM->sDB_CONNECTION->GetAll($sql);
                $url = $result[0];
            } else {
                $result = mysql_query($sql);
                $url = mysql_fetch_assoc($result);
            }
        }
        //Split
        $url_conf = explode("\n", $url['domainaliase']);


        //sCONFIG nicht verf�gbar!!!
        $temp = str_replace($sCONFIG["sHOST"],$url_conf[0],$sCONFIG["sBASEPATH"]);

        $temp = str_replace("\n","",$temp);
        $temp = str_replace("\r","",$temp);

        $string = str_replace("{sTicketDirectUrl}", "http://".$temp."/shopware.php?sViewport=ticketdirect&sAID=".$ticketData['uniqueID'], $string);

        return $string;
    }

    /**
     * Returns one support tickets as an associative array
     *
     * @access public
     * @author Dennis Scharfenberg
     * @version 1.0
     *
     * @param int $ticketID s_ticket_support.id
     * @param int $userID BenutzerID (sollte aus Sicherheitsgr�nden �bergeben werden)
     * @return array
     */
    public function getTicketSupportById($ticketID, $userID=0)
    {
        //Escape
        $ticketID = intval($ticketID);
        $userID = intval($userID);

        if (!empty($userID)) {
            $whereAdd = "AND ts.`userID` = ".$userID;
        }

        /*
         * FETCH TICKET ARRAY
         */

        $sql = "SELECT
        ts.*,
        u.email AS user_email,
        ts.email AS ticket_email,
        DATE_FORMAT(ts.receipt, '%d.%m.%Y - %H:%i') AS receipt_f,
        DATE_FORMAT(ts.receipt, '%d.%m.%Y') AS receipt_date_f,
        DATE_FORMAT(ts.last_contact, '%d.%m.%Y - %H:%i') AS last_contact_f,
        CONCAT_WS(', ',ub.lastname, ub.firstname) AS contact,
        ub.company,
        st.responsible,
        st.closed

        FROM `s_ticket_support` AS ts
        LEFT JOIN `s_user_billingaddress` AS ub ON(ub.userID = ts.userID)
        LEFT JOIN `s_user` AS u ON(u.id = ts.userID)
        LEFT JOIN `s_ticket_support_status` AS st ON(st.id = ts.statusID)

        WHERE ts.id = '{$ticketID}'
        {$whereAdd}";

        if ($this->sDbType == "mysql") {

            $result = mysql_query($sql);
            $fetch = mysql_fetch_assoc($result);
        } elseif ($this->sDbType == "adodb") {

            $fetch = $this->sSYSTEM->sDB_CONNECTION->GetRow($sql);

        }



        $aResults = array();

        $aResults['id'] = $fetch['id'];
        $aResults['userID'] = $fetch['userID'];
        $aResults['ticket_type'] = $fetch['ticket_type'];
        $aResults['contact'] = $fetch['contact'];
        $aResults['statusID'] = $fetch['statusID'];
        $aResults['company'] = $fetch['company'];
        $aResults['subject'] = $fetch['subject'];
        $aResults['message'] = $fetch['message'];
        //$aResults['message'] = nl2br($fetch['message']);
        $aResults['receipt'] = $fetch['receipt_f'];
        $aResults['receipt_date'] = $fetch['receipt_date_f'];
        $aResults['last_contact'] = $fetch['last_contact_f'];
        $aResults['user_email'] = $fetch['user_email'];
        $aResults['ticket_email'] = $fetch['ticket_email'];
        $aResults['additional'] = $fetch['additional'];
        $aResults['responsible'] = $fetch['responsible'];
        $aResults['closed'] = $fetch['closed'];
        $aResults['uniqueID'] = $fetch['uniqueID'];
        $aResults['isocode'] = $fetch['isocode'];

        return $aResults;
    }

    /**
     * Returns the last message of this ticket of the tickethistory
     *
     * @param int $ticketID s_ticket_support.id
     * @return array of ticketData
     */
    public function getLastHistoryEntryByTicketId($ticketID)
    {
        return array();
    }

    /**
     * Returns all support stat�s as an associative array
     *
     * @access public
     * @author Dennis Scharfenberg
     * @version 1.0
     *
     * @return array
     */
    public function getTicketStatusStore($filterStore=false)
    {

        $sql = "SELECT * FROM `s_ticket_support_status`";
        $result = mysql_query($sql);

        $aResults = array();

        //Add to filter store
        if ($filterStore) {
            $data['id'] = 0;
            $data['description'] = "Filter deaktivieren";
            $aResults['data'][] = $data;
            $data['id'] = -1;
            $data['description'] = "Alle anzeigen";
            $aResults['data'][] = $data;
        }

        while ($fetch = mysql_fetch_assoc($result)) {
            $data = array();
            $data['id'] = $fetch['id'];
            $data['description'] = $fetch['description'];
            $aResults['data'][] = $data;
        }

        return $aResults;
    }

    /**
     * Update the ticket with the values of an array
     *
     * @param int $ticketID s_ticket_support.id
     * @param array $aUpdates an associative arrray of update values
     */
    public function updateTicketDataById($ticketID, $aUpdates)
    {

        //Escape
        $ticketID = intval($ticketID);

        $sep = false;
        if ($this->sDbType == "mysql") {
            $sql = "UPDATE `s_ticket_support` SET ";

            foreach ($aUpdates as $field => $value) {
                //Escape
                $field = mysql_real_escape_string(stripcslashes($field));
                $value = mysql_real_escape_string(stripcslashes($value));

                if($sep) $sql .= ", ";
                $sql .= sprintf("`%s` = '%s'", $field, $value);
                $sep=true;
            }
            $sql .= sprintf(" WHERE `id` = '%s'", $ticketID);

            return mysql_query($sql);
        } else {
            foreach ($aUpdates as $field => $value) {
                $updateSQL[] = "$field = ".$this->sSYSTEM->sDB_CONNECTION->qstr($value);
            }
            $sql = "UPDATE `s_ticket_support` SET ".implode(",",$updateSQL)." WHERE `id` = ?";
            return $this->sSYSTEM->sDB_CONNECTION->Execute($sql,array($ticketID));
        }
    }

    /**
     * Saves a new entry in s_ticket_support_history
     *
     * @param array $aInsert An associative array of s_ticket_support_history values
     */
    public function insertTicketHistoryEntry($aInsert)
    {
        if ($aInsert['direction'] == "OUT") {
            if (!empty($this->sSYSTEM->_SESSION['sName'])) {
                $aInsert['user'] = $this->sSYSTEM->_SESSION['sName'];
            } else {
                $aInsert['user'] = $this->sSYSTEM->_SESSION['sUsername'];
            }
        } else {
            $aInsert['user'] = "";
        }

        if ($this->sDbType == "mysql") {
            //Escape
            $aInsert['ticketID'] = intval($aInsert['ticketID']);
            $aInsert['message'] = mysql_real_escape_string(stripcslashes($aInsert['message']));
            $aInsert['support_type'] = mysql_real_escape_string(stripcslashes($aInsert['support_type']));
            $aInsert['subject'] = mysql_real_escape_string(stripcslashes($aInsert['subject']));
            $aInsert['direction'] = mysql_real_escape_string(stripcslashes($aInsert['direction']));
            $aInsert['user'] = mysql_real_escape_string(stripcslashes($aInsert['user']));
            // todo@all $sw_user is undefined ?
            $sql = "INSERT INTO `s_ticket_support_history`
                (`ticketID`, `swUser`, `message`,  `support_type`,  `subject`, `receipt`, `direction`) VALUES
                ('{$aInsert['ticketID']}', '{$sw_user}', '{$aInsert['message']}', '{$aInsert['support_type']}', '{$aInsert['subject']}', NOW() , '{$aInsert['direction']}')";

            //Letzter Kontakt aktualisieren
            $sqlUp = "UPDATE `s_ticket_support` SET `last_contact` = NOW( ) WHERE `id` = '{$aInsert['ticketID']}' LIMIT 1";

            mysql_query($sql);
            mysql_query($sqlUp);
        } else {
            $sql = "INSERT INTO `s_ticket_support_history`
                    (`ticketID`, `swUser`, `message`,  `support_type`,  `subject`, `receipt`, `direction`) VALUES
                    (?, ?, ?, ?, ?, NOW(), ?)";
            $this->sSYSTEM->sDB_CONNECTION->Execute($sql, array($aInsert['ticketID'], $aInsert['user'], $aInsert['message'], $aInsert['support_type'], $aInsert['subject'], $aInsert['direction']));
            //Letzter Kontakt aktualisieren
            $sqlUp = "UPDATE `s_ticket_support` SET `last_contact` = NOW() WHERE `id` = ? LIMIT 1";
            $this->sSYSTEM->sDB_CONNECTION->Execute($sqlUp, array($aInsert['ticketID']));
        }
    }

    /**
     * returns all tickets and answers for one user
     *
     * @param int $ticketID The ticket ID
     * @param int $userID The id of the user
     * @return associative array
     */
    public function getTicketHistoryStore($ticketID, $userID)
    {
        $pre = ""; $post = "";
        //Escape
        $ticketID = intval($ticketID);
        $userID = intval($userID);

        if (!empty($userID)) {
            $whereAdd = "OR `userID` = '{$userID}'";
        }
        $tasks = mysql_query("
            SELECT
                ts.*,
                DATE_FORMAT(ts.receipt, '%d.%m.%Y') AS date,
                DATE_FORMAT(ts.receipt, '%H:%i') AS time
            FROM `s_ticket_support` AS ts
            WHERE
                `id` = '{$ticketID}'
                {$whereAdd}
            ORDER BY `id` DESC, ts.receipt DESC
        ");

        if (mysql_num_rows($tasks) != 0) {
            while ($fetch = mysql_fetch_array($tasks)) {
                if ($fetch['id'] == $ticketID) {
                    $current = 1;
                } else {
                    $current = 0;
                }


                $data_f = array();
                $data_f['id'] = $fetch['id'];
                $data_f['ticketID'] = $fetch['id'];
                $data_f['send_to'] = 'Support';
                $data_f['subject'] = utf8_encode($fetch['subject']);
                $data_f['message'] = $pre.utf8_encode($fetch['message']).$post;
                $data_f['message'] = nl2br($data_f['message']);
                $data_f['sUser'] = $pre."<b>Ursprungsnachricht</b>".$post;
                $data_f['date'] = $fetch['date'];
                $data_f['time'] = $fetch['time'];
                $data_f['current'] = $current;

                $tasks_history = mysql_query("
                    SELECT
                        th.*,
                        DATE_FORMAT(th.receipt, '%d.%m.%Y') AS date,
                        DATE_FORMAT(th.receipt, '%H:%i') AS time
                    FROM `s_ticket_support_history` AS th
                    WHERE
                        `ticketID` = '{$data_f['id']}'
                    ORDER BY `receipt` DESC
                ");
                while ($fetch2 = mysql_fetch_array($tasks_history)) {

                    if ($fetch2['direction'] == "IN") {
                        $username = $fetch['email'];
                    } else {
                        $username = $fetch2['swUser'];
                    }

                    $data = array();
                    $data['id'] = $fetch2['id'];
                    $data['direction'] = $fetch2['direction'];
                    $data['ticketID'] = $fetch2['ticketID'];
                    $data['subject'] = utf8_encode($fetch2['subject']);
                    $data['message'] = utf8_encode($fetch2['message']);
                    $data['message'] = nl2br($data['message']);
                    $data['sUser'] = $pre.utf8_encode($username).$post;
                    $data['date'] = $fetch2['date'];
                    $data['time'] = $fetch2['time'];
                    $data['current'] = $current;
                    $ret[] = $data;
                }

                //add ticket enquiry
                $ret[] = $data_f;
            }
        } else {
            $data['id'] = 0;
            $ret[] = $data;
        }

        return array("data"=>$ret);
    }

    /**
     * returns the history of one ticket
     *
     * @param int $ticketID The ticket ID
     * @return associative array
     */
    public function getSingleTicketHistoryStore($ticketID)
    {
        //Escape
        $pre = "";
        $post = "";
        $ret = array();
        $ticketID = intval($ticketID);

        $sql = "
            SELECT
                th.*,
                DATE_FORMAT(th.receipt, '%d.%m.%Y') AS date,
                DATE_FORMAT(th.receipt, '%H:%i') AS time
            FROM `s_ticket_support_history` AS th
            WHERE
                `ticketID` = '{$ticketID}'
            ORDER BY `receipt` ASC
        ";

        if ($this->sDbType == "mysql") {
            $fetchHistory = array();
            $result = mysql_query($sql);
            if($result&&mysql_num_rows($result))
            while ($fetch = mysql_fetch_assoc($result)) {
                $fetchHistory[] = $fetch;
            }
        } elseif ($this->sDbType == "adodb") {
            $fetchHistory = $this->sSYSTEM->sDB_CONNECTION->GetAll($sql);
        }
        foreach ($fetchHistory as $fetch2) {
            $data['id'] = $fetch2['id'];
            $data['ticketID'] = $fetch2['ticketID'];
            $data['subject'] = $fetch2['subject'];
            $data['message'] = $fetch2['message'];
            $data['message'] = nl2br($fetch2['message']);
            $data['sUser'] = $pre."Kundenanfrage".$post;
            $data['date'] = $fetch2['date'];
            $data['time'] = $fetch2['time'];
            $data['direction'] = $fetch2['direction'];
            $data['current'] = 0;
            $ret[] = $data;
        }

        return array("data"=>$ret);
    }

    public function getTicketIdByUniqueID($uniqueID)
    {
        $sql = "SELECT
                    id
                FROM `s_ticket_support`
                WHERE
                    `uniqueID` = ?
                ORDER BY `id` DESC
                LIMIT 1";

        $fetch_tmp = $this->sSYSTEM->sDB_CONNECTION->GetRow($sql,array($uniqueID));

        return $fetch_tmp['id'];
    }

    /**
     * send notification by new or answered tickets
     *
     * @param int $ticketID
     * @param bool $newticket true=new ticket; false=ticket answer
     */
    public function sendNotifyEmail($ticketID, $newticket=true)
    {
        $this->sDbType = "adodb";
        //Escape
        $ticketID = intval($ticketID);

        $isocode = $this->getTicketIsoCodeByTicketID($ticketID);

        if ($newticket) {
            $sTICKETNOTIFYMAIL_ID = $this->getTicketMailItemIdByName("sTICKETNOTIFYMAILNEW", $isocode);
        } else {
            $sTICKETNOTIFYMAIL_ID = $this->getTicketMailItemIdByName("sTICKETNOTIFYMAILANS", $isocode);
        }

        if (!empty($this->sSYSTEM->sCONFIG["sTICKETNOTIFYEMAIL"]) && !empty($sTICKETNOTIFYMAIL_ID)) {
            $notifyTpl = $this->getTicketMailItem($sTICKETNOTIFYMAIL_ID, $ticketID);

            $mail = $this->sSYSTEM->sMailer;

            if (!$mail) die("PHPMAILER failure");
            $mail->IsHTML(1);


            $mail->From     = $notifyTpl["frommail"] ? $notifyTpl["frommail"] : $this->sSYSTEM->sCONFIG["sMAIL"];
            $mail->FromName = $notifyTpl["fromname"] ? $notifyTpl["fromname"] : $this->sSYSTEM->sCONFIG["sSHOPNAME"];


            $mail->Subject  = $notifyTpl["subject"];

            if (empty($notifyTpl["ishtml"])) {
                $mail->Body = utf8_decode(nl2br($notifyTpl["content"]));
            } else {
                $mail->Body = $notifyTpl["contentHTML"];
            }


            $mail->ClearAddresses();

            $explMails = explode(";",  $this->sSYSTEM->sCONFIG["sTICKETNOTIFYEMAIL"]);
            foreach ($explMails as $explMail) {
                $mail->AddAddress($explMail, "");
            }

            $mail->Send();
        }

        //notify costumer
        if (!empty($this->sSYSTEM->sCONFIG["sTICKETNOTIFYMAILCOSTUMER"]) && $newticket == true) {
            //Fetch Ticket Details
            $ticketData = $this->getTicketSupportById($ticketID);

            //Fetch mail template
            $sTICKETNOTIFYMAIL_ID = $this->getTicketMailItemIdByName("sTICKETNOTIFYMAILCOSTUMER", $isocode);

            $notifyTpl = $this->getTicketMailItem($sTICKETNOTIFYMAIL_ID, $ticketID);

            $mail = $this->sSYSTEM->sMailer;

            if (!$mail) die("PHPMAILER failure");
            $mail->IsHTML(1);


            $mail->From     = $notifyTpl["frommail"] ? $notifyTpl["frommail"] : $this->sSYSTEM->sCONFIG["sMAIL"];
            $mail->FromName = $notifyTpl["fromname"] ? $notifyTpl["fromname"] : $this->sSYSTEM->sCONFIG["sSHOPNAME"];


            $mail->Subject  = $notifyTpl["subject"];

            if (empty($notifyTpl["ishtml"])) {
                $mail->Body = utf8_decode(nl2br($notifyTpl["content"]));
            } else {
                $mail->Body = $notifyTpl["contentHTML"];
            }

            $ticketData['ticket_email'] = $ticketData['ticket_email'] ? $ticketData['ticket_email'] : $this->sSYSTEM->sCONFIG["sMAIL"];
            $mail->ClearAddresses();
            $mail->AddAddress($ticketData['ticket_email'], "");

            $mail->Send();
        }
    }

    public function getTicketMailItemIdByName($name, $isocode="de")
    {
        //Escape
        $fetch = $this->sSYSTEM->sDB_CONNECTION->GetAll("SELECT id FROM `s_ticket_support_mails` WHERE `name` =  ? AND `isocode` = ? LIMIT 1", array($name, $isocode));
        return $fetch[0]["id"];
    }

    public function getTicketIsoCodeByTicketID($ticketID)
    {
        //Escape
        $ticketID = intval($ticketID);

        $fetch = $this->sSYSTEM->sDB_CONNECTION->GetAll("SELECT `isocode` FROM `s_ticket_support` WHERE `id` = '{$ticketID}' LIMIT 1");
        return $fetch[0]["isocode"];
    }
}
?>
