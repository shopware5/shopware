<?php
class Shopware_Components_Archive_Tar extends Shopware_Components_File_Adapter
{
    protected $entryList;

    public function __construct($fileName = null, $flags = null)
    {
        $this->init($fileName, $flags);

        $this->entryList = $this->listContent();

        $this->position = 0;
        $this->count = count($this->entryList);
    }

    public function current()
    {
        return new Shopware_Components_File_Entry_Tar($this, $this->position);
    }

    public function getEntry($position)
    {
        return isset($this->entryList[$position]) ? $this->entryList[$position] : null;
    }

    public function getContents($position)
    {
        $entry = $this->getEntry($position);
        $content = '';
        if (!empty($entry['size'])) {
            if ($this->compressType == 'gz') {
                @gzseek($this->file, $entry['position']);
            } else if ($this->compressType == 'none') {
                @fseek($this->file, $entry['position']);
            }
            $n = floor($entry['size'] / 512);
            for ($i = 0; $i < $n; $i++) {
                $content .= $this->readBlock();
            }
            if (($entry['size'] % 512) != 0) {
                $content .= $this->readBlock($entry['size'] % 512);
            }
        }
        return $content;
    }

    /**
     * @public string Name of the Tar
     */
    public $tarname = '';

    /**
     * @public boolean if true, the Tar file will be gzipped
     */
    public $compress = false;

    /**
     * @public string Type of compression : 'none', 'gz' or 'bz2'
     */
    public $compressType = 'none';

    /**
     * @public string Explode separator
     */
    public $separator = ' ';

    /**
     * @public file descriptor
     */
    public $file = 0;

    /**
     * @public string Local Tar name of a remote Tar (http:// or ftp://)
     */
    public $tempFileName = '';

    protected function init($p_tarname, $p_compress = null)
    {
        $this->compress = false;
        $this->compressType = 'none';
        if (($p_compress === null) || ($p_compress == '')) {
            if (@file_exists($p_tarname)) {
                if ($fp = @fopen($p_tarname, 'rb')) {
                    // look for gzip magic cookie
                    $data = fread($fp, 2);
                    fclose($fp);
                    if ($data == "\37\213") {
                        $this->compress = true;
                        $this->compressType = 'gz';
                        // No sure it's enought for a magic code ....
                    } elseif ($data == "BZ") {
                        $this->compress = true;
                        $this->compressType = 'bz2';
                    }
                }
            } else {
                // probably a remote file or some file accessible
                // through a stream interface
                if (substr($p_tarname, -2) == 'gz') {
                    $this->compress = true;
                    $this->compressType = 'gz';
                } elseif ((substr($p_tarname, -3) == 'bz2') ||
                    (substr($p_tarname, -2) == 'bz')
                ) {
                    $this->compress = true;
                    $this->compressType = 'bz2';
                }
            }
        } else {
            if (($p_compress === true) || ($p_compress == 'gz')) {
                $this->compress = true;
                $this->compressType = 'gz';
            } else if ($p_compress == 'bz2') {
                $this->compress = true;
                $this->compressType = 'bz2';
            } else {
                die("Unsupported compression type '$p_compress'\n" .
                    "Supported types are 'gz' and 'bz2'.\n");
            }
        }
        $this->tarname = $p_tarname;
        if ($this->compress) { // assert zlib or bz2 extension support
            if ($this->compressType == 'gz') {
                $extname = 'zlib';
            } elseif ($this->compressType == 'bz2') {
                $extname = 'bz2';
            }

            if (!extension_loaded($extname)) {
                die("The extension '$extname' couldn't be found.\n" .
                    "Please make sure your version of PHP was built " .
                    "with '$extname' support.\n");
            }
        }
    }

    public function __destruct()
    {
        $this->close();
        // ----- Look for a local copy to delete
        if ($this->tempFileName != '')
            @unlink($this->tempFileName);
    }

    function extract($p_path = '')
    {
        return $this->extractModify($p_path, '');
    }

    public function listContent()
    {
        $listDetail = array();

        if ($this->openRead()) {
            if (!$this->extractList('', $listDetail, 'list', '', '')) {
                $listDetail = array();
            }
            $this->close();
        }

        return $listDetail;
    }

    public function extractModify($p_path, $p_remove_path)
    {
        $v_result = true;
        $vlistDetail = array();

        if ($v_result = $this->openRead()) {
            $v_result = $this->extractList($p_path, $vlistDetail,
                "complete", 0, $p_remove_path);
            $this->close();
        }

        return $v_result;
    }

