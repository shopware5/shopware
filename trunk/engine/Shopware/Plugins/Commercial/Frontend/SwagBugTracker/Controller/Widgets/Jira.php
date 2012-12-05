<?php
use \Shopware\Components\Jira\API\Model\Query;

/**
 * This controller manages the logic of the bug tracker
 * The bug tracker communicates with the local jira instance of shopware.
 * Over the bug tracker external users can view the tasks of shopware and has
 * the possibility to create new tickets and proposals
 *
 * @copyright Copyright (c) 2011, Shopware AG
 * @author d.scharfenberg
 * @author $Author$
 * @package Shopware
 * @subpackage Controllers_Frontend
 * @creation_date 16.05.12 12:33
 * @version $Id$
 */
class Shopware_Controllers_Widgets_Jira extends Enlight_Controller_Action
{
    /**
     * Holds the key of the current project (by default SW for Shopware)
     * @var string
     */
    protected $project_key = "SW";

    /**
     * Holds the id of the current version
     * Actually "4.0.0 Beta 2"
     * @var int
     */
    protected $project_version = 10207;

    protected $versions = array("");

    /**
     * Initializes the controller
     * - Starts the json renderer so that the view parameters
     *   will be returned as a json string
     */
    public function init()
    {
        $this->Front()->Plugins()->ScriptRenderer()->setRender();
        $this->Front()->Plugins()->JsonRequest()
             ->setParseInput()
             ->setParseParams(array('group', 'sort', 'filter'))
             ->setPadding($this->Request()->targetField);
    }

    /**
     * Enable json renderer for index / load action
     *
     * @return void
     */
    public function preDispatch()
    {
        if(in_array($this->Request()->getActionName(), array('getIssueList', 'addComment', 'addIssue', 'getIssueComments'))) {
            $this->Front()->Plugins()->Json()->setRenderer();
        }
    }

    /**
     * Needs to be present for the script renderer
     */
    public function indexAction()
    {
        //Checks if the request parameter viewport is set
        //In this case set the analog template parameter
        //to start the bug tracker as a viewport application
        if(isset($this->Request()->viewport)) {
            //$this->View()->viewport = true;
        }
        if (isset($this->Request()->ticket)){
            $this->View()->ticket = htmlentities($this->Request()->ticket,ENT_QUOTES);
        }
        if (isset($this->Request()->version)){
            $this->View()->version = htmlentities($this->Request()->version,ENT_QUOTES);
        }

    }

    /**
     * Needs to be present for the script renderer
     */
    public function loadAction()
    {
        //Checks if the request parameter viewport is set
        //In this case set the analog template parameter
        //to start the bug tracker as a viewport application
        if(isset($this->Request()->viewport)) {
            $this->View()->viewport = true;
        }
    }

    public function getIssueDetailsAction(){
       if (!$this->Request()->issueKey){
           return;
       }
       $this->Front()->Plugins()->Json()->setRenderer();

       $this->View()->success = false;
       $this->View()->data = array();

       $jira = Shopware()->Jira();

       $projectService = $jira->getProjectService();
       $issueService   = $jira->getIssueService();

       //Loads the shopware project
       $sw_project = current($projectService->loadByKeys($this->project_key));

       //Creates and configures a query object for the issue fetch
       $query = new Query();
       $query->setOffset(0);
       $query->setLength(1);



       //Loads the issues of the shopware project
       $searchResult = $issueService->loadByKey($this->Request()->issueKey);
       $issues = array($searchResult);
       $total = 1;

       //Creates a array of issues by using the search result object
       $data = array();
       if(!empty($total)) {
           foreach ($issues as $issue) {

            $versions = $issue->getVersions();
            $tempVersions = array();
            foreach ($versions as $version){
                $tempVersions[] = $version->getName();
            }


            $data[] = array(
                   'id'            => $issue->getId(),
                   'key'           => $issue->getKey(),
                   'name'          => $issue->getName(),
                   'description'   => nl2br($issue->getDescription()),
                   'type'          => $issue->getType(),
                   'priority'      => $issue->getPriority(),
                   'status'        => $issue->getStatus(),
                   'reporter'      => $issue->getReporter(),
                   'assignee'      => $issue->getAssignee(),
                   'createdAt'    => $issue->getCreatedAt(),
                   'modifiedAt'   => $issue->getModifiedAt(),
                   'versions'	   => implode(",",$tempVersions)
               );
           }
       }

       //Sets the result for the list element
       $this->View()->data = $data;
       $this->View()->success = true;
       $this->View()->total = $total;

    }
    /**
     * Loads and returns the jira issues for the extjs store component
     */
    public function getIssueListAction()
    {
        //Fetches the required request parameters
        $page =  intval($this->Request()->page);
        $limit = intval($this->Request()->limit);
        $sort = $this->Request()->sort;
        $search = $this->Request()->search;
        $versionFilter = $this->Request()->version;


        //Initializes the jira api services
        $jira = Shopware()->Jira();
        $projectService = $jira->getProjectService();
        $issueService   = $jira->getIssueService();

        $versionsArray = array("4.0.4" => "10403",
        "4.0.5" => "10412"
        );
        //$this->project_key = 10403;
        //Loads the shopware project
        $sw_project = current($projectService->loadByKeys($this->project_key));

        //Creates and configures a query object for the issue fetch
        $query = new Query();
        $query->setOffset($page-1);
        $query->setLength($limit);

        //Check if a sort value is given. In this case add a sort statement
        //to the search query
        if(!empty($sort[0])) {
            $sort = current($sort);

            //Pretty up the sort parameter of the query
            $sort = $jira->prettyUpSort($sort);

            $query->setOrderBy($sort['property']);
            $query->setOrderDir($sort['direction']);
        }

        //Add search
        if(!empty($search)) {
            $criterion = new Shopware\Components\Jira\API\Model\Query\Criterion\SearchText($search);
            $query->addCriterion($criterion);
        }

        if (!empty($versionFilter)){
            $criterion = new Shopware\Components\Jira\API\Model\Query\Criterion\FixVersion($versionFilter);
            $query->addCriterion($criterion);
        }

        //Loads the issues of the shopware project
        $searchResult = $issueService->loadIssues($sw_project, $query);
        $issues = $searchResult->getIssues();
        $total = $searchResult->getTotal();

        //Creates a array of issues by using the search result object
        $data = array();
        if(!empty($total)) {
            foreach ($issues as $issue) {
            	
            	$versions = $issue->getVersions();
            	$tempVersions = array();
            	foreach ($versions as $version){
            		$tempVersions[] = $version->getName();
            	}
            		
            
            	$data[] = array(
                    'id'            => $issue->getId(),
                    'key'           => $issue->getKey(),
                    'name'          => $issue->getName(),
                    'description'   => nl2br($issue->getDescription()),
                    'type'          => $issue->getType(),
                    'priority'      => $issue->getPriority(),
                    'status'        => $issue->getStatus(),
                    'reporter'      => $issue->getReporter(),
                    'assignee'      => $issue->getAssignee(),
                    'createdAt'    => $issue->getCreatedAt(),
                    'modifiedAt'   => $issue->getModifiedAt(),
                    'versions'	   => implode(",",$tempVersions)
                );
            }
        }

        //Sets the result for the list element
        $this->View()->data = $data;
        $this->View()->total = $total;
    }

