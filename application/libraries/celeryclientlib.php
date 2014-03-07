<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class CeleryClientLib 
{

	function CeleryClientLib()
	{
		log_message("info", "constructor of celeryclientlib");
		$this->ci =& get_instance();
		$celeryParams = array('host'=>'localhost', 'login'=>'guest', 'password' => 'guest', 'vhost' => '/');
		$this->ci->load->library('celery', $celeryParams);
		$this->ci->load->library('celery/celeryexception');
		$this->ci->load->library('celery/celerytimeoutexception');
	}

	function test()
	{
		echo "celeryclientlib library test function <br>";
	}

	// Check if broker is running on host and port.
	// broker : rabbitmq 
	function getBrokerStatus()
	{
		try {
			$amqpConnection = new AMQPConnection();
			$amqpConnection->setLogin("guest");
			$amqpConnection->setPassword("guest");
			$amqpConnection->setVhost("/");
			$amqpConnection->connect();
		} catch (Exception $e) {
			log_message("info", "Exception: " . $e->getMessage());
			return false;
		}

		if (!$amqpConnection->isConnected()) {
			log_message("info", "Cannot connect to the broker!");
			return false;
		}
		$amqpConnection->disconnect();
		return true;
	}

}

