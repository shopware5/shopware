<?php
require_once 'phing/Task.php';
require_once 'phing/tasks/ext/svn/SvnBaseTask.php';

/**
 * Exports the differences between a specified revision and HEAD of a repository to a local directory
 */
class SvnExportDiffTask extends SvnBaseTask
{
    /**
     * Revision number
     *
     * @var int
     */
    protected $_revision;

    /**
     * Name of the file where the list of deleted files/folders will be written
     *
     * @var string
     */
    protected $_filelistDeleted;

    /**
     * Sets the revision number
     *
     * @param int $revision revision number
     *
     * @return void
     */
    public function setRevision($revision)
    {
        $this->_revision = (int) $revision;
    }

    /**
     * Sets the filename where to put the list of files/folders deleted
     *
     * @param string $filename file name
     *
     * @return void
     */
    public function setFilelistDeleted($filename)
    {
        $this->_filelistDeleted = $filename;
    }

    /**
     * The main entry point
     */
    public function main()
    {
        $this->setup('diff');
        $output = $this->run(array(), array('summarize' => true, 'r' => $this->_revision));

        $repo    = $this->getRepositoryUrl();
        $toDir   = $this->getToDir();

        $toDir = rtrim($toDir, '/') . '/';

        $deletedFiles = array();
        foreach (explode("\n", $output) as $line) {

            $flag = $line[0];

            // remove "line-header"
            $line = substr($line, strpos($line, $repo));

            // remove repo-url part
            $a    = explode($repo, $line);
            $file = $a[1];

            // extract path
            $path = explode('/', $file);
            array_pop($path);

            if ($flag == 'A' || $flag == 'M') {
                $path = $toDir . implode('/', $path);

                // and make sure it exists. because we can get a file within a folder, and we'll export
                // the file in a corresponding folder, only if it doesn't exists export will fail, so:
                if (!file_exists($path)) {
                    mkdir($path, 0775, true); // TRUE == recursive
                }

                // because the diff can get us both a folder and files within, with files first.
                // so, we export if dest doesn't already exists, or is not a folder (which will
                // fail unless force was true). Exists && folder was (probably) created when
                // previously exporting a file within
                if (!file_exists($dest = $toDir . $file) || !is_dir($dest)) {
                    $this->log("Exporting $file from $repo to $toDir");
                    $this->setRepositoryUrl($repo . $file);
                    // need to re-do setup each time, since we have a new repositoryUrl
                    $this->setup('export');
                    $this->run(array($dest));
                }
            } elseif (isset($this->_filelistDeleted) && $flag == 'D') {
                $deletedFiles[] = $file;
            }
        }

        if (isset($this->_filelistDeleted)) {
            file_put_contents($toDir . DIRECTORY_SEPARATOR . $this->_filelistDeleted, implode(PHP_EOL, $deletedFiles));
        }
    }
}
