<?php

/*
 * This file is part of the HDIV bundle.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HDIV\SecurityBundle\HDIVCore;

use Symfony\Component\HttpKernel\Config\FileLocator;
use HDIV\SecurityBundle\HDIVCore\EditableValidation;

/**
 * Class HDIVConfig
 * @package HDIV\SecurityBundle\HDIVCore
 */
class HDIVConfig
{

	private $startPagesArray;
	private $startParemetersArray;
	private $isEditableValidationEnabled;
	private $isDebugModeEnabled;
	private $maxPagesPerSession;
	private $excludedExtensionsArray;
	private $defaultBlackListRules;
	private $userValidationRules;
	private $isRandomNameEnabled;
	private $isHdivEnabled;
	private $session;

	/**
	 * Array with URL pattern as key and array of validations as value to apply to that URL pattern
	 */
	private $editableValidations;


	public function __construct($path, FileLocator $fileLocator, $session) {

		$this->session = $session;
		$this->loadBlackListRulesFromFile($fileLocator);

		// Load user config
		$array = array_values($path);
		$xml=simplexml_load_file($array[0].'/Resources/hdivconfig.xml');

		//Gets Users EditableValidations
		foreach($xml->validations->validation as $child) {

			$validationAttributes = $child->attributes();

			$validationRuleName = (string) $validationAttributes["name"];

			$acceptedPattern = $child->acceptedPattern->__toString();
			$rejectedPattern = $child->rejectedPattern->__toString();

			$newEditableValidation = new EditableValidation(trim($validationRuleName), $acceptedPattern, $rejectedPattern);
			$this->userValidationRules[trim($validationRuleName)] = $newEditableValidation;
		}

		//Gets EditableValidations with urls
		$editableValidationAttributes = $xml->editableValidations->attributes();
		$this->isEditableValidationEnabled = (string) $editableValidationAttributes["enabled"];

		foreach($xml->editableValidations->validationRule as $child) {

			$validationRuleAttributes = $child->attributes();
			$validationRuleUrl = (string) $validationRuleAttributes["url"];
			$validationRuleEnableDefault = (string) $validationRuleAttributes["enableDefaultBlackListRules"];

			$validationsArray = [];
			if ($validationRuleEnableDefault == 'true') {
				$validationsArray = $this->defaultBlackListRules;
			}

			$rules = $child->__toString();
			if (strlen($rules) > 0) {
				$rulesArray = explode(",", $rules);
				foreach($rulesArray as $value) {

					$validationsArray[] = $this->userValidationRules[$value];
				}
			}

			$urlPattern = $this->transformPattern($validationRuleUrl);
			$this->editableValidations[$urlPattern] = $validationsArray;
		}

		//Order editable validations
		$keys = array_map('strlen', array_keys($this->editableValidations));
		array_multisort($keys, SORT_DESC, $this->editableValidations);

		//Gets from XML startPages
		$startPagesArray = array();
		foreach($xml->startPages->startPage as $child) {

			$startPage = $this->transformPattern($child->__toString());
			$this->startPagesArray[] = $startPage;
		}

		//Gets from XML startParameters
		$startParemetersArray = array();
		foreach($xml->startParameters->startParameter as $child) {

			$startParameter = $this->transformPattern($child->__toString());
			$this->startParemetersArray[] = $startParameter;
		}

		//Gets from XML excludedExtensions
		foreach($xml->excludedExtensions->excludedExtension as $excludedExtension) {

			$this->excludedExtensionsArray[] = $excludedExtension->__toString();
		}

		//Gets from XML maxPagesPerSession value
		$this->maxPagesPerSession = $xml->maxPagesPerSession->__toString();

		//Gets debug mode value
		$this->isDebugModeEnabled = $xml->debugMode->__toString();

		//Gets hdiv enabled value
		$this->isHdivEnabled = $xml->hdivEnabled->__toString();

		//Gets random name value
		$this->isRandomNameEnabled = $xml->randomName->__toString();
	}

