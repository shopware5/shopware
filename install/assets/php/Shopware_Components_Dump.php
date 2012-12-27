<?php
class Shopware_Components_Dump implements SeekableIterator, Countable
{
	protected $count;
	protected $stream;
	protected $position;
	protected $current;

	public function __construct($filename)
	{
		$this->stream = fopen($filename, 'rb');
		$this->position = 0;
		$this->count = 0;
		while (!feof($this->stream)) {
			stream_get_line($this->stream, 1000000, ";\n");
			$this->count++;
		}
		$this->rewind();
	}

	public function seek($position)
	{
		$this->position = (int) $position;
	}

	public function count()
	{
		return $this->count;
	}

	public function rewind()
	{
		rewind($this->stream);
		$this->current = stream_get_line($this->stream, 1000000, ";\n");
		$this->current = trim(preg_replace('#^\s*--[^\n\r]*#', '', $this->current));
		$this->position = 0;
	}

	public function current()
	{
		return $this->current;
	}

	public function key()
	{
		return $this->position;
	}

	public function next()
	{
		$this->current = stream_get_line($this->stream, 1000000, ";\n");
		$this->current = trim(preg_replace('#^\s*--[^\n\r]*#', '', $this->current));
		++$this->position;
	}

	public function valid()
	{
		return !feof($this->stream);
	}
}