    public function extractPartialList($p_filelist, $p_path = '', $p_remove_path = '')
    {
        $v_result = true;
        $vlistDetail = array();

        if (is_array($p_filelist))
            $v_list = $p_filelist;
        elseif (is_string($p_filelist))
            $v_list = explode($this->separator, $p_filelist); else {
            $this->error('Invalid string list');
            return false;
        }

        if ($v_result = $this->openRead()) {
            $v_result = $this->extractList($p_path, $vlistDetail, "partial",
                $v_list, $p_remove_path);
            $this->close();
        }

        return $v_result;
    }

    protected function error($p_message)
    {
        throw new Exception($p_message);
    }

    protected function isArchive($p_filename = NULL)
    {
        if ($p_filename == NULL) {
            $p_filename = $this->tarname;
        }
        clearstatcache();
        return @is_file($p_filename) && !@is_link($p_filename);
    }

    protected function openRead()
    {
        if (strtolower(substr($this->tarname, 0, 7)) == 'http://') {

            // ----- Look if a local copy need to be done
            if ($this->tempFileName == '') {
                $this->tempFileName = uniqid('tar') . '.tmp';
                if (!$v_file_from = @fopen($this->tarname, 'rb')) {
                    $this->error('Unable to open in read mode \''
                        . $this->tarname . '\'');
                    $this->tempFileName = '';
                    return false;
                }
                if (!$v_file_to = @fopen($this->tempFileName, 'wb')) {
                    $this->error('Unable to open in write mode \''
                        . $this->tempFileName . '\'');
                    $this->tempFileName = '';
                    return false;
                }
                while ($v_data = @fread($v_file_from, 1024))
                    @fwrite($v_file_to, $v_data);
                @fclose($v_file_from);
                @fclose($v_file_to);
            }

            // ----- File to open if the local copy
            $v_filename = $this->tempFileName;

        } else
            // ----- File to open if the normal Tar file
            $v_filename = $this->tarname;

        if ($this->compressType == 'gz')
            $this->file = @gzopen($v_filename, "rb");
        else if ($this->compressType == 'bz2')
            $this->file = @bzopen($v_filename, "r");
        else if ($this->compressType == 'none')
            $this->file = @fopen($v_filename, "rb");
        else
            $this->error('Unknown or missing compression type ('
                . $this->compressType . ')');

        if ($this->file == 0) {
            $this->error('Unable to open in read mode \'' . $v_filename . '\'');
            return false;
        }

        return true;
    }

    protected function close()
    {
        //if (isset($this->file)) {
        if (is_resource($this->file)) {
            if ($this->compressType == 'gz')
                @gzclose($this->file);
            else if ($this->compressType == 'bz2')
                @bzclose($this->file);
            else if ($this->compressType == 'none')
                @fclose($this->file);
            else
                $this->error('Unknown or missing compression type ('
                    . $this->compressType . ')');

            $this->file = 0;
        }

        // ----- Look if a local copy need to be erase
        // Note that it might be interesting to keep the url for a time : ToDo
        if ($this->tempFileName != '') {
            @unlink($this->tempFileName);
            $this->tempFileName = '';
        }

        return true;
    }

    protected function cleanFile()
    {
        $this->close();

        // ----- Look for a local copy
        if ($this->tempFileName != '') {
            // ----- Remove the local copy but not the remote tarname
            @unlink($this->tempFileName);
            $this->tempFileName = '';
        } else {
            // ----- Remove the local tarname file
            @unlink($this->tarname);
        }
        $this->tarname = '';

        return true;
    }

    protected function readBlock($length = 512)
    {
        $v_block = null;
        if (is_resource($this->file)) {
            if ($this->compressType == 'gz')
                $v_block = @gzread($this->file, $length);
            else if ($this->compressType == 'bz2')
                $v_block = @bzread($this->file, $length);
            else if ($this->compressType == 'none')
                $v_block = @fread($this->file, $length);
            else
                $this->error('Unknown or missing compression type ('
                    . $this->compressType . ')');
        }
        return $v_block;
    }

    protected function jumpBlock($p_len = null)
    {
        if (is_resource($this->file)) {
            if ($p_len === null)
                $p_len = 1;

            if ($this->compressType == 'gz') {
                @gzseek($this->file, gztell($this->file) + ($p_len * 512));
            } else if ($this->compressType == 'bz2') {
                // ----- Replace missing bztell() and bzseek()
                for ($i = 0; $i < $p_len; $i++)
                    $this->readBlock();
            } else if ($this->compressType == 'none')
                @fseek($this->file, ftell($this->file) + ($p_len * 512));
            else
                $this->error('Unknown or missing compression type ('
                    . $this->compressType . ')');

        }
        return true;
    }

