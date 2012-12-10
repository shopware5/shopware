<?php
interface Observable {
	public function attach(Observer $Observer);
	public function detach(Observer $Observer);
	public function notify($key, $message = '');
}