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
 * @author     Stefan Hamann
 */
use   Shopware\CustomModels\Staging\Tables as Tables,
Shopware\CustomModels\Staging\Queue as Queue;

set_time_limit(0);

/**
 * Shopware component which constitutes staging logic
 * @todo
 * 1.) Ignore bck_ tables in table synchronisation - OK
 * 2.) Delete bck_ tables optionally after finish a job OK
 * 3.) Give a warning notice if start a new staging > master job and backup tables are there
 * 4.) Reset job Button OK
 * 4.a) Restore from backup (?)
 * 5.) Proper error handling in job-methods - write exceptions in job-errormsg
 * 6.) Update job-grid when finishing a job OK
 * 7.) Give a MessageBox when a job is finished OK
 * 8.) Give a MessageBox when a job produced an error
 * 9.) Add template parameter to staging config OK
 * 10.) Fix .htaccess DirectoryIndex to work in staging-system OK
 * 11.) Define image / resource handling
 * 12.) Comment classes
 * 13.) Integrate licence check
 * 14.) Import 2 sample profiles (Backup/Restore)
 * 15.) Do configuration check on start OK
 * 16.) Test all workflows
 * 17.) Prevent user from start jobs in wrong scope OK
 * 18.) Proper duration display in job-view OK
 * 19.) Show icons for successful & failed jobs OK
 * 20.) Protocol slave-queue in more detail in job-description
 * 21.) Add acl & snippets
 * 22.) Use database parameters from config, instead of local static class properties OK
 */
class Shopware_Components_Staging implements Enlight_Hook {

    /**
     * Database-configuration master-system
     * @var array
     */
    protected $_master = array(
        'database' => ''
    );

    /**
     * Database-configuration staging-system
     * @var array
     */
    protected $_slave = array(
        'database' => ''
    );

    /**
     * Set master & staging configuration from shopware configuration
     * Throw error if configuration is not set properly
     */
    public function __construct(){
        $stagingConfig = Shopware()->getOption('custom');
        if (empty($stagingConfig["master_database"]) || empty($stagingConfig["staging_database"])){
            throw new Exception("Shopware_Components_Staging: Miss master_ or slave_ database definition");
        }
        $this->_master["database"] = $stagingConfig["master_database"];
        $this->_slave["database"] = $stagingConfig["staging_database"];

    }

    /**
     * Id of current job
     * @var
     */
    protected $_jobId;

    /**
     * Model of current job
     * @var Shopware_Models_Staging_Job
     */
    protected $_job;

    /**
     * Model of current profile
     * @var Shopware_Models_Staging_Profile
     */
    protected $_profile;

    /**
     * Configure replication direction
     * master_slave
     * slave_master
     * @var string
     */
    protected $_mode;


    /**
     * Database connection
     * @var Zend_Db_Adapter_Pdo_Mysql
     */
    protected $_database;

    /**
     * Set mode (Done automaticly)
     * @param $mode
     */
    public function setMode($mode){
        $this->_mode = $mode;
    }

    /**
     * Get current mode (master or slave)
     * @return string
     */
    public function getMode(){
        return $this->_mode;
    }

    /**
     * Set database connection
     * @param $database
     */
    public function setDatabase($database){
        $this->_database = $database;
    }

    /**
     * Get database connection
     * @return Zend_Db_Adapter_Pdo_Mysql
     */
    public function getDatabase(){
        return $this->_database;
    }

    /**
     * Prepare job-queue if a new job was created
     */
    public function prepareQueue(){
        if ($this->getMode()=="master"){
            $this->prepareQueueMasterSlave();
        }else {
            $this->prepareQueueSlaveMaster();
        }
    }

