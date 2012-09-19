<?php
class	Shopware_Components_File_Entry_Tar
{
	protected $position;
	protected $stream;
	protected $entry;
	
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