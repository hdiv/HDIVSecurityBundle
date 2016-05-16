<?php

/*
 * This file is part of the HDIV bundle.
 *
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HDIV\SecurityBundle\HDIVCore\DataComposer;

use HDIV\SecurityBundle\HDIVCore\HDIVConfig;
use HDIV\SecurityBundle\HDIVCore\Session\StateCache;
use HDIV\SecurityBundle\HDIVCore\State\Page;
use HDIV\SecurityBundle\HDIVCore\State\State;
use HDIV\SecurityBundle\HDIVCore\State\Parameter;

/**
 * Class DataComposerMemory
 * @package HDIV\SecurityBundle\HDIVCore\DataComposer
 */
class DataComposerMemory
{
	private $session;

	private $maxPagesPerSession;

	private $stateCache;

	public function __construct($session, HDIVConfig $HDIVConfig)
	{
		$this->session = $session;
        $this->maxPagesPerSession= $HDIVConfig->getMaxPagesPerSession();
        $this->stateCache = $this->session->get('StateCache');

		if (!isset($this->stateCache)) {
			$this->stateCache = new StateCache();
		}
	}

    /**
     * Adds new page to session
     */
	public function addNewPage()
	{
		if ($this->maxPagesPerSession==$this->stateCache->getSize()) {
			$this->session->remove('Page '.$this->stateCache->removeFirst());
			$this->session->set('StateCache', $this->stateCache);
		}

		$this->stateCache->addPage();
		$this->session->set('StateCache', $this->stateCache);

		$this->page = new Page($this->stateCache->getLast());


		$this->session->set('Page '.$this->stateCache->getLast(), $this->page);
	}


    /**
     *  Adds a new url to the current page
     *
     * @param $url
     * @return string
     */
	public function addUrlToCurrentPage($url)
	{
        $this->stateCache = $this->session->get('StateCache');

		$numPage = $this->stateCache->getLast();
		$actPage = $this->session->get('Page '.$numPage);

		$newState = new State($numPage, $actPage->getSize() + 1, $actPage->getRandomToken());

		if (!strpos($url,'?')) {
			$url .= '?_HDIV_STATE_='.$newState->getStateToken();
		} else {
			$url .= '&_HDIV_STATE_='.$newState->getStateToken();
		}


        $newState->setUrl($url);

        $actPage->addState($newState);
        $this->session->set('Page '.$numPage, $actPage);



		return $url;
	}

    /**
     *
     * Adds a new form to the current page
     *
     * @param $form
     * @param $newAction
     * @return string
     */
    public function addFormToCurrentPage($form, $newAction)
    {
        $numPage = $this->session->get('StateCache')->getLast();
        $actPage = $this->session->get('Page '.$numPage);

        $newState = new State($numPage, $actPage->getSize() + 1, $actPage->getRandomToken());
        $newState->setUrl($newAction);

        $this->session->set('actualUrl', $newAction);

        $this->saveChildrenToState($form->all(), $newState, $form->getName());

        $actPage->addState($newState);
        $this->session->set('Page '.$numPage, $actPage);

        return $newState->getStateToken();
    }


    /**
     * Iterates Symfony's form and stores parameters in the page
     *
     * @param $children
     * @param State $newState
     * @param $parentName
     */
    public function saveChildrenToState($children, State $newState, $parentName)
    {
        foreach ($children as $ch) {
            $childrenName = $ch->getName();
            $name = $parentName.'['.$childrenName.']';
            $type = $ch->getConfig()->getType()->getName();

            if ($ch->all() && $type!='choice') {
                $this->saveChildrenToState($ch, $newState,$name);
            } else {

                if ($name==='form[_HDIV_STATE_]') {
                    $param = new Parameter($name, $type, $newState->getStateToken());
                }else if (!$ch->getData()) {
                    $param = new Parameter($name, $type, '');
                } else {
                    $param = new Parameter($name, $type, $ch->getData());
                }
                $newState->addParameter($name, $param);
            }
        }
    }

}