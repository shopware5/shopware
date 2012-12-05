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
 * @package    Shopware_Plugins
 * @subpackage Staging
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     $Author$
 */
use   Shopware\CustomModels\Staging\Tables as Tables,
Shopware\CustomModels\Staging\Jobs as Jobs,
Shopware\CustomModels\Staging\Profiles as Profile,
Shopware\CustomModels\Staging\Columns as Column;

/**
 * Backend controller for staging plugin
 */
class Shopware_Controllers_Backend_Staging extends Shopware_Controllers_Backend_ExtJs
{
    /**
     * this function is called initially and extends the standard template directory
     * @return void
     */
    public function init()
    {
        $this->View()->addTemplateDir(dirname(__FILE__) . "/../../Views/");
        $stagingConfig = Shopware()->getOption('custom');

        if (!empty($stagingConfig["is_staging"])){
            $this->View()->assign('system','staging');
        }else {
            $this->View()->assign('system','master');
        }
        // Disable mysql foreign keys check (constrains)
        Shopware()->Db()->query("SET FOREIGN_KEY_CHECKS = 0");
        parent::init();
    }

    /**
     * Return empty / null date for doctrine models
     * @return null
     */
    protected function getEmptyDate(){
        return NULL;
    }

    /**
     * Restore a existing backup in master-system
     * @throws Exception
     */
    public function restoreBackupAction(){
        //bck_staging_stat
        $masterDb = Shopware()->getOption("custom");
        $masterDb = $masterDb["master_database"];
        if (empty($masterDb)){
            throw new Exception("Staging configuration: master database is not defined");
        }

        $backupTables = Shopware()->Db()->fetchAll("SELECT TABLE_NAME AS `table`
        FROM information_schema.tables
        WHERE table_schema = ?
        AND table_name LIKE 'bck_%'
        AND table_name != 'bck_staging_stat'
        ",array($masterDb));

        foreach ($backupTables as $table){
            // Delete production table
            $productionTable = str_replace("bck_","",$table["table"]);
            $productionTable = $masterDb.".".$productionTable;
            Shopware()->Db()->query("DROP TABLE IF EXISTS $productionTable");

            // Rename backup-table to production table
            $backupTable = $masterDb.".".$table["table"];
            Shopware()->Db()->query("
            RENAME TABLE $backupTable TO $productionTable
            ");
        }

        // Drop status-table
        $table = $masterDb."."."bck_staging_stat";
        Shopware()->Db()->query("DROP TABLE IF EXISTS $table");

        $this->View()->assign(array(
            'success' => true,
            'data' => array(),
            'testStatus' => '',
            'message' => '')
        );
    }

    /**
     * Delete all backup tables (bck_*) from master-database
     * @throws Exception
     */
    public function deleteBackupAction(){
        $masterDb = Shopware()->getOption("custom");
        $masterDb = $masterDb["master_database"];
        if (empty($masterDb)){
            throw new Exception("Staging configuration: master database is not defined");
        }

        $backupTables = Shopware()->Db()->fetchAll("SELECT TABLE_NAME AS `table`
        FROM information_schema.tables
        WHERE table_schema = ?
        AND table_name LIKE 'bck_%'",array($masterDb));
        foreach ($backupTables as $table){
            $table = $masterDb.".".$table["table"];
            Shopware()->Db()->query("DROP TABLE IF EXISTS $table");
        }

        $this->View()->assign(array(
            'success' => true,
            'data' => array(),
            'testStatus' => '',
            'message' => '')
        );
    }

    /**
     * Reset a job completely, so that it can executed again
     * @throws Exception
     */
    public function resetJobAction(){
        $jobId = $this->Request()->getParam("jobId");
        if (empty($jobId)){
            throw new Exception("No job selected");
        }

        $model = Shopware()->Models()->getRepository('Shopware\CustomModels\Staging\Jobs');
        $job = $model->find($jobId);

        $params["createDate"] = new \DateTime();
        $params["startDate"] = $this->getEmptyDate();
        $params["endDate"] = $this->getEmptyDate();
        $params["running"] = 0;
        $params["jobsTotal"] = 0;
        $params["jobsCurrent"] = 0;
        $params["successful"] = 0;

        $job->fromArray($params);
        Shopware()->Models()->persist($job);
        Shopware()->Models()->flush();

        // Delete existing queue
        $model->deleteJobQueue($jobId)->getQuery()->execute(array());


        // Create new queue
        $staging = new Shopware_Components_Staging();
        $staging->setDatabase(Shopware()->Db());
        $staging->setJobId($jobId);
        $staging->prepareQueue();

        $this->View()->assign(array(
            'success' => true,
            'data' => array(),
            'testStatus' => '',
            'message' => '')
        );

    }

    /**
     * Check if staging plugin was configured correctly
     */
    public function getTestResultsAction(){
        $data = array();
        $allTestsOkay = false;

        // Test #1 - Check if config_staging.php exists
        $status = file_exists(Shopware()->OldPath()."/config_staging.php");
        $data[] = array("test"=>"Check if ".Shopware()->OldPath()."config_staging.php exists","status" => $status);

        // Test #2 - Check if .htaccess modifications are done
        $status = strpos(file_get_contents(Shopware()->OldPath()."/.htaccess"),"SetEnvIf Host") !== false ? 1 : 0;
        $data[] = array("test"=>"Check if ".Shopware()->OldPath().".htaccess considers staging rules","status" => $status);

        // Test #3 - Check staging-config
        $stagingConfig = Shopware()->getOption("custom");
        $stagingHost = $stagingConfig["staging_url"];
        $stagingDatabase = $stagingConfig["staging_database"];
        $masterDatabase = $stagingConfig["master_database"];

        $data[] = array("test"=>"Check if shop config includes staging_url","status" => !empty($stagingHost) ? true : false);
        $data[] = array("test"=>"Check if shop config includes staging_database","status" => !empty($stagingDatabase) ? true : false);
        $data[] = array("test"=>"Check if shop config includes master_database","status" => !empty($masterDatabase) ? true : false);
        $data[] = array("test"=>"Check if master database is not equal staging database","status" => $stagingDatabase != $masterDatabase ? true : false);

        // Test #4 - Check if staging kernel exists
        /*$status = file_exists(Shopware()->OldPath()."/staging.php");
        $data[] = array("test"=>"Check if ".Shopware()->OldPath()."/staging.php (Staging Bootstrap) exists","status" => $status);
        */
        // Test #5 - Check database data
        $dbConfig = Shopware()->getOption("db");
        $username = $dbConfig["username"];
        $password = $dbConfig["password"];
        $host = $dbConfig["host"];
        $port = $dbConfig["port"] ? $dbConfig["port"] : 3306;

        // Check staging db
        $stageDbTestStatus = true;
        $exception = '';
        try {
           $tempDb = new PDO("mysql:host=$host;port=$port;dbname=$stagingDatabase", $username, $password);
           $tempDb->exec("SET CHARACTER SET utf8");
        } catch (PDOException $e) {
            $stageDbTestStatus = false;
            $exception = $e->getMessage();
        }
        $data[] = array("test"=>"Check if staging database is reachable ".$exception,"status" => !empty($stageDbTestStatus) ? true : false);

        // Check master db
        $masterDbTestStatus = true;
        $exception = '';
        try {
           $tempDb = new PDO("mysql:host=$host;port=$port;dbname=$masterDatabase", $username, $password);
           $tempDb->exec("SET CHARACTER SET utf8");
        } catch (PDOException $e) {
            $masterDbTestStatus = false;
            $exception = $e->getMessage();
        }
        $data[] = array("test"=>"Check if master database is reachable ".$exception,"status" => !empty($masterDbTestStatus) ? true : false);

        // Check privileges on database

        $privilegeCheck = true;
        $exception = '';
        try  {
            // Create and drop table in slave
            Shopware()->Db()->query("
            CREATE TABLE IF NOT EXISTS $stagingDatabase.`a` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `test` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=5 ;");

            Shopware()->Db()->query("
            DROP TABLE IF EXISTS $stagingDatabase.`a`;
            ");
        } catch (Zend_Db_Exception $e) {
            $privilegeCheck = false;
            $exception = $e->getMessage();
        }
        $data[] = array("test"=>"Check if user has privileges ".$exception,"status" => !empty($privilegeCheck) ? true : false);


        $status = file_exists(Shopware()->OldPath()."/staging/cache/database");
        $data[] = array("test"=>"Check if ".Shopware()->OldPath()."/staging/cache/database exists","status" => $status);

        $status = file_exists(Shopware()->OldPath()."/staging/cache/templates");
        $data[] = array("test"=>"Check if ".Shopware()->OldPath()."/staging/cache/templates exists","status" => $status);

        $status = file_exists(Shopware()->AppPath()."/ProxiesStaging");
        $data[] = array("test"=>"Check if ".Shopware()->AppPath()."/ProxiesStaging exists","status" => $status);

        $allTestsOkay = true;

        $this->View()->assign(array(
            'success' => true,
            'data' => $data,
            'testStatus' => $allTestsOkay,
            'message' => '')
        );
    }

    /**
     * index action is called if no other action is triggered
     * @return void
     */
    public function indexAction()
    {
        /**
         * @var StagingConfig
         */
        //$model = new StagingConfigModel();


        $this->View()->loadTemplate("backend/staging/app.js");
    }

    /**
     * Load configuration from s_plugin_staging_config
     */
    public function loadConfigAction(){
        $model = Shopware()->Models()->getRepository('Shopware\CustomModels\Staging\Config');
        $a = $model->find(1);
        $this->View()->assign(array(
            'success' => true,
            'data' => Shopware()->Models()->toArray($a),
            'message' => '')
        );
    }

    /**
     * Save configuration to s_plugin_staging_config
     */
    public function saveConfigAction(){
        $fields = $this->Request()->getParams();
        $model = Shopware()->Models()->getRepository('Shopware\CustomModels\Staging\Config');
        if (!empty($id)) {
           $config = $model->find($id);
        } else {
           $config = new Config();
        }
        $config->fromArray($fields);
        Shopware()->Models()->persist($config);
        Shopware()->Models()->flush();
    }

    /**
     * Get all tables that are configured in a certain profile
     */
    public function getTablesAction(){
        /**@var $repository \Shopware\CustomModels\Staging\Repository*/
        $profileId = $this->Request()->getParam("profileId");
        $filter = $this->Request()->getParam("filtertable");

        if (empty($profileId)){
            throw new Exception("Missing profileId");
        }
        $model = Shopware()->Models()->getRepository('Shopware\CustomModels\Staging\Tables');
        $data = $model->getListQueryBuilder($profileId,$filter);
        $data = $data->getQuery()->getArrayResult();

        $this->View()->assign(array(
            'success' => true,
            'data' => $data,
            'message' => '')
        );
    }

    /**
     * Get all columns that belongs to a certain table
     * Get all columns that are selected
     * @throws Exception
     */
    public function getTableColumnsAction(){
        $tableId = $this->Request()->getParam('tableId');
        $tableName = $this->Request()->getParam("tableName");

        if (empty($tableId)){
            throw new Exception("Empty table id");
        }
        $model = Shopware()->Models()->getRepository('Shopware\CustomModels\Staging\Columns');
        $cols = $model->findByTableId($tableId);
        $cols = Shopware()->Models()->toArray($cols);
        // Get all columns from table
        $sql = "SHOW COLUMNS FROM $tableName ";
        $getAllCols = Shopware()->Db()->fetchAll($sql);
        $resultCols = array();
        foreach ($getAllCols as $column){
            if ($column["Key"] == "PRI") continue;// Ignore primary keys
            $name = $column["Field"];
            $resultCols[$name] = array("id"=>$name,"name" => $name,"checked"=>false);

        }
        foreach ($cols as $selectedColumns){
            $resultCols[$selectedColumns["column"]]["id"] = $selectedColumns["id"];
            $resultCols[$selectedColumns["column"]]["checked"] = true;
        }
        $resultCols = array_values($resultCols);

        $this->View()->assign(array(
            'success' => true,
            'data' => $resultCols,
            'message' => '')
        );
    }

    /**
     * Update profile table <> column assignation
     * @throws Exception
     */
    public function updateTableColumnsAction(){
        $tableId = $this->Request()->getParam("tableId");
        if (empty($tableId)){
            throw new Exception("tableId is missing");
        }
        $columns = $this->Request()->getParam("selectedColumns");
        if (!empty($columns)){
            $columns = json_decode($columns);
        }

        // First remove all entries for table
        $model = Shopware()->Models()->getRepository('Shopware\CustomModels\Staging\Columns');
        $model->deleteColumnAssignments($tableId);

        foreach ($columns as $column){
            $columnModel = new Column();
            $columnModel->fromArray(array("column"=>$column->name,"tableId"=>$tableId));
            Shopware()->Models()->persist($columnModel);
            Shopware()->Models()->flush();
        }
        $this->View()->assign(array(
            'success' => true,
            'data' => array(),
            'message' => '')
        );
    }

    /**
     * Synchronize all tables from master to configure them in staging administration
     */
    public function syncTablesAction(){
        $profileId = $this->Request()->getParam("profileId");

        if (empty($profileId)){
           throw new Exception("Missing profileId");
        }

        $stagingConfig = Shopware()->getOption("custom");
        $stagingDatabaseMaster = $stagingConfig["master_database"];

        $model = Shopware()->Models()->getRepository('Shopware\CustomModels\Staging\Tables');

        $sql= "SELECT DISTINCT TABLE_NAME as name FROM `information_schema`.`TABLES` WHERE `TABLE_SCHEMA` = ? ORDER BY TABLE_NAME";
        $getTables = Shopware()->Db()->fetchAll($sql,array($stagingDatabaseMaster));
        foreach ($getTables as $table){
            // Ignore backup tables
            if (strpos($table["name"],"bck_")!==false) continue;
            // Ignore temp tables
            if (strpos($table["name"],"temp_")!==false) continue;

            $table = $table["name"];
            if ($model->findBy(array('tableName'=>$table,'profileId'=>$profileId))) continue; // Don´t import tables twice
            $tableModel = new Tables();
            $tableModel->fromArray(array("profileId"=>$profileId,"tableName"=>$table,"strategy"=>"replicate"));
            Shopware()->Models()->persist($tableModel);
        }
        Shopware()->Models()->flush();
        $this->View()->assign(array(
            'success' => true,
            'data' => array(),
            'message' => '')
        );
    }

    /**
     * Remove all backup-tables (tables that starts with bck_ in name) from master database
     */
    public function deleteBackupTablesAction(){
        $stagingConfig = Shopware()->getOption("custom");
        $stagingDatabaseMaster = $stagingConfig["master_database"];
        $sql= "SELECT DISTINCT TABLE_NAME as name FROM `information_schema`.`TABLES` WHERE `TABLE_SCHEMA` = ? ORDER BY TABLE_NAME";
        $getTables = Shopware()->Db()->fetchAll($sql,array($stagingDatabaseMaster));
        foreach ($getTables as $table){
             // Ignore backup tables
             if (strpos($table["name"],"bck_")!==false){
                 // Delete Backup
                    $sql = "DROP TABLE IF EXISTS $stagingDatabaseMaster.{$table["name"]};";
                    Shopware()->Db()->query($sql);
             }
        }
        $this->View()->assign(array(
           'success' => true,
           'data' => array(),
           'message' => '')
        );
    }

    /**
     * Update table properties
     */
    public function updateTableAction(){
        $params = $this->Request()->getParams();

        $model = Shopware()->Models()->getRepository('Shopware\CustomModels\Staging\Tables');

       // if (!empty($params["strategy"])) $params["selectedStrategy"] = $params["strategy"];
       // if (!empty($params["tableName"])) $params["tables"][] = $params["tableName"];

        if($params['tables']) {
        	$tables = json_decode($params['tables']);

	        foreach($tables as $table) {
	        	$modelData = array(
	        		'id' => $table->id,
	        		'tableName' => $table->tableName,
	        		'strategy' => $params['selectedStrategy']
	        	);
		        $tableModel = $model->find($modelData['id']);
		        $tableModel->fromArray($modelData);
		        Shopware()->Models()->persist($tableModel);
		        Shopware()->Models()->flush();
	        }
        }elseif ($params["tableName"]){
            $modelData = array(
                'id' => $params["id"],
                'tableName' => $params["tableName"],
                'strategy' => $params['strategy']
            );
            $tableModel = $model->find($modelData['id']);
            $tableModel->fromArray($modelData);
            Shopware()->Models()->persist($tableModel);
            Shopware()->Models()->flush();
        }
        $this->View()->assign(array(
           'success' => true,
           'data' => $params,
           'message' => '')
        );
    }

    /**
     * Create a job
     */
    public function createJobAction(){
        $jobs = new Jobs();

        $params = $this->Request()->getParams();
        if (empty($params)){
           throw new Exception("ProfileId missing");
        }

        $params["createDate"] = new \DateTime();
        //$params["startDate"] = $this->getEmptyDate();
        //$params["endDate"] = $this->getEmptyDate();
        $identity = Shopware()->Auth()->getIdentity();
        if (!$identity->username){
            throw new Exception("Unknown identity");
        }
        $params["user"] = $identity->username;

        $params["running"] = 0;
        $params["jobsTotal"] = 0;
        $params["jobsCurrent"] = 0;
        $params["successful"] = 0;
        $params["errorMsg"] = "";

        $jobType = $this->Request()->getParam("profileType");

        $params["description"] = $params["profileText"];

        $jobs->fromArray($params);
        Shopware()->Models()->persist($jobs);
        Shopware()->Models()->flush();

        $jobId = $jobs->getId();
        // Preparing queue
        $staging = new Shopware_Components_Staging();
        $staging->setDatabase(Shopware()->Db());
        $staging->setJobId($jobId);
        $staging->prepareQueue();


        $this->View()->assign(array(
           'success' => true,
           'data' => array(),
           'message' => '')
        );

    }

    /**
     * Delete a job
     */
    public function deleteJobAction(){
        $id = $this->Request()->getParam("id");
        $model = Shopware()->Models()->getRepository('Shopware\CustomModels\Staging\Jobs');
        $model = $model->find($id);
        Shopware()->Models()->remove($model);
        Shopware()->Models()->flush();
        $this->View()->assign(array(
            'success' => true,
            'data' => array(),
            'message' => '')
        );
    }

    /**
     * Get all jobs from
     */
    public function getJobsAction(){
        $model = Shopware()->Models()->getRepository('Shopware\CustomModels\Staging\Jobs');
        $data = $model->getJobsQueryBuilder();
        $data = $data->getQuery()->getArrayResult();

        $result = array();

        foreach ($data as $row){
            $profileAssignment = $row["profileAssignment"];
            $row = $row[0];
            $result[] = array(
                "text" => $row["description"]." (Id:".$row["id"].")",
                "leaf" => true,
                "id" => $row["id"],
                "iconCls" => $profileAssignment == "master" ? "sprite-applications" : "sprite-applications-blue"
            );
        }

        $this->View()->assign(array(
            'success' => true,
            'data' => $result,
            'message' => '')
        );
    }

    /**
     * Get all data for a certain job
     */
    public function getJobAction(){
        $id = $this->Request()->getParam("id");
        if (!$id){
            throw new Exception("jobId missing");
        }
        $model = Shopware()->Models()->getRepository('Shopware\CustomModels\Staging\Jobs');
        $data = $model->find($id);
        $data = Shopware()->Models()->toArray($data);

        $profileModel = Shopware()->Models()->getRepository('Shopware\CustomModels\Staging\Profiles');
        $profileData = $profileModel->find($data["profileId"]);
        $profileData = Shopware()->Models()->toArray($profileData);

        // Reset backup parameters
        $data["profileassignment"] = $profileData["profileAssignment"];
        $data["backupExistsForThisJob"] = false;
        $data["backupExists"] = false;

        $jobId = $data["id"];
        // Check if job has an active backup, is this the case check if the backup is from this job
        $masterDb = Shopware()->getOption("custom");
        $masterDb = $masterDb["master_database"];
        if (empty($masterDb)){
            throw new Exception("Staging configuration: master database is not defined");
        }

        if (Shopware()->Db()->fetchOne("SELECT COUNT(*)
        FROM information_schema.tables
        WHERE table_schema = ?
        AND table_name = 'bck_staging_stat'",array($masterDb)) && $data["profileassignment"] == "slave"){

            // Database has an active backup job
            $getBackupId = Shopware()->Db()->fetchOne("
            SELECT jobId FROM $masterDb".".bck_staging_stat");
            if ($getBackupId == $jobId){
                // Backup is from this job
                $data["backupExistsForThisJob"] = true;
                $data["backupTables"] = Shopware()->Db()->fetchAll("SELECT TABLE_NAME AS `table`
                FROM information_schema.tables
                WHERE table_schema = ?
                AND table_name LIKE 'bck_%'",array($masterDb));
            }
            $data["backupExists"] = $getBackupId;
        }

        $this->View()->assign(array(
            'success' => true,
            'data' => $data,
            'message' => '')
        );
    }

    /**
     * Get the queue for a certain job
     */
    public function getJobQueueAction(){
        $id = $this->Request()->getParam("id");
        $model = Shopware()->Models()->getRepository('Shopware\CustomModels\Staging\Queue');
        $data = $model->getQueueQueryBuilder($id);
        $data = $data->getQuery()->getArrayResult();
        $i = 0;
        foreach ($data as &$row){
            $i++;
            $row["position"] = $i;
        }
        $this->View()->assign(array(
            'success' => true,
            'data' => $data,
            'message' => '')
        );
    }

    /**
     * Start execution of a job
     */
    public function executeJobAction(){
        try {

        $jobId = $this->Request()->getParam("jobId");
        if (empty($jobId)){
            throw new Exception("Empty jobId");
        }
        $limit = $this->Request()->getParam("limit");

        if (empty($limit)) $limit = 5;

        $staging = new Shopware_Components_Staging();

        $staging->setDatabase(Shopware()->Db());
        $staging->setJobId($jobId);

        $data = $staging->processQueue($limit);


        /*
         * - First get current queue state
         * - If queue is done, set job properties to done and return
         * - If queue is not done, progress with queue
        */
        //'done', 'totalQueueJobs','doneQueueJobs','jobId','offset'
        // Get current job state
        $this->View()->assign(array(
           'success' => true,
           'data' => $data,
           'message' => '')
       );
        } catch (Exception $e){
            $this->View()->assign(array(
                       'success' => false,
                       'data' => $e->getMessage(),
                       'message' => $e->getMessage())
                   );
        }

    }

    /**
     * Get all profiles for master / staging system
     */
    public function getProfilesAction(){
        $filter = $this->Request()->getParam("profileFilter");

        $model = Shopware()->Models()->getRepository('Shopware\CustomModels\Staging\Profiles');
        $data = $model->getProfilesQueryBuilder($filter);
        $data = $data->getQuery()->getArrayResult();
        foreach ($data as &$row){
            $row["text"] = $row["profileText"]." (".$row["profileAssignment"].")";
            $row["leaf"] = true;
            $row["iconCls"] = $row["profileAssignment"] == "master" ? "sprite-applications" : "sprite-applications-blue";
        }
        $this->View()->assign(array(
            'success' => true,
            'data' => $data,
            'message' => '')
        );
    }

    /**
     * Get a certain profile
     */
    public function getProfileAction(){
        $id = $this->Request()->getParam("id");
        $model = Shopware()->Models()->getRepository('Shopware\CustomModels\Staging\Profiles');
        $data = $model->find($id);
        $data = Shopware()->Models()->toArray($data);
        $this->View()->assign(array(
            'success' => true,
            'data' => $data,
            'message' => '')
        );
    }

    /**
     * Update a profile
     */
    public function updateProfileAction(){
        $id = $this->Request()->getParam("id");
        if (!empty($id)){
            $model = Shopware()->Models()->getRepository('Shopware\CustomModels\Staging\Profiles')->find($id);
        }else {
            $model = new Profile();
        }
        $model->fromArray($this->Request()->getParams());
        Shopware()->Models()->persist($model);
        Shopware()->Models()->flush();
        $this->View()->assign(array(
           'success' => true,
           'data' => array(Shopware()->Models()->toArray($model)),
           'message' => '')
        );
    }

    /**
     * Delete a profile
     */
    public function deleteProfileAction(){
        $id = $this->Request()->getParam("id");
        $model = Shopware()->Models()->getRepository('Shopware\CustomModels\Staging\Profiles');
        $model = $model->find($id);
        Shopware()->Models()->remove($model);
        Shopware()->Models()->flush();
        $this->View()->assign(array(
            'success' => true,
            'data' => array(),
            'message' => '')
        );
    }

}
