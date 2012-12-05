<?php
/**
 *
 * This controller is used to fetch commit messages from github
 * to provide a relation between tickets and commits
 *
 * @copyright Copyright (c) 2011, Shopware AG
 * @author d.scharfenberg
 * @author $Author$
 * @package Shopware
 * @subpackage Controllers_Frontend
 * @creation_date 16.05.12 12:33
 * @version $Id$
 */
class Shopware_Controllers_Frontend_Github extends Enlight_Controller_Action
{

    protected $githubApi = "https://api.github.com/";
    protected $githubUser = "ShopwareAG";
    protected $githubRepo = "shopware-4";


    protected $count;

    /**
     * Get a full list of commits
     */
    public function indexAction()
    {

        $this->View()->setTemplate();
        $url = "";
        for ($i=1;$i<=2;$i++){
            $result = $this->getGithubCommits($url);
            $this->syncCommitsWithDatabase($result);
            if (!empty($result["paging"]["next"])){
                $url = $result["paging"]["next"];
            }else {
                break;
            }
        }

        echo $this->count." commits processed";
        //print_r($result);

    }

    /**
     * Loop through commit objects and sync them with database
     * @param array $results
     */
    protected function syncCommitsWithDatabase(array $results){
        foreach ($results["commits"] as $commit){
            $this->count++;
            $url = $commit->commit->url;
            $author = $commit->commit->author->name;
            $message = $commit->commit->message;
            $date = date("Y-m-d H:i:s",strtotime($commit->commit->author->date));

            Shopware()->Db()->query("
            INSERT IGNORE INTO swag_bug_github (`date`,author,message,url)
            VALUES (?,?,?,?)
            ",array(
                $date,$author,$message,$url
            ));
        }
    }

    /**
     * Get a list of github commits
     * @param string $url
     * @return array
     */
    protected function getGithubCommits($url = ""){
        if (empty($url)){
            $url = $this->githubApi."repos/".$this->githubUser."/".$this->githubRepo."/commits";
        }
        $client = new Zend_Http_Client($url);
        $response = $client->request();
        $body = $response->getBody();
        $linkProperties = $this->getProperLinkArray($response->getHeader("link"));
        return array(
          "commits" => json_decode($body),
          "paging" => $linkProperties
        );
    }

    /**
     * Parse github link header to get pagination working
     * @param $linkString
     * @return array
     */
    protected function getProperLinkArray($linkString){
        //<https://api.github.com/repos/ShopwareAG/shopware-4/commits?last_sha=1dde4f0dbf1711f9e819c2a9aca518ef2d09d233&top=master>; rel="next", <https://api.github.com/repos/ShopwareAG/shopware-4/commits?sha=master>; rel="first"
        $result = array();
        preg_match_all("/\<(.*)\>\; rel\=\"(.*)\"/Uis",$linkString,$result);
        return array(
          'next' => strpos($result[1][0],"last_sha")!==false ? $result[1][0] : false,
          'first' => isset($result[1][1]) ? $result[1][1] : false
        );
    }


}