    protected function readHeader($v_binary_data, &$v_header)
    {
        if (strlen($v_binary_data) == 0) {
            $v_header['filename'] = '';
            return true;
        }

        if (strlen($v_binary_data) != 512) {
            $v_header['filename'] = '';
            $this->error('Invalid block size : ' . strlen($v_binary_data));
            return false;
        }

        if (!is_array($v_header)) {
            $v_header = array();
        }
        // ----- Calculate the checksum
        $v_checksum = 0;
        // ..... First part of the header
        for ($i = 0; $i < 148; $i++)
            $v_checksum += ord(substr($v_binary_data, $i, 1));
        // ..... Ignore the checksum value and replace it by ' ' (space)
        for ($i = 148; $i < 156; $i++)
            $v_checksum += ord(' ');
        // ..... Last part of the header
        for ($i = 156; $i < 512; $i++)
            $v_checksum += ord(substr($v_binary_data, $i, 1));

        $v_data = unpack("a100filename/a8mode/a8uid/a8gid/a12size/a12mtime/"
                . "a8checksum/a1typeflag/a100link/a6magic/a2version/"
                . "a32uname/a32gname/a8devmajor/a8devminor",
            $v_binary_data);

        // ----- Extract the checksum
        $v_header['checksum'] = OctDec(trim($v_data['checksum']));
        if ($v_header['checksum'] != $v_checksum) {
            $v_header['filename'] = '';

            // ----- Look for last block (empty block)
            if (($v_checksum == 256) && ($v_header['checksum'] == 0))
                return true;

            $this->error('Invalid checksum for file "' . $v_data['filename']
                . '" : ' . $v_checksum . ' calculated, '
                . $v_header['checksum'] . ' expected');
            return false;
        }

        // ----- Extract the properties
        $v_header['filename'] = trim($v_data['filename']);
        if ($this->maliciousFilename($v_header['filename'])) {
            $this->error('Malicious .tar detected, file "' . $v_header['filename'] .
                '" will not install in desired directory tree');
            return false;
        }
        $v_header['mode'] = OctDec(trim($v_data['mode']));
        $v_header['uid'] = OctDec(trim($v_data['uid']));
        $v_header['gid'] = OctDec(trim($v_data['gid']));
        $v_header['size'] = OctDec(trim($v_data['size']));
        $v_header['mtime'] = OctDec(trim($v_data['mtime']));
        if (($v_header['typeflag'] = $v_data['typeflag']) == "5") {
            $v_header['size'] = 0;
        }
        $v_header['link'] = trim($v_data['link']);
        /* ----- All these fields are removed form the header because
		they do not carry interesting info
        $v_header[magic] = trim($v_data[magic]);
        $v_header[version] = trim($v_data[version]);
        $v_header[uname] = trim($v_data[uname]);
        $v_header[gname] = trim($v_data[gname]);
        $v_header[devmajor] = trim($v_data[devmajor]);
        $v_header[devminor] = trim($v_data[devminor]);
        */

        return true;
    }

    protected function maliciousFilename($file)
    {
        if (strpos($file, '/../') !== false) {
            return true;
        }
        if (strpos($file, '../') === 0) {
            return true;
        }
        return false;
    }

    protected function readLongHeader(&$v_header)
    {
        $v_filename = '';
        $n = floor($v_header['size'] / 512);
        for ($i = 0; $i < $n; $i++) {
            $v_content = $this->readBlock();
            $v_filename .= $v_content;
        }
        if (($v_header['size'] % 512) != 0) {
            $v_content = $this->readBlock();
            $v_filename .= $v_content;
        }

        // ----- Read the next header
        $v_binary_data = $this->readBlock();

        if (!$this->readHeader($v_binary_data, $v_header))
            return false;

        $v_filename = trim($v_filename);
        $v_header['filename'] = $v_filename;
        if ($this->maliciousFilename($v_filename)) {
            $this->error('Malicious .tar detected, file "' . $v_filename .
                '" will not install in desired directory tree');
            return false;
        }

        return true;
    }

