<?php

/*
 * This file is part of the HDIV bundle.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HDIV\SecurityBundle\HDIVCore;

/**
 * Class HDIVConfig
 * @package HDIV\SecurityBundle\HDIVCore
 */
class HDIVConfig
{

	private $startPagesArray;
	private $isEditableValidationEnabled;
	private $isDebugModeEnabled;
	private $maxPagesPerSession;
	private $excludedExtensionsArray;
	private $excludedPagesArray;

	public function __construct($path) {

		$array = array_values($path);
		$xml=simplexml_load_file($array[0].'/Resources/hdivconfig.xml');

		$startPagesArray = array();

		//Gets from XML startPages
		foreach($xml->startPages->startPage as $child) {

			$startPage = $this->transformPattern($child->__toString());
			$this->startPagesArray[] = $startPage;
		}

		//Gets from XML excludedExtensions
		foreach($xml->excludedExtensions->excludedExtension as $excludedExtension) {

			$this->excludedExtensionsArray[] = $excludedExtension->__toString();
		}

		//Gets from XML excludedPaths
		foreach($xml->excludedPages->excludedPage as $child) {

			$excludedPage = $this->transformPattern($child->__toString());
			$this->excludedPagesArray[] = $excludedPage;
		}


		//Gets from XML maxPagesPerSession value
		$this->maxPagesPerSession = $xml->maxPagesPerSession->__toString();

		//Gets editable validation value
		$this->isEditableValidationEnabled = $xml->editableValidation->__toString();

		//Gets debug mode value
		$this->isDebugModeEnabled = $xml->debugMode->__toString();
	}

	/**
	 * @param $pattern
	 * @return mixed|string
	 */
	public function transformPattern($pattern) {
		if (strlen($pattern) > 1 && $this->endsWith($pattern, "/.*")) {
			$pattern = str_replace(".*", "", $pattern);
		} else {

			$pattern = $pattern.'$';
		}

		$pattern = '#^'.$pattern.'#';
		$pattern = str_replace(".", "\.", $pattern);
		$pattern = str_replace("/\.", "/.", $pattern);

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
	 * Checks if <code>$uri</code> is an excluded URI.
	 * @param $uri
	 * @return bool
	 */
	public function isExcludedPage($uri) {

		$isExcludedPage = FALSE;
		foreach($this->excludedPagesArray as $excludedPage) {
			if (preg_match($excludedPage, $uri)) {

				$isExcludedPage = TRUE;
			}
		}
		return $isExcludedPage;
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

		if ($this->isEditableValidationEnabled=='True') {
			return TRUE;
		} else {
			return FALSE;
		}
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
}