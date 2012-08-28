<?php
/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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
 *
 * @category   Shopware
 * @package    Shopware_Controllers
 * @subpackage ErrorReporter
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Stephan Pohl
 * @author     $Author$
 */

/**
 * Shopware Backend Error Reporter
 *
 * This controller handles the client side error
 * reporter and saves the errors in the database.
 *
 * Shopware()->Models()->ErrorReporter()->getAll();
 * Shopware()->Models()->ErrorReporter()->getById($id);
 * Shopware()->Models()->ErrorReporter()->remove(Model);
 * Shopware()->Models()->ErrorReporter()->save();
 */
class Shopware_Controllers_Backend_ErrorReporter extends Enlight_Controller_Action
{

    /**
     * Holds the instance of the model manager
     *
     * @var
     * @private
     */
    protected $manager = null;

    /**
     * Initialized the controller and sets the correct renderer.
     *
     * @return void
     */
    public function init() {
        $this->Front()->Plugins()->ScriptRenderer()->setRender();

        // Prepare incoming data
        if($this->Request()->isPost() && !count($this->Request()->getPost())) {
            $data = file_get_contents('php://input');
            $data = Zend_Json::decode($data);
            // Remove null values because Zend_Request may crash
            foreach((array) $data as $key => $value) {
                if($value !== null) {
                    $this->Request()->setPost($key, $value);
                }
            }
        }

    }

    /**
     * Prevents the view to render a template
     *
     * @return void
     */
    public function preDispatch() {
        if(!in_array($this->Request()->getActionName(), array('index', 'load'))) {
            Shopware()->Plugins()->Controller()->ViewRenderer()->setNoRender();
        }
    }

    /**
     * Loads the neccessary template files which are needed
     * for the backend module.
     *
     * The method provides auto-magic and needs only the method definition.
     *
     * @return void
     */
    public function loadAction() { }

    /**
     * Loads the app.js and resolves the depencencies.
     *
     * The method provides auto-magic and needs only the method definition.
     *
     * @return void
     */
    public function indexAction() { }

    /**
     * Requests the saved error messages from the database
     * and assigns them to the view.
     *
     * @return void
     */
    public function getAction() {
        $result = Shopware()->Models()->toArray(Shopware()->Models()->ErrorReporter()->findAll());

        foreach($result as $key => $error) {
            $date = $error["created"];
            $error["created"] = $date->format('d.m.Y');
            $result[$key] = $error;
        }
        echo Zend_Json::encode(array('success' => true, 'data' => $result, 'count' => count($result)));
    }

    /**
     * Saves a new stack of error messages
     * into the database.
     *
     * @return boolean
     */
    public function saveAction() {

        $request = $this->Request();

        if(!$request->isPost() || !$request->isXmlHttpRequest()) {
            echo Zend_Json::encode(array('success' => false));
            return false;
        }
        $model = new \Shopware\Models\ErrorReporter\ErrorReporter();

        $model->setCreated(new DateTime('now', new DateTimeZone("Europe/London")));
        $model->setMessage($request->message);
        $model->setFileName($request->filename);
        $model->setLineNumber($request->linenumber);

        try {
            Shopware()->Models()->persist($model);
            Shopware()->Models()->flush();
        } catch(Exception $e) {
            echo Zend_Json::encode(array('success' => false));
            return false;
        }

        echo Zend_Json::encode(array('success' => true, 'data' => $model->toArray()));
        return true;
    }

    /**
     * Deletes the whole error messages stack from
     * the database.
     *
     * @return bool
     */
    public function deleteAction() {

        if(!$this->Request()->isXmlHttpRequest()) {
            echo Zend_Json::encode(array('success' => false));
            return false;
        }
        $query = Shopware()->Models()->createQuery("DELETE FROM \Shopware\Models\ErrorReporter\ErrorReporter");
        $query->execute();

        return true;
    }
}