    protected function extractList($p_path, &$plistDetail, $p_mode,
                                   $p_file_list, $p_remove_path)
    {
        $v_result = true;
        $v_nb = 0;
        $v_extract_all = true;
        $v_listing = false;

        $p_path = $this->translateWinPath($p_path, false);
        if ($p_path == '' || (substr($p_path, 0, 1) != '/'
            && substr($p_path, 0, 3) != "../" && !strpos($p_path, ':'))
        ) {
            $p_path = "./" . $p_path;
        }
        $p_remove_path = $this->translateWinPath($p_remove_path);

        // ----- Look for path to remove format (should end by /)
        if (($p_remove_path != '') && (substr($p_remove_path, -1) != '/'))
            $p_remove_path .= '/';
        $p_remove_path_size = strlen($p_remove_path);

        switch ($p_mode) {
            case "complete" :
                $v_extract_all = TRUE;
                $v_listing = FALSE;
                break;
            case "partial" :
                $v_extract_all = FALSE;
                $v_listing = FALSE;
                break;
            case "list" :
                $v_extract_all = FALSE;
                $v_listing = TRUE;
                break;
            default :
                $this->error('Invalid extract mode (' . $p_mode . ')');
                return false;
        }

        clearstatcache();

        while (strlen($v_binary_data = $this->readBlock()) != 0) {
            $v_extract_file = FALSE;
            $v_extraction_stopped = 0;
            $v_header = null;

            if (!$this->readHeader($v_binary_data, $v_header))
                return false;

            if ($v_header['filename'] == '') {
                continue;
            }

            // ----- Look for long filename
            if ($v_header['typeflag'] == 'L') {
                if (!$this->readLongHeader($v_header))
                    return false;
            }

            if ((!$v_extract_all) && (is_array($p_file_list))) {
                // ----- By default no unzip if the file is not found
                $v_extract_file = false;

                for ($i = 0; $i < sizeof($p_file_list); $i++) {
                    // ----- Look if it is a directory
                    if (substr($p_file_list[$i], -1) == '/') {
                        // ----- Look if the directory is in the filename path
                        if ((strlen($v_header['filename']) > strlen($p_file_list[$i]))
                            && (substr($v_header['filename'], 0, strlen($p_file_list[$i]))
                                == $p_file_list[$i])
                        ) {
                            $v_extract_file = TRUE;
                            break;
                        }
                    } // ----- It is a file, so compare the file names
                    elseif ($p_file_list[$i] == $v_header['filename']) {
                        $v_extract_file = TRUE;
                        break;
                    }
                }
            } else {
                $v_extract_file = TRUE;
            }

            if ($v_listing && $v_header['typeflag'] == 0) {
                if ($this->compressType == 'gz')
                    $v_header['position'] = @gztell($this->file);
                else if ($this->compressType == 'none')
                    $v_header['position'] = @ftell($this->file);
            }

            // ----- Look if this file need to be extracted
            if (($v_extract_file) && (!$v_listing)) {
                if (($p_remove_path != '')
                    && (substr($v_header['filename'], 0, $p_remove_path_size)
                        == $p_remove_path)
                )
                    $v_header['filename'] = substr($v_header['filename'],
                        $p_remove_path_size);
                if (($p_path != './') && ($p_path != '/')) {
                    while (substr($p_path, -1) == '/')
                        $p_path = substr($p_path, 0, strlen($p_path) - 1);

                    if (substr($v_header['filename'], 0, 1) == '/')
                        $v_header['filename'] = $p_path . $v_header['filename'];
                    else
                        $v_header['filename'] = $p_path . '/' . $v_header['filename'];
                }
                if (file_exists($v_header['filename'])) {
                    if ((@is_dir($v_header['filename']))
                        && ($v_header['typeflag'] == '')
                    ) {
                        $this->error('File ' . $v_header['filename']
                            . ' already exists as a directory');
                        return false;
                    }
                    if (($this->isArchive($v_header['filename']))
                        && ($v_header['typeflag'] == "5")
                    ) {
                        $this->error('Directory ' . $v_header['filename']
                            . ' already exists as a file');
                        return false;
                    }
                    if (!is_writeable($v_header['filename'])) {
                        $this->error('File ' . $v_header['filename']
                            . ' already exists and is write protected');
                        return false;
                    }
                    if (filemtime($v_header['filename']) > $v_header['mtime']) {
                        // To be completed : An error or silent no replace ?
                    }
                } // ----- Check the directory availability and create it if necessary
                elseif (($v_result
                    = $this->dirCheck(($v_header['typeflag'] == "5"
                    ? $v_header['filename']
                    : dirname($v_header['filename'])))) != 1
                ) {
                    $this->error('Unable to create path for ' . $v_header['filename']);
                    return false;
                }

                if ($v_extract_file) {
                    if ($v_header['typeflag'] == "5") {
                        if (!@file_exists($v_header['filename'])) {
                            if (!@mkdir($v_header['filename'], 0777)) {
                                $this->error('Unable to create directory {'
                                    . $v_header['filename'] . '}');
                                return false;
                            }
                        }
                    } elseif ($v_header['typeflag'] == "2") {
                        if (@file_exists($v_header['filename'])) {
                            @unlink($v_header['filename']);
                        }
                        if (!@symlink($v_header['link'], $v_header['filename'])) {
                            $this->error('Unable to extract symbolic link {'
                                . $v_header['filename'] . '}');
                            return false;
                        }
                    } else {
                        if (($v_dest_file = @fopen($v_header['filename'], "wb")) == 0) {
                            $this->error('Error while opening {' . $v_header['filename']
                                . '} in write binary mode');
                            return false;
                        } else {
                            $n = floor($v_header['size'] / 512);
                            for ($i = 0; $i < $n; $i++) {
                                $v_content = $this->readBlock();
                                fwrite($v_dest_file, $v_content, 512);
                            }
                            if (($v_header['size'] % 512) != 0) {
                                $v_content = $this->readBlock();
                                fwrite($v_dest_file, $v_content, ($v_header['size'] % 512));
                            }

                            @fclose($v_dest_file);

                            // ----- Change the file mode, mtime
                            @touch($v_header['filename'], $v_header['mtime']);
                            if ($v_header['mode'] & 0111) {
                                // make file executable, obey umask
                                $mode = fileperms($v_header['filename']) | (~umask() & 0111);
                                @chmod($v_header['filename'], $mode);
                            }
                        }

                        // ----- Check the file size
                        clearstatcache();
                        if (filesize($v_header['filename']) != $v_header['size']) {
                            $this->error('Extracted file ' . $v_header['filename']
                                . ' does not have the correct file size \''
                                . filesize($v_header['filename'])
                                . '\' (' . $v_header['size']
                                . ' expected). Archive may be corrupted.');
                            return false;
                        }
                    }
                } else {
                    $this->jumpBlock(ceil(($v_header['size'] / 512)));
                }
            } else {
                $this->jumpBlock(ceil(($v_header['size'] / 512)));
            }

            if ($v_listing || $v_extract_file || $v_extraction_stopped) {
                $plistDetail[$v_nb++] = $v_header;
                if (is_array($p_file_list) && (count($plistDetail) == count($p_file_list))) {
                    return true;
                }
            }
        }

        return true;
    }