	/**
	 * Load blacklist rules from xml file
	 * @param FileLocator $fileLocator
	 */
	private function loadBlackListRulesFromFile(FileLocator $fileLocator) {

		// Get path of default validation rules from file
		$defaultBlackListRulesPath = $fileLocator->locate('@HDIVSecurityBundle/Resources/config/defaultBlackListRules.xml');

		// Load default black list xml
		$xml=simplexml_load_file($defaultBlackListRulesPath);

		// Gets default EditableValidations from xml
		foreach($xml->validation as $child) {

			$validationAttributes = $child->attributes();

			$validationRuleName = (string) $validationAttributes["name"];
			$rejectedPattern = $child->rejectedPattern->__toString();

			$newEditableValidation = new EditableValidation(trim($validationRuleName), NULL, $rejectedPattern);
			$this->defaultBlackListRules[trim($validationRuleName)] = $newEditableValidation;
		}

	}
	
	/**
	 * @param $pattern
	 * @return mixed|string
	 */
	public function transformPattern($pattern) {

		return $pattern;
	}

	/**
	 * Checks if $haystack endsWith $needle
	 *
	 * @param $haystack
	 * @param $needle
	 * @return bool
	 */
	public function endsWith($haystack, $needle) {
		return $needle === "" || strpos($haystack, $needle, strlen($haystack) - strlen($needle)) !== FALSE;
	}

	/**
	 * Checks if <code>$uri</code> is an init action, in which case it will not be treated by HDIV.
	 * @param $uri
	 * @return bool
	 */
	public function isStartPage($uri) {

		$isStartPage = FALSE;
		foreach($this->startPagesArray as $startPage) {
			if (preg_match($startPage,$uri)) {

				$isStartPage = TRUE;
			}
		}
		return $isStartPage;
	}

	/**
	 * Checks if <code>$parameter</code> is a startParemeter, in which case it will not be treated by HDIV.
	 * @param $parameter
	 * @return bool
	 */
	public function isStartParameter($parameter) {

		$isStartParameter = FALSE;
		foreach($this->startParemetersArray as $startParameter) {
			if (preg_match($startParameter,$parameter)) {

				$isStartParameter = TRUE;
			}
		}
		return $isStartParameter;
	}


	/**
	 * Get StartPages
	 * @param $uri
	 * @return bool
	 */
	public function getStartPagesArray(){
		return $this->startPagesArray;
	}


	/**
	 * Get StartParameters
	 * @param $uri
	 * @return bool
	 */
	public function getStartParametersArray(){
		return $this->startParemetersArray;
	}

	/**
	 * Gets default validation rules
	 * @return defaultBlackListRules
	 */
	public function getDefaultRules(){
		return $this->defaultBlackListRules;
	}

	/**
	 * Get MaxPagesPerSession
	 * @param $uri
	 * @return bool
	 */
	public function getMaxPagesPerSession(){
		return $this->maxPagesPerSession;
	}

	/**
	 * Checks if editableValidation is enabled
	 * @param $uri
	 * @return bool
	 */
	public function isEditableValidationEnabled(){

		return $this->isEditableValidationEnabled;
	}

	/**
	 * Checks if debugMode is enabled
	 * @param $uri
	 * @return bool
	 */
	public function isDebugModeEnabled(){

		if ($this->isDebugModeEnabled=='True') {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	/**
	 * Checks if hdiv is enabled
	 * @param $uri
	 * @return bool
	 */
	public function isHdivEnabled(){

		if ($this->isHdivEnabled=='True') {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	/**
	 * Gets Hdiv State name
	 * @param $uri
	 * @return bool
	 */
	public function getHdivStateName(){

		if ($this->isRandomNameEnabled=='True') {

            $sessionRandomName = $this->session->get('hdiv-randomName');

            if (strlen($sessionRandomName) == 0) {
                $sessionRandomName = substr(strtoupper(md5(uniqid(rand(), true))), 0 , 5);
                $this->session->set('hdiv-randomName',  $sessionRandomName, 0 , 5);
             
            } 

            return $sessionRandomName;

		} else {

			return "_HDIV_STATE_";
		}
	}

	/**
	 * Checks if <code>$uri</code> has a excluded extension
	 * @param $uri
	 * @return bool
	 */
	public function hasExcludedExtension($uri){
		$hasExcluded = FALSE;
		foreach($this->excludedExtensionsArray as $excluded) {
			if (strlen($uri) > 1 && $this->endsWith($uri, $excluded)) {
				$hasExcluded = TRUE;
			}
		}
		return $hasExcluded;
	}

	/**
	 * @return mixed
	 */
	public function getEditableValidations()
	{
		return $this->editableValidations;
	}

	/**
	 * @param mixed $editableValidations
	 */
	public function setEditableValidations($editableValidations)
	{
		$this->editableValidations = $editableValidations;
	}


}