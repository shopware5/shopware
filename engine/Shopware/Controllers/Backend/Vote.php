<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
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
 * Shopware Vote Controller
 *
 * This controller handles all actions made by the user in the premium module.
 * It reads out all votes, accepts/declines them or sets an answer.
 */
class Shopware_Controllers_Backend_Vote extends Shopware_Controllers_Backend_ExtJs
{
    /**
     * @var \Shopware\Models\Article\Repository
     */
    protected $articleRepository = null;

    /**
     * Helper function to get access to the article repository.
     * @return \Shopware\Models\Article\Repository
     */
    private function getArticleRepository()
    {
        if ($this->articleRepository === null) {
            $this->articleRepository = Shopware()->Models()->getRepository('Shopware\Models\Article\Article');
        }
        return $this->articleRepository;
    }

    public function initAcl()
    {
        $this->addAclPermission("getVotesAction", "read", "You're not allowed to see the articles.");
        $this->addAclPermission("deleteVoteAction", "delete", "You're not allowed to delete articles.");
    }

    /**
     * Disable template engine for all actions
     *
     * @return void
     */
    public function preDispatch()
    {
        if (!in_array($this->Request()->getActionName(), array('index', 'load'))) {
            $this->Front()->Plugins()->Json()->setRenderer(true);
        }
    }

    /*
     * Index action for the controller
     * @return void|string
     */
    public function indexAction()
    {
    }

    /**
     * Load action for the script renderer.
     */
    public function loadAction()
    {
    }


    /**
     * Function to get all votes with it's article and article-name
     * Also used for searching articles
     */
    public function getVotesAction()
    {
        $start = $this->Request()->get('start');
        $limit = $this->Request()->get('limit');

        //order data
        $order = (array)$this->Request()->getParam('sort', array());

        $filterValue = null;
        //filter from the search-field
        if ($this->Request()->get('filter')) {
            $filter = $this->Request()->get('filter');
            $filterValue = $filter[0]['value'];
        }

        $query = $this->getArticleRepository()->getVoteListQuery($filterValue, $start, $limit, $order);
        //total count for paging
        $totalResult = Shopware()->Models()->getQueryCount($query);
        $result = $query->getArrayResult();

        $this->View()->assign(array("success" => true, 'data' => $result, 'total' => $totalResult));
    }

    /**
     * Function to accept a vote by setting the active-value to 1
     * @return void
     */
    public function editVoteAction()
    {
        $params = $this->Request()->getParams();
        unset($params['module']);
        unset($params['controller']);
        unset($params['action']);
        unset($params['_dc']);

        if ($params[0]) {
            $data = array();
            foreach ($params as $values) {
                /**
                 * @var $vote \Shopware\Models\Article\Vote
                 */
                $voteModel = Shopware()->Models()->find('\Shopware\Models\Article\Vote', $values['id']);
                //unset because the datum-format is wrong
                unset($values['datum']);
                $date = get_object_vars($voteModel->getAnswerDate());

                //to prevent resetting an already set datum
                if (substr($date['date'], 0, 4) == "0000") {
                    //Set the datum of the answer manually
                    $voteModel->setAnswerDate($values['answer_datum']);
                }

                //<br> is set, when the WYSIWYG-Editor is empty
                //Delete the <br> then
                if ($values['answer'] == '<br>') {
                    $values['answer'] = '';
                }

                //Fill the model by using an array
                $voteModel->fromArray($values);
                //save model
                Shopware()->Models()->persist($voteModel);
                Shopware()->Models()->flush();

                $data[] = Shopware()->Models()->toArray($voteModel);
            }
        } else {
            /**
             * @var $vote \Shopware\Models\Article\Vote
             */
            $voteModel = Shopware()->Models()->find('\Shopware\Models\Article\Vote', $params['id']);
            //unset because the datum-format is wrong
            unset($params['datum']);
            $date = get_object_vars($voteModel->getAnswerDate());

            //to prevent resetting an already set datum
            if (substr($date['date'], 0, 4) == "0000") {
                //Set the datum of the answer manually
                $voteModel->setAnswerDate($params['answer_datum']);
            }

            //<br> is set, when the WYSIWYG-Editor is empty
            //Delete the <br> then
            if ($params['answer'] == '<br>') {
                $params['answer'] = '';
            }

            //Fill the model by using an array
            $voteModel->fromArray($params);
            //save model
            Shopware()->Models()->persist($voteModel);
            Shopware()->Models()->flush();

            $data = Shopware()->Models()->toArray($voteModel);
        }

        $this->View()->assign(array("success" => true, 'data' => $data));
    }

    /**
     * Function to delete a single or multiple votes.
     * This function is also called when deleting more than one vote at the same time
     * @return void
     */
    public function deleteVoteAction()
    {
        $params = $this->Request()->getParams();
        unset($params['module']);
        unset($params['controller']);
        unset($params['action']);
        unset($params['_dc']);

        if ($params[0]) {
            $data = array();
            foreach ($params as $values) {
                /**
                 * @var $vote \Shopware\Models\Article\Vote
                 */
                $voteModel = Shopware()->Models()->find('\Shopware\Models\Article\Vote', $values['id']);
                //delete model
                Shopware()->Models()->remove($voteModel);
                Shopware()->Models()->flush();
                $data[] = Shopware()->Models()->toArray($voteModel);
            }
        } else {
            /**
             * @var $vote \Shopware\Models\Article\Vote
             */
            $voteModel = Shopware()->Models()->find('\Shopware\Models\Article\Vote', $params['id']);
            //delete model
            Shopware()->Models()->remove($voteModel);
            Shopware()->Models()->flush();
            $data = Shopware()->Models()->toArray($voteModel);
        }

        $this->View()->assign(array("success" => true, 'data' => $data));
    }
}