    protected function dirCheck($p_dir)
    {
        clearstatcache();
        if ((@is_dir($p_dir)) || ($p_dir == ''))
            return true;

        $p_parent_dir = dirname($p_dir);

        if (($p_parent_dir != $p_dir) &&
            ($p_parent_dir != '') &&
            (!$this->dirCheck($p_parent_dir))
        )
            return false;

        if (!@mkdir($p_dir, 0777)) {
            $this->error("Unable to create directory '$p_dir'");
            return false;
        }

        return true;
    }

    protected function pathReduction($p_dir)
    {
        $v_result = '';

        // ----- Look for not empty path
        if ($p_dir != '') {
            // ----- Explode path by directory names
            $v_list = explode('/', $p_dir);

            // ----- Study directories from last to first
            for ($i = sizeof($v_list) - 1; $i >= 0; $i--) {
                // ----- Look for current path
                if ($v_list[$i] == ".") {
                    // ----- Ignore this directory
                    // Should be the first $i=0, but no check is done
                } else if ($v_list[$i] == "..") {
                    // ----- Ignore it and ignore the $i-1
                    $i--;
                } else if (($v_list[$i] == '')
                    && ($i != (sizeof($v_list) - 1))
                    && ($i != 0)
                ) {
                    // ----- Ignore only the double '//' in path,
                    // but not the first and last /
                } else {
                    $v_result = $v_list[$i] . ($i != (sizeof($v_list) - 1) ? '/'
                        . $v_result : '');
                }
            }
        }
        $v_result = strtr($v_result, '\\', '/');
        return $v_result;
    }

    protected function translateWinPath($p_path, $p_remove_disk_letter = true)
    {
        if (defined('OS_WINDOWS') && OS_WINDOWS) {
            // ----- Look for potential disk letter
            if (($p_remove_disk_letter)
                && (($v_position = strpos($p_path, ':')) != false)
            ) {
                $p_path = substr($p_path, $v_position + 1);
            }
            // ----- Change potential windows directory separator
            if ((strpos($p_path, '\\') > 0) || (substr($p_path, 0, 1) == '\\')) {
                $p_path = strtr($p_path, '\\', '/');
            }
        }
        return $p_path;
    }
}