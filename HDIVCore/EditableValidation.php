<?php

/*
 * This file is part of the HDIV bundle.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HDIV\SecurityBundle\HDIVCore;

/**
 * Class EditableValidation
 * @package HDIV\SecurityBundle\HDIVCore
 */
class EditableValidation
{
	private $name;

	private $acceptedPattern;

	private $rejectedPattern;

	/**
	 * EditableValidation constructor.
	 * @param $name
	 * @param $acceptedPattern
	 * @param $rejectedPattern
	 */
	public function __construct($name, $acceptedPattern, $rejectedPattern)
	{
		$this->name = $name;
		$this->acceptedPattern = $acceptedPattern;
		$this->rejectedPattern = $rejectedPattern;
	}


	/**
	 * @return mixed
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param mixed $name
	 */
	public function setName($name)
	{
		$this->name = $name;
	}

	/**
	 * @return mixed
	 */
	public function getAcceptedPattern()
	{
		return $this->acceptedPattern;
	}

	/**
	 * @param mixed $acceptedPattern
	 */
	public function setAcceptedPattern($acceptedPattern)
	{
		$this->acceptedPattern = $acceptedPattern;
	}

	/**
	 * @return mixed
	 */
	public function getRejectedPattern()
	{
		return $this->rejectedPattern;
	}

	/**
	 * @param mixed $rejectedPattern
	 */
	public function setRejectedPattern($rejectedPattern)
	{
		$this->rejectedPattern = $rejectedPattern;
	}
}