    /**
     * Work off through job queue
     * Create backup info-table in bck_staging_stat on slave > master synchronisation
     * Call all jobs that are defined in job-queue
     * @param $limit - max. jobs from queue that will be executed in one call
     * @return array
     * @throws Exception
     */
    public function processQueue($limit){
        $queueObject = Shopware()->Models()->getRepository('Shopware\CustomModels\Staging\Queue');

        $jobObject = $this->getJob();
        $jobConfiguration = Shopware()->Models()->toArray($jobObject);

        if (empty($jobConfiguration["id"])) throw new Exception("Unknown job exception");

        $profileModel = $this->getProfile();
        $profileModel = Shopware()->Models()->toArray($profileModel);
        $limit = !empty($profileModel["jobsPerRequest"]) ? $profileModel["jobsPerRequest"] : 5;
        if ($jobConfiguration["jobsCurrent"] == 0){

            if ($profileModel["profileAssignment"] == "slave"){
                // Create backup info table
                $tableName = $this->getMaster("database")."."."bck_staging_stat";
                // Delete old backup-profile-table
                Shopware()->Db()->query("
                DROP TABLE IF EXISTS $tableName;
                ");
                // Create new backup-profile-table
                Shopware()->Db()->query("
                CREATE TABLE $tableName (
                 `jobId` INT NOT NULL
                 ) ENGINE = InnoDB;
                ");
                // Insert jobid in backup-profile-table
                Shopware()->Db()->query("
                INSERT INTO $tableName (jobId)
                VALUES ({$jobConfiguration["id"]})
                ");
            }
            /**
             * CREATE TABLE `sw4`.`bck_staging_stat` (
             `jobId` INT NOT NULL
             ) ENGINE = InnoDB;

             */
            // If job is executed first time, set startDate
            $jobObject->fromArray(
                array(
                    'startDate' => new \DateTime()
                )
            );
            Shopware()->Models()->persist($jobObject);
            Shopware()->Models()->flush();
        }

        // Get jobs total
        $data = $queueObject->getQueueTotalCount($this->getJobId(),false);
        $data = $data->getQuery()->getArrayResult();
        $totalJobs = $data[0][1];

        // Proceed job queue
        $jobQueue = $queueObject->getQueueQueryBuilder($this->getJobId(),true,$limit);
        $jobQueue = $jobQueue->getQuery()->getArrayResult();

        foreach ($jobQueue as $job){
            // Set start-time of job
            $start = time();
            $startTime = new \DateTime();
            // Proceed attached method calls
            if (empty($job["job"])){
                throw new Exception("Job has no attached method calls");
            }
            $jobOperations = unserialize($job["job"]);
            foreach ($jobOperations as $jobOperationCall){
                $jobMethod  = $jobOperationCall["method"];
                $jobParameters = $jobOperationCall["parameters"];
                if (method_exists($this,$jobMethod)){
                    call_user_func_array(array($this,$jobMethod),$jobParameters);
                }else {
                    throw new Exception("$jobMethod not found in staging component");
                }
            }
            // Calculate job-duration and update job from queue
            $end = time() - $start;
            $duration = new \DateTime();
            $duration->setTime(0,0,$end);
            // Update job in database
            $jobRow = $queueObject->find($job["id"]);
            $jobRow->fromArray(array(
                'done' => true,
                'start' => $startTime,
                'duration'=> $duration
            ));
            Shopware()->Models()->persist($jobRow);
            Shopware()->Models()->flush();
        }

        // Get remaining jobs
        $data = $queueObject->getQueueTotalCount($this->getJobId(),true);
        $data = $data->getQuery()->getArrayResult();
        $remainingJobs = $data[0][1];

        // Update replication job state
        if ($remainingJobs == 0){
               $done = true;
        }else {
           $done = false;
        }
        $jobUpdate = array(
            'successful' => $done,
            'jobsTotal' => $totalJobs,
            'jobsCurrent' => $totalJobs - $remainingJobs,
            'running' => $done == true ? false : true
        );
        if ($done == true){
            // Job is finished, set end-time
            $jobUpdate['endDate'] = new \DateTime();
        }
        $jobObject->fromArray($jobUpdate);
        Shopware()->Models()->persist($jobObject);
        Shopware()->Models()->flush();

        // Return statistics
        return array(
            "done" => $done,
            "totalQueueJobs"=>$totalJobs,
            "doneQueueJobs"=>$totalJobs - $remainingJobs,
            "jobId"=>$this->getJobId(),
            "offset"=>50
        );
    }

    /**
     * Create a new job queue for slave
     */
    protected function prepareQueueSlaveMaster(){
        // Get involved tables
        /*
         * Loop through table configuration
         * Slave > Master Profile:
         * replicate (Default complete, or col-based)
         *   1.) Check if we have to restore the table complete, or if we have to hold some cols from original table
         *   1.a) On complete restore
         *      - Check if table is physical available (not a view), if not ignore table
         *      - Drop temporary table if exists
         *      - Create temporary table (t_tablename) in master with data from staging-table
         *      - Drop table backup if exists
         *      - Rename original master table to bck_tablename
         *      - Rename temporary table to s_tablename
         *      - !!Finished!!
         *   1.b) On column based restore
         *      - Process as described above
         *      - Before switching tables,
         *      - UPDATE t_table SET t_table.col1 = s_table.col1,.... WHERE t_table.id = s_table.id
         * ignore
         * col_based_replication - Synchronize only some column values back
         * Add a table-inspect job to each table-definition
         *   -- check if table is available & table is not a view
         *   -- if table is a view ignore
         */
        $tables = $this->getInvolvedTables();

        foreach ($tables as $table){

            $sourceName = $this->getSlave('database').".".$table["tableName"];
            $targetName = $this->getMaster('database').".".$table["tableName"];
            if (strpos($table["tableName"],"s_plugin_staging")!==false){
                // Never copy staging core tables back to master
                $table["strategy"] = "ignore";
            }
            // Check if table exists in staging database
            $checkIfTableExists = Shopware()->Db()->fetchOne("SELECT TABLE_NAME AS `table`
            FROM information_schema.tables
            WHERE table_schema = ?
            AND table_name = ?
            ",array($this->getSlave('database'),$table["tableName"]));
            if (!$checkIfTableExists){
                $table["strategy"] = "ignore";
            }

            switch ($table["strategy"]){
                case "replicate":
                    // Check if there are cols defined, that should migrated from master system before restore
                    $colModel = Shopware()->Models()->getRepository('Shopware\CustomModels\Staging\Columns');
                    $cols = $colModel->findByTableId($table["id"]);
                    $cols = Shopware()->Models()->toArray($cols);
                    $colText = "";
                    if (!empty($cols)){
                        $colText = "(w/cols)";
                    }
                    $this->addJobToQueue("Restore $colText $sourceName to $targetName",
                    array(
                        0 => array(
                            'method' => 'queueRestoreTable',
                            'parameters' => array(
                                $sourceName,$targetName,$cols
                            )
                        )
                    )
                    );
                    break;
                // Table views could be ignored
                case "col_based_replication":
                    $colModel = Shopware()->Models()->getRepository('Shopware\CustomModels\Staging\Columns');
                    $cols = $colModel->findByTableId($table["id"]);
                    $cols = Shopware()->Models()->toArray($cols);
                    $this->addJobToQueue("Restore Columns from $sourceName to $targetName",
                    array(
                        0 => array(
                               'method' => 'queueRestoreColumns',
                               'parameters' => array(
                                   $sourceName,$targetName,$cols
                               )
                           )
                    )
                    );
                    break;
                case "view":
                case "ignore":
                default:
                    break;
            }
        }
    }

    /**
     * Job that take back only colums from staging / slave system to master database
     * For example replicate staging.s_articles.description to master.s_articles.description
     * Creates a backup of the affected tables
     * @param $source
     * @param $target
     * @param $columns
     * @return bool
     */
    protected function queueRestoreColumns ($source,$target,$columns){
        if (empty($columns)) return false;
        // Restore only some columns from staging.table to master.table
        // - Check if table is physical available (not a view), if not ignore table
        // - Drop table backup if exists
        // - Copy original table into backup
        // - Execute column based backup on master original table

        $getStagingConfig = Shopware()->getOption("custom");
        $stagingDatabase = $getStagingConfig["staging_database"];
        $normalizedTableName = str_replace($stagingDatabase.".","",$source);

        if (substr($normalizedTableName,0,1)=="s_"){
            $backupTableName = $getStagingConfig["master_database"].".".str_replace("s_","bck_",$normalizedTableName);
        }else {
            $backupTableName = $getStagingConfig["master_database"]."."."bck_".$normalizedTableName;
        }
         // Check first if table exists physical
        $sql = "SELECT TABLE_TYPE FROM information_schema.tables WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?";
        $checkIfTableHasCorrectType = Shopware()->Db()->fetchOne($sql,array($stagingDatabase,$normalizedTableName));
        if ($checkIfTableHasCorrectType == "VIEW" || empty($checkIfTableHasCorrectType)){
            // Protocol error in job
            //throw new Exception("Table $normalizedTableName in $stagingDatabase is unknown or is a view");
            return;
        }

         // Drop backup table
        $sql = "DROP TABLE IF EXISTS $backupTableName";
        Shopware()->Db()->query($sql);

        // Create backup of master table
        $sql = "
        CREATE TABLE $backupTableName
        LIKE $target;
        ";
        $this->getDatabase()->query($sql);

        $sql = "
        INSERT INTO $backupTableName
        SELECT * FROM $target
        ";
        $this->getDatabase()->query($sql);

        // If columns are defined
        if (!empty($columns) && is_array($columns)){
            /*
             * $columns = array("col1","col2");
             * UPDATE sw4.b SET sw4.b.a = IFNULL((SELECT a FROM sw4_staging.b WHERE id = sw4.b.id),sw4.b.a)
             */
            $updateCols = array();
            foreach ($columns as $column){

                $column = $column["column"];
                $updateCols[] = " $target.$column = IFNULL((SELECT $column FROM $source WHERE id =  $target.id),$target.$column)";
            }
            $updateCols = implode(",",$updateCols);
            // Update defined columns in temporary table with data from master-table
            /**
             * UPDATE sw4.t_b SET  sw4.t_b.a = (SELECT a FROM sw4.b WHERE id = sw4.t_b.id)
             */
            $sql = "UPDATE {$target} SET {$updateCols}";

            Shopware()->Db()->query($sql);
        }

        return true;
    }

    /**
     * Job that restores a full table from staging / slave system to master database
     * Creates a full table backup in master-system before replace table
     * Can merge columns from master-database in staging table result
     * @param $source
     * @param $target
     * @param $columns
     */
    protected function queueRestoreTable($source,$target,$columns){
        /*
 *        *   1.a) On complete restore
          *      - Check if table is physical available (not a view), if not ignore table [OK]
          *      - Drop temporary table if exists [OK]
          *      - Create temporary table (t_tablename) in master with data from staging-table
          *      - Drop table backup if exists [OK]
          *      - Rename original master table to bck_tablename
          *      - Rename temporary table to s_tablename
          *      - !!Finished!!
         */
        $getStagingConfig = Shopware()->getOption("custom");
        $stagingDatabase = $getStagingConfig["staging_database"];
        $normalizedTableName = str_replace($stagingDatabase.".","",$source);

        /**
         * Set table-names or temporary used tables
         * t_[TABLE] for temporary table
         * bck_[TABLE] for backup original table
         */
        if (substr($normalizedTableName,0,1)=="s_"){
            // For shopware-tables
            $temporaryTableName = $getStagingConfig["master_database"].".".str_replace("s_","temp_",$normalizedTableName);
        }else {
            // For other tables
            $temporaryTableName = $getStagingConfig["master_database"]."."."temp_".$normalizedTableName;
        }

        if (substr($normalizedTableName,0,1)=="s_"){
            $backupTableName = $getStagingConfig["master_database"].".".str_replace("s_","bck_s_",$normalizedTableName);
        }else {
            $backupTableName = $getStagingConfig["master_database"]."."."bck_".$normalizedTableName;
        }

        // Check first if table exists physical
        $sql = "SELECT TABLE_TYPE FROM information_schema.tables WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?";
        $checkIfTableHasCorrectType = Shopware()->Db()->fetchOne($sql,array($stagingDatabase,$normalizedTableName));
        if ($checkIfTableHasCorrectType == "VIEW" || empty($checkIfTableHasCorrectType)){
            // Protocol error in job
            //throw new Exception("Table $normalizedTableName in $stagingDatabase is unknown or is a view");
            return;
        }
        // Drop temporary table
        $sql = "DROP TABLE IF EXISTS $temporaryTableName";
        Shopware()->Db()->query($sql);

        // Drop backup table
        $sql = "DROP TABLE IF EXISTS $backupTableName";
        Shopware()->Db()->query($sql);
        // Create temporary table with structure from master and data from stage

        $sql = "
        CREATE TABLE $temporaryTableName
        LIKE $source;
        ";
        $this->getDatabase()->query($sql);

        $sql = "
        INSERT INTO $temporaryTableName
        SELECT * FROM $source
        ";
        $this->getDatabase()->query($sql);

        // If columns are defined
        if (!empty($columns) && is_array($columns)){
            /*
             * $columns = array("col1","col2");
             * UPDATE sw4.b SET sw4.b.a = IFNULL((SELECT a FROM sw4_staging.b WHERE id = sw4.b.id),sw4.b.a)
             */
            $updateCols = array();
            foreach ($columns as $column){

                $column = $column["column"];
                $updateCols[] = " $temporaryTableName.$column = IFNULL((SELECT $column FROM $target WHERE id =  $temporaryTableName.id),$temporaryTableName.$column)";
            }
            $updateCols = implode(",",$updateCols);
            // Update defined columns in temporary table with data from master-table
            /**
             * UPDATE sw4.t_b SET  sw4.t_b.a = (SELECT a FROM sw4.b WHERE id = sw4.t_b.id)
             */
            $sql = "UPDATE {$temporaryTableName} SET {$updateCols}";

            Shopware()->Db()->query($sql);
        }

        // Rename tables / Finish job
        $sql = "
        RENAME TABLE
            $target TO $backupTableName,
            $temporaryTableName TO $target;
        ";
        Shopware()->Db()->query($sql);
    }
    /**
     * Create job queue for replicate master database to slave database
      * Fields:
      * tableName
      * strategy =>
      * - ignore = Ignore table
      * - replicate = Copy table
      * - col_based_replication = Copy table and replace columns during slave > master replication
      * - view = Simulate table as view in slave
      */
    protected function prepareQueueMasterSlave(){
        // Get involved tables
        $tables = $this->getInvolvedTables();

        foreach ($tables as $table){

            $sourceName = $this->getMaster('database').".".$table["tableName"];
            $targetName = $this->getSlave('database').".".$table["tableName"];
            if (strpos($table["tableName"],"s_plugin_staging")!==false){
                  // Use staging-system tables always as view in staging system
                  $table["strategy"] = "view";
            }
            switch ($table["strategy"]){
                case "replicate":
                case "col_based_replication":
                    $this->addJobToQueue("Copy $sourceName to $targetName",
                    array(
                        0 => array(
                            'method' => 'queueJobRemoveTableIfExists',
                            'parameters' => array($targetName)
                        ),
                        1 => array(
                            'method' => 'queueJobReplicateTable',
                            'parameters' => array(
                                $sourceName,$targetName
                            )
                        )
                    )
                    );
                    break;
                case "view":
                    $this->addJobToQueue("Make view of $sourceName in $targetName",
                       array(
                           0 => array(
                               'method' => 'queueJobRemoveTableIfExists',
                               'parameters' => array($targetName)
                           ),
                           1 => array(
                               'method' => 'queueJobReplicateTableAsView',
                               'parameters' => array(
                                   $sourceName,$targetName
                               )
                           )
                       )
                       );
                    break;
                case "ignore":
                default:
                    break;
            }
        }
    }

    /**
     * Add a new job to queue model
     * @param $text
     * @param $job
     */
    protected function addJobToQueue($text,$job){
        $duration = new \DateTime();
        $duration->setTime(0,0,0);
        $job = array(
            'jobId' => $this->getJobId(),
            'text' => $text,
            'job' => serialize($job),
            'done' => 0,
            'errorMsg' => '',
            'start' => new \DateTime(),
            'duration' => $duration
        );
        $newQueueEntry = new Queue();
        $newQueueEntry->fromArray($job);
        Shopware()->Models()->persist($newQueueEntry);
        Shopware()->Models()->flush();
    }

    /**
     * Replicate a database table from db1 to db2
     * @param $from database.table
     * @param $to   database.table
     */
    protected function queueJobReplicateTable($source,$origin){

        $sql = "
        CREATE TABLE $origin
        LIKE $source;
        ";
        $this->getDatabase()->query($sql);

        $sql = "
        INSERT INTO $origin
        SELECT * FROM $source
        ";
        $this->getDatabase()->query($sql);
    }

    /**
     * Replicate a database table from db1 to db2
     * @param $from database.table
     * @param $to   database.table
     */
    protected function queueJobReplicateTableAsView($source,$origin){
        $sql = "
        CREATE VIEW $origin AS (
            SELECT * FROM $source
        )
        ";
        $this->getDatabase()->query($sql);
    }

    /**
     * Drop a table or view from database
     * @param $table database.table
     */
    protected function queueJobRemoveTableIfExists($table){

        $sql = "
        DROP TABLE IF EXISTS $table;
        ";
        $this->getDatabase()->query($sql);
        $sql = "
        DROP VIEW IF EXISTS $table;
        ";
        $this->getDatabase()->query($sql);
    }

    /**
     * Get table-configuration
     * @return mixed
     */
    protected function getInvolvedTables(){
        // Read all tables from database during model
        $job = $this->getJob();
        $job = Shopware()->Models()->toArray($job);
        if (empty($job["profileId"])){
            throw new Exception("Job with empty profileId");
        }

        $model = Shopware()->Models()->getRepository('Shopware\CustomModels\Staging\Tables');
        $data = $model->getListQueryBuilder($job["profileId"]);
        $data = $data->getQuery()->getArrayResult();
        return $data;
    }

    /**
     * Set job id
     * @param $jobId
     */
    public function setJobId($jobId)
    {
        $this->_jobId = $jobId;
        $this->loadJob();
        $this->loadProfile();
    }

    /**
     * Get job id
     * @return mixed
     */
    public function getJobId()
    {
        return $this->_jobId;
    }

    /**
     * Load job model
     * @throws Exception
     */
    public function loadJob(){
        $job = Shopware()->Models()->getRepository('Shopware\CustomModels\Staging\Jobs');

        $job = $job->find($this->getJobId());
        if (!$job){
            throw new Exception("Job not found");
        }
        $this->_job = $job;
    }

    /**
     * Load profile model
     * @throws Exception
     */
    public function loadProfile(){
       $profile = Shopware()->Models()->getRepository('Shopware\CustomModels\Staging\Profiles');
       $profile = $profile->find($this->getJob()->getProfileId());
       if (!$profile){
           throw new Exception("Profile not found");
       }
       $this->_profile = $profile;
       $this->setMode($profile->getProfileAssignment());
   }

    /**
     * Get profile model
     * @return Shopware_Models_Staging_Profile
     */
    public function getProfile(){
       return $this->_profile;
   }

    /**
     * Get job model
     * @return Shopware_Models_Staging_Job
     */
    public function getJob(){
        return $this->_job;
    }

    /**
     * Set master configuration
     * @param $master
     */
    public function setMaster($master)
    {
        $this->_master = $master;
    }

    /**
     * Get master configuration
     * @param string $key
     * @return array
     */
    public function getMaster($key = "")
    {
        if (isset($this->_master[$key])) return $this->_master[$key];
        return $this->_master;
    }

    /**
     * Set slave configuration
     * @param $slave
     */
    public function setSlave($slave)
    {
        $this->_slave = $slave;
    }

    /**
     * Get slave configuration
     * @param string $key
     * @return array
     */
    public function getSlave($key = "")
    {
        if (isset($this->_slave[$key])) return $this->_slave[$key];
        return $this->_slave;
    }
}