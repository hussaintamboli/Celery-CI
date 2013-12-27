<?php

/**
 * This file contains a PHP client to Celery distributed task queue
 *
 * LICENSE: 2-clause BSD
 *
 * Copyright (c) 2012, GDR!
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met: 
 * 
 * 1. Redistributions of source code must retain the above copyright notice, this
 *    list of conditions and the following disclaimer. 
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 *    this list of conditions and the following disclaimer in the documentation
 *    and/or other materials provided with the distribution. 
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
 * ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 * 
 * The views and conclusions contained in the software and documentation are those
 * of the authors and should not be interpreted as representing official policies, 
 * either expressed or implied, of the FreeBSD Project. 
 *
 * @link http://massivescale.net/
 * @link http://gdr.geekhood.net/
 * @link https://github.com/gjedeer/celery-php
 *
 * @package celery-php
 * @license http://opensource.org/licenses/bsd-license.php 2-clause BSD
 * @author GDR! <gdr@go2.pl>
 */

/**
 * Client for a Celery server
 * @package celery-php
 */
class Celery
{
	private $connection = null; // AMQPConnection object
	private $connection_details = array(); // array of strings required to connect

	function __construct($params)
	{
		$exchange = 'celery';
		$binding = 'celery';
		$port = 5672;
		$host = $params['host'];
		$login = $params['login'];
		$password = $params['password'];
		$vhost = $params['vhost'];
		
		if(!class_exists('AMQPConnection'))
		{
            throw new CeleryException("Class AMQPConnection not found\nMake sure that AMQP extension is installed and enabled:\nhttp://www.php.net/manual/en/amqp.installation.php");
		}

		foreach(array('host', 'login', 'password', 'vhost', 'exchange', 'binding', 'port') as $detail)
		{
			$this->connection_details[$detail] = $$detail;
		}

		$this->connection = Celery::InitializeAMQPConnection($this->connection_details);

		#$success = $this->connection->connect();
	}

	static function InitializeAMQPConnection($details)
	{
		$connection = new AMQPConnection();
		$connection->setHost($details['host']);
		$connection->setLogin($details['login']);
		$connection->setPassword($details['password']);
		$connection->setVhost($details['vhost']);
		$connection->setPort($details['port']);

		return $connection;
	}

	/**
	 * Post a task to Celery
	 * @param string $task Name of the task, prefixed with module name (like tasks.add for function add() in task.py)
	 * @param array $args Array of arguments (kwargs call when $args is associative)
	 * @return AsyncResult
	 */
	function PostTask($task, $args)
	{
		$this->connection->connect();
		if(!is_array($args))
		{
			throw new CeleryException("Args should be an array");
		}
		$id = uniqid('php_', TRUE);
		$ch = new AMQPChannel($this->connection);
		$xchg = new AMQPExchange($ch);
		$xchg->setName($this->connection_details['exchange']);

		/* $args is numeric -> positional args */
		if(array_keys($args) === range(0, count($args) - 1))
		{
			$kwargs = array();
		}
		/* $args is associative -> contains kwargs */
		else
		{
			$kwargs = $args;
			$args = array();
		}
                                                                            
		$task_array = array(
			'id' => $id,
			'task' => $task,
			'args' => $args,
			'kwargs' => (object)$kwargs,
		);
		$task = json_encode($task_array);
		$params = array('content_type' => 'application/json',
			'content_encoding' => 'UTF-8',
			'immediate' => false,
			);
                
		$success = $xchg->publish($task, $this->connection_details['binding'], 0, $params);
		$this->connection->disconnect();

		$params = array('id' => $id, 'connection_details' => $this->connection_details,
				'task_name' => $task_array['task'], 'task_args' => $args);
		$this->ci =& get_instance();
		return $this->ci->load->library('celery/asyncresult', $params);
// 		return new AsyncResult($id, $this->connection_details, $task_array['task'], $args);
	}
}


