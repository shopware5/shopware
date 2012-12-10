<?php
class Observer {
	
	private $stack = array();
	
	/**
	 * 
	 * Update registered Observers
	 * @param string $key
	 * @param string $message
	 * @param Observable $observable
	 */
	public function update($key, $message, Observable $observable) {
		$this->stack[$key][0] = $message;
		//$this->stack[$key][1] = $observable;
	}
}