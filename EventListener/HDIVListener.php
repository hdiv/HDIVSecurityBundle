<?php

/*
 * This file is part of the HDIV bundle.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HDIV\SecurityBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use HDIV\SecurityBundle\HDIVCore\DataComposer\DataComposerMemory;
use HDIV\SecurityBundle\HDIVCore\DataValidator\DataValidator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use HDIV\SecurityBundle\HDIVCore\HDIVConfig;


/**
 * Class HDIVListener
 * @package HDIV\SecurityBundle\EventListener
 */
class HDIVListener
{
	private $dataValidator;
    private $dataComposerMemory;
    private $HDIVConfig;
    private $router;
    private $logger;

    public function __construct(DataValidator $dataValidator,
        DataComposerMemory $dataComposerMemory, HDIVConfig $HDIVConfig, $router, $logger)
    {
        $this->dataValidator = $dataValidator;
        $this->dataComposerMemory = $dataComposerMemory;
        $this->HDIVConfig = $HDIVConfig;
        $this->router = $router;
        $this->logger = $logger;
    }


 	public function onKernelRequest(GetResponseEvent $event)
    {

        if (!$this->HDIVConfig->isHdivEnabled()) {
            return;
        }
        
        $request = $event->getRequest();
        $uri = str_replace('http://'.$_SERVER['HTTP_HOST'],'', $request->getRequestUri());

        if ($this->HDIVConfig->hasExcludedExtension($uri)) {
            return;
        }

        if ($this->HDIVConfig->isStartPage($uri)) {
            return;
        }

        $validation = $this->validate($request, $uri);

        if (!$validation && !$this->HDIVConfig->isDebugModeEnabled()) {
            $event->setResponse(new RedirectResponse($this->router->generate('hdivsecurity_homepage')));
        }

        $this->dataComposerMemory->addNewPage();

    }

    /**
     * Validates request parameters
     *
     * @param $request
     * @param $uri
     * @return bool
     */
    public function validate($request, $transformedUri)
    {
        $HDIVToken = $request->query->get($this->HDIVConfig->getHdivStateName());

        if (isset($HDIVToken)) { //It's a GET URL.

            return $this->dataValidator->validateUrl($transformedUri, $HDIVToken);

        } else {

            $array = $request->request->keys();

            if (count($array) == 1) {

                $formName = array_shift($array);
                $values = $request->request->get($formName);
                $HDIVToken = $values[$this->HDIVConfig->getHdivStateName()];
                $list = $request->request->all();
                $formParameters = array_shift($list);

                if (isset($HDIVToken)) { //It's a POST FORM.

                    return $this->dataValidator->validateForm($formName, $formParameters, $HDIVToken);

                } else { //It's a GET FORM.

                    $param = $request->query->get($formName);

                    return $this->dataValidator->validateForm($formName, $param, $HDIVToken);

                }

            } else {
                $this->logger->log('HDIV_PARAMETER_NOT_EXISTS', $transformedUri, $_SERVER['REMOTE_ADDR'], null, null, null, null);
                return false;
            }
        }
    }
}
