<?php

/*
 * This file is part of the HDIV bundle.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HDIV\SecurityBundle\HDIVCore\State;

/**
 * Class Parameter
 * @package HDIV\SecurityBundle\HDIVCore\State
 */
class Parameter
{
    private $value;

    private $type;

    private $name;

	public function __construct($name, $type, $value){
        $this->name = $name;
        $this->type = $type;
        $this->value = $value;
	}

    public function getName(){
        return $this->name;
    }

    public function getType(){
        return $this->type;
    }

    public function getValue(){
        return $this->value;
    }
}