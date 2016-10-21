<?php

/*
 * This file is part of the HDIV bundle.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HDIV\SecurityBundle\HDIVCore\Logger;

/**
 * Class HDIVLogger
 * @package HDIV\SecurityBundle\HDIVCore\Logger
 */
class HDIVLogger
{

	private $logger;
	private $hdivLoggerFormatter;

	public function __construct($path, $logger) {

		$this->logger = $logger;

		// Load user config
		$array = array_values($path);

		if (file_exists($array[0].'/Resources/hdivloggerconfig.properties')) {

			$hdivLogger = file($array[0].'/Resources/hdivloggerconfig.properties');
			$this->hdivLoggerFormatter = implode('', $hdivLogger);
			
		} else {

			$this->hdivLoggerFormatter = 'Hdiv Security;[type of attack];[url];[Ip];[fieldName];[fieldType];[fieldValue];[ruleName]';
		}
	}



	public function log($attack, $url, $Ip, $fieldName, $fieldType, $fieldValue, $ruleName) {

		$this->hdivLoggerFormatter = str_replace("[type of attack]", $attack, $this->hdivLoggerFormatter);
		$this->hdivLoggerFormatter = str_replace("[url]", $url, $this->hdivLoggerFormatter);
		$this->hdivLoggerFormatter = str_replace("[Ip]", $Ip, $this->hdivLoggerFormatter);
		$this->hdivLoggerFormatter = str_replace("[fieldName]", $fieldName, $this->hdivLoggerFormatter);
		$this->hdivLoggerFormatter = str_replace("[fieldType]", $fieldType, $this->hdivLoggerFormatter);
		$this->hdivLoggerFormatter = str_replace("[fieldValue]", $fieldValue, $this->hdivLoggerFormatter);
		$this->hdivLoggerFormatter = str_replace("[ruleName]", $ruleName, $this->hdivLoggerFormatter);

		$this->logger->error($this->hdivLoggerFormatter);
	}

}