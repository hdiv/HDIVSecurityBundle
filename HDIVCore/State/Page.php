<?php

/*
 * This file is part of the HDIV bundle.
 *
 * (c) Fernando Lozano
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HDIV\SecurityBundle\HDIVCore\State;

/**
 * Class Page
 * @package HDIV\SecurityBundle\HDIVCore\State
 */
class Page
{

	private $states;

	private $id;

	private $randomToken;

	public function __construct($id)
	{
		$states = array();
		$this->id = $id;
		$this->randomToken = strtoupper(md5(uniqid(rand(), true)));
	}

	public function getSize(){
		return count($this->states);
	}

	public function getId(){
		return $this->id;
	}

	public function getRandomToken(){
		return $this->randomToken;
	}

	public function addState($state){
		$this->states[] = $state;
	}

	public function getStates(){
		return $this->states;
	}

	public function getName(){
		return 'Page '.$this->id;
	}

}