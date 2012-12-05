<?php
require_once 'phing/Task.php';

class AppendSqlDeltasTask extends Task
{

    /**
     * Path to the directory that holds the database patch files
     *
     * @var string
     */
    protected $dir;

    /**
     * Output file for performing all database patches of this deployment
     * Contains all the SQL statements that need to be executed
     *
     * @var string
     */
    protected $outputFile = 'update.sql';

    /**
     * The main function for the task
     *
     * @throws BuildException
     * @return void
     */
    public function main()
    {
        try {
            $this->createOutputFile($this->outputFile);

        } catch (Exception $e) {
            throw new BuildException($e);
        }
    }

    /**
     * Generate the sql for doing/undoing the deployment and write it to a file
     *
     * @param string $file
     * @return void
     */
    protected function createOutputFile($file)
    {
        $fileHandle = fopen($file, "a+");
        $sql = $this->generateSql();
        fwrite($fileHandle, $sql);
    }

    /**
     * Generate the sql for doing/undoing this deployment
     *
     * @return string The sql
     */
    protected function generateSql()
    {
        $sql = '';
        $files = $this->getDeltasFilesArray();

        foreach ($files as $fileChangeNumber => $fileName) {
            $sql .= '-- ' . $fileName . "\n";

            // read the file
            $fullFileName = $this->dir . '/' . $fileName;
            $fh = fopen($fullFileName, 'r');
            $contents = fread($fh, filesize($fullFileName));
            // allow construct with and without space added
            $split = strpos($contents, '-- //@UNDO');
            if ($split === false)
                $split = strpos($contents, '--//@UNDO');
            if ($split === false)
                $split = strlen($contents);

            $sql .= substr($contents, 0, $split);

        }

        return $sql;
    }

    /**
     * Get a list of all the patch files in the patch file directory
     *
     * @return array
     */
    protected function getDeltasFilesArray()
    {
        $files = array();
        $baseDir = realpath($this->dir);
        $dh = opendir($baseDir);
        $fileChangeNumberPrefix = '';
        while (($file = readdir($dh)) !== false) {
            if (preg_match('[\d+]', $file, $fileChangeNumberPrefix)) {
                $files[intval($fileChangeNumberPrefix[0])] = $file;
            }
        }

        ksort($files);

        return $files;
    }

    /**
     * Set the directory where to find the patchfiles
     *
     * @param string $dir
     * @return void
     */
    public function setDir($dir)
    {
        $this->dir = $dir;
    }

    /**
     * Set the outputfile which contains all patch sql statements for this deployment
     *
     * @param string $outputFile
     * @return void
     */
    public function setOutputFile($outputFile)
    {
        $this->outputFile = $outputFile;
    }


}
