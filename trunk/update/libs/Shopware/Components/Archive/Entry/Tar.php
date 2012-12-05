<?php
class Shopware_Components_File_Entry_Tar
{
    /**
     * @var int
     */
    protected $position;

    /**
     * @var Shopware_Components_Tar
     */
    protected $stream;

    /**
     * @var array
     */
    protected $entry;

    /**
     * @param Shopware_Components_Tar $stream
     * @param int $position
     */
    public function __construct($stream, $position)
	{
		$this->position = $position;
		$this->stream = $stream;
		$this->entry = $stream->getEntry($position);
	}
		
	public function getStream()
	{
		$content = $this->stream->getContents($this->position);
		$handle = fopen('php://temp', 'r+');
		fwrite($handle, $content);
		rewind($handle);
		return $handle;
	}
	
	public function getContents()
	{
		return $this->stream->getContents($this->position);
	}
	
	public function isDir()
	{
		return $this->entry['typeflag'] == 5;
	}
	
	public function isFile()
	{
		return $this->entry['typeflag'] == 0;
	}
	
	public function getName()
	{
		return $this->entry['filename'];
	}
}