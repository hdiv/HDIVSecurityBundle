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
 * Class State
 * @package HDIV\SecurityBundle\HDIVCore\State
 */
class State
{
	private $id;

	private $url;

    private $parameters;

	private $stateToken;

	public function __construct($pageId, $id, $pageRandomToken){
		$this->id = $id;
		$this->stateToken = $pageId.'-'.$this->id.'-'.$pageRandomToken;
        $this->paremeters= array();
	}

	public function getStateToken(){
		return $this->stateToken;
	}

	public function getUrl(){
		return $this->url;
	}

    public function setUrl($url) {
        $this->url = $url;
    }

    public function getParameters() {
        return $this->parameters;
    }

    public function addParameter($key, $value) {

        $this->parameters[$key] = $value;
    }

    public function getSizeParameters(){
        return count($this->parameters);
    }
}