    public function getIssueCommitsAction(){
        $this->Front()->Plugins()->Json()->setRenderer();

        $this->View()->success = false;
        $this->View()->data = array();

        $issueKey   = Shopware()->Db()->quote("%".$this->Request()->issueKey."%");

        $fetchIssues = Shopware()->Db()->fetchAll("
        SELECT date,author,message,url FROM swag_bug_github
        WHERE message LIKE $issueKey
        ");

        foreach ($fetchIssues as &$issue){
            $link = $issue["url"];
            // https://github.com/ShopwareAG/shopware-4/commit/fced8de9b7ef60402044cc749151ae0e56a7947b
            // https://api.github.com/repos/ShopwareAG/shopware-4/git/commits/fced8de9b7ef60402044cc749151ae0e56a7947b
            $link = str_replace("https://api.github.com/repos/ShopwareAG/shopware-4/git/commits/","",$link);
            $issue["url"] = "https://github.com/ShopwareAG/shopware-4/commit/".$link;
            $issue["date"] = date("d.m.Y H:i:s",strtotime($issue["date"]));
        }

        $this->View()->data = $fetchIssues;
        $this->View()->success = true;
    }
    /**
     * Loads and returns all comments of the the
     * given issue id
     */
    public function getIssueCommentsAction() {
        //Sets the success flag by default to false
        $this->View()->success = false;
        $this->View()->data = array();

        //Fetches the request parameters
        $issueId    = intval($this->Request()->issueId);
        $issueKey   = $this->Request()->issueKey;

        //Validates the parameters
        if(empty($issueId) ||empty($issueKey)) return;

        //Initializes the jira api services
        $jira = Shopware()->Jira();
        $issueService   = $jira->getIssueService();
        $commentService = $jira->getCommentService();

        //Loads the issue by using the given id
        $issue = $issueService->load($issueId);
        if(empty($issue)) return;
        //Checks if the given key matches with the issue key
        if($issueKey != $issue->getKey()) return;

        //Loads the comments of the issue
        $comments = $commentService->loadByIssue($issue);
        $this->View()->success = true;
        if(!empty($comments)) {
            $data = array();
            foreach($comments as $comment) {
                $createdAt = $comment->getCreatedAt();
                if($createdAt instanceof DateTime) {
                    $createdAt = $createdAt->format('d.m.Y - H:i');
                }

                $data[] = array(
                    'id' => $comment->getId(),
                    'author' => $comment->getAuthor(),
                    'createdAt' => $createdAt,
                    'description' => nl2br($comment->getDescription()),
                );
            }
        }

        $this->View()->data = $data;
        $this->View()->total = 0;
    }

    /**
     * Creates a new comment for an existed issue by
     * using the given request parameters
     */
    public function addCommentAction()
    {
        //Sets the success flag by default to false
        $this->View()->success = false;

        //Fetches the request parameters
        $issueId    = intval($this->Request()->issueId);
        $issueKey   = $this->Request()->issueKey;
        $name       = $this->Request()->name;

        //The request parameter "comment" will be fetch by using
        //the php input stream. So we bypass the request filter
        //plugin and get the html line breaks
        $comment = $this->getPhpStreamParam('comment');

        //Validates the parameters
        if(empty($issueId) ||empty($issueKey) ||empty($name) ||empty($comment)) return;

        //Initializes the jira api services
        $jira = Shopware()->Jira();
        $issueService   = $jira->getIssueService();
        $commentService = $jira->getCommentService();

        //Loads the issue by the given id and
        //checks if the given issue key matches
        $issue   = $issueService->load((int) $issueId);
        if(empty($issue)) return;
        if($issue->getKey() != $issueKey) return;

        //Adds the comment to the issue
        $commentCreate = $commentService->newCommentCreate($issue);
        $commentCreate->setAuthor($name);
        $commentCreate->setBody(str_replace('<br>', "\n", $comment));
        $commentService->create($commentCreate);

        //Sets the success flag
        $this->View()->success = true;
    }

    /**
     * Adds a new issue to the current project
     * by using the given request parameters
     *
     * @return mixed
     */
    public function addIssueAction()
    {
        //Sets the success flag by default to false
        $this->View()->success = false;

        //Fetches the request parameters
        $type   = intval($this->Request()->type);
        $author = $this->Request()->author;
        $name   = $this->Request()->name;
        $email  = $this->Request()->email;

        //The request parameter "description" will be fetch by using
        //the php input stream. So we bypass the request filter
        //plugin and get the html line breaks
        $description = $this->getPhpStreamParam('description');

        //Validates the parameters
        if(empty($type) || empty($author) || empty($email) || empty($name) || empty($description)) return;

        //$this->tempSendMail($type, $author, $name, $email, $description);

        //Initializes the jira api services
        $jira = Shopware()->Jira();
        $projectService = $jira->getProjectService();
        $issueService   = $jira->getIssueService();
        $versionService = $jira->getVersionService();

        //Loads the shopware project
        $sw_project = current($projectService->loadByKeys($this->project_key));

        $issueCreate = $issueService->newIssueCreate($sw_project);
        $issueCreate->setType($type);
        $issueCreate->setName($name);
        $issueCreate->setDescription(str_replace('<br>', "\n", $description));
        $issueCreate->setRemoteUser($author);
        $issueCreate->setRemoteEmail($email);
        $issueCreate->addVersion($versionService->load($this->project_version));
        $issue = $issueService->create($issueCreate);
        if(empty($issue)) return;
        $this->View()->issueId = $issue->getId();
        $this->View()->issueKey = $issue->getKey();


        $this->View()->success = true;
    }

    /**
     * Fetches the given parameter by using the php input stream
     *
     * @param $param string
     *  The name of the parameter which will be fetch
     * @return string
     *  The value of the given parameter
     */
    protected function getPhpStreamParam($param)
    {
        $value = '';

        $phpInput = explode('&', file_get_contents("php://input"));
        foreach($phpInput as $input) {
            $input = explode('=', $input);
            if($input[0] == $param) {
                $value = urldecode($input[1]);
            }
        }

        return $value;
    }

    /**
     * Temporary mail function  to send the new ticket
     * as am email to sth@shopware.de
     *
     * @param $type
     * @param $author
     * @param $name
     * @param $email
     * @param $description
     */
    protected function tempSendMail($type, $author, $name, $email, $description)
    {

        if($email == 'ds@shopware.de') {
            return;
        }
        
        $type = $type == 1 ? 'Bug' : 'Improvement';
        $body = sprintf("
            <style type='text/css'>
                table tr td {
                    padding: 5px;
                }
                .headline {
                    font-weight: bold;
                }
            </style>
            <table>
                <tr>
                    <td valign='top' class='headline'>Ticket-Typ:</td>
                    <td valign='top' class='content'>%s</td>
                </tr>
                <tr>
                    <td valign='top' class='headline'>Bezeichnung:</td>
                    <td valign='top' class='content'>%s</td>
                </tr>
                <tr>
                    <td valign='top' class='headline'>Kunde / Partner:</td>
                    <td valign='top' class='content'>%s</td>
                </tr>
                <tr>
                    <td valign='top' class='headline'>eMail:</td>
                    <td valign='top' class='content'>%s</td>
                </tr>
                <tr>
                    <td valign='top' class='headline'>Beschreibung:</td>
                    <td valign='top' class='content'>%s</td>
                </tr>
            </table>
        ", $type, $name, $author, $email, $description);

        $mailModel = new Shopware\Models\Mail\Mail();
        $mailModel->setName('Jira Beta Feedback');
        $mailModel->setFromMail($email);
        $mailModel->setFromName($author);
        $mailModel->setSubject('Jira-Feedback: ' . $name);
        $mailModel->setContentHtml($body);
        $mailModel->setIsHtml(true);

        $mail = Shopware()->TemplateMail()->createMail($mailModel, array());
        $mail->addTo('sth@shopware.de');
        $mail->send();
        die($body);
    }
}