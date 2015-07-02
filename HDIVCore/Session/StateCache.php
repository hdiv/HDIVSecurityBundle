<?php

/*
 * This file is part of the HDIV bundle.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HDIV\SecurityBundle\HDIVCore\Session;

/**
 * Class StateCache
 * @package HDIV\SecurityBundle\HDIVCore\Session
 */
class StateCache
{

	/**
	* Page's ids map
	*/
	private $pageIds;

	public function __construct()
	{
		$pageIds = array();
	}

	public function addPage()
	{
		if (empty($this->pageIds)) {
			$this->pageIds[] = 1;	
		} else {
			$this->pageIds[] = $this->getLast()+1;	
		}
	}

	public function getSize()
	{
		return count($this->pageIds);
	}

	public function getFirst()
	{
		return reset($this->pageIds);
	}

	public function getLast()
	{
		return end($this->pageIds);
	}

	public function removeFirst()
	{
		return array_shift($this->pageIds);
	}
}