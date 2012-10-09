<?php
abstract class Shopware_Components_Archive_Adapter implements SeekableIterator, Countable
{
    protected $position;
    protected $count;

    public function seek($position)
    {
        $this->position = (int)$position;
    }

    public function count()
    {
        return $this->count;
    }

    public function rewind()
    {
        $this->position = 0;
    }

    public function key()
    {
        return $this->position;
    }

    public function next()
    {
        ++$this->position;
    }

    public function valid()
    {
        return $this->count > $this->position;
    }

    public function each()
    {
        if (!$this->valid()) {
            return false;
        }
        $result = array($this->key(), $this->current());
        $this->next();
        return $result;
    }
}