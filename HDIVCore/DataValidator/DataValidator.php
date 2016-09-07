<?php

/*
 * This file is part of the HDIV bundle.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HDIV\SecurityBundle\HDIVCore\DataValidator;

use HDIV\SecurityBundle\HDIVCore\Session\StateCache;
use HDIV\SecurityBundle\HDIVCore\State\Page;
use HDIV\SecurityBundle\HDIVCore\State\Parameter;

/**
 * Class DataValidator
 * @package HDIV\SecurityBundle\HDIVCore\DataValidator
 */
class DataValidator
{
	private $session;
    private $actualParameters;
    private $logger;

	public function __construct($session, $logger)
	{
		$this->session = $session;
        $this->logger = $logger;

    }

    /**
     * Validates a URL
     *
     * @param $uri
     * @param $tokenHDIVAct
     * @return bool
     */
	public function validateUrl($uri, $tokenHDIVAct)
	{
		$stateCache = $this->session->get('StateCache');
		$validation = False; 
		$firstPageId = $stateCache->getFirst();
		$lastPageId = $stateCache->getLast();

		for ($i = $firstPageId; $i <= $lastPageId; $i++) { 

			$page = $this->session->get('Page '.$i);
			$pageStates = $page->getStates();

			if (is_array($pageStates)) {	
				foreach ($pageStates as $state) {
					$valorToken = $state->getStateToken();

					if ($valorToken===$tokenHDIVAct) {
						$valorUrl = $state->getUrl();

						if ($valorUrl===$uri) {
							$validation=True;
						}
					}
				}
			}
		}

        if (!$validation) {
            $this->logger->error('Hdiv Logger. Url validation | Requested URL: '. $uri.' | IP: '.$_SERVER['REMOTE_ADDR']);
        }

		return $validation;
	}


    /**
     * Validates a Form
     *
     * @param $formName
     * @param $formParameters
     * @param $tokenHDIVAct
     * @return bool
     */
    public function validateForm($formName, $formParameters, $tokenHDIVAct) {

        $stateCache = $this->session->get('StateCache');
        $validation = True;
        $firstPageId = $stateCache->getFirst();
        $lastPageId = $stateCache->getLast();

        for ($i = $firstPageId; $i <= $lastPageId; $i++) {

            $page = $this->session->get('Page '.$i);
            $pageStates = $page->getStates();

            if (is_array($pageStates)) {
                foreach ($pageStates as $state) {
                    $valorToken = $state->getStateToken();
                    if ($valorToken===$tokenHDIVAct) {

                        $valorUrl = $state->getUrl();

                        if ($valorUrl!=$_SERVER['REQUEST_URI']) {

                            $this->logger->error('Hdiv Logger. Form validation | IP: '.$_SERVER['REMOTE_ADDR']);

                            return False;
                        }
                        $sessionParameters =  $state->getParameters();
                        $this->actualParameters = array();
                        $this->generateStructure($formParameters, $formName);

                        $validation = $this->compareSessionParametersWithActualParameters($sessionParameters, $this->actualParameters);

                        if (!$validation) {
                            $this->logger->error('Hdiv Logger. Form validation | IP: '.$_SERVER['REMOTE_ADDR']);
                        }

                        return $validation;
                    }
                }
            }
        }

        $this->logger->error('Hdiv Logger. Form validation | IP: '.$_SERVER['REMOTE_ADDR']);
        return False;

    }

    /**
     * Generates a Symfony's form tree (structure)
     * @param $formParameters
     * @param $formName
     */
    public function generateStructure($formParameters, $formName ) {

        foreach ($formParameters as $key => $value) {
            if($key!='_token') { //EXCLUDE CSRF PROTECTION
                $key = $formName.'['.$key.']';
                if (is_array($value)){
                    $this->generateStructure($value, $key);
                }else {
                    $param = new Parameter($key, 'request', $value);
                    $this->actualParameters[$key]=$param;

                }
            }
        }
    }


    /**
     * Compare session parameters with the given parameters
     *
     * @param $sessionArray
     * @param $actualParameters
     * @return bool
     */
    public function compareSessionParametersWithActualParameters($sessionArray, $actualParameters) {

        if (count($sessionArray) < count($actualParameters)) {
            return False;
        } else {

            foreach ($actualParameters as $keyActual => $valueActual) {
               $val = False;
                foreach ($sessionArray as $keySession => $valueSession) {
                    if($keyActual===$keySession) {
                        if($valueSession->getType()=='hidden') {
                            if($valueActual->getValue()==$valueSession->getValue()){
                                $val = True;
                            } else {
                                return False;
                            }
                        } else{
                            $val = True;
                        }
                    }
                }

                if($val==False) {
                    return False;
                }
            }
            return True;
        }
    }
}