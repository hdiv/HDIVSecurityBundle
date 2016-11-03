<?php

/*
 * This file is part of the HDIV bundle.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HDIV\SecurityBundle\Routing;

use Symfony\Bundle\FrameworkBundle\Routing\Router as BaseRouter;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\RequestContext;

/**
 * Class Router
 * @package HDIV\SecurityBundle\Routing
 */
class Router extends BaseRouter
{
    private $dataComposerMemory;
    private $HDIVConfig;

    public function __construct(ContainerInterface $container, $resource, array $options = array(), RequestContext $context = null)
    {
        parent::__construct($container, $resource, $options, $context);
        $this->dataComposerMemory = $container->get('hdiv_security_bundle.hdiv_data_composer_memory');
        $this->HDIVConfig = $container->get('hdiv_security_bundle.hdiv_config');
    }


    public function generate($name, $parameters = array(), $referenceType = self::ABSOLUTE_PATH)
    {

        $urlParent = parent::generate($name, $parameters, $referenceType);

        if (!$this->HDIVConfig->isHdivEnabled()) {
            return $urlParent;
        }
        
        $url = str_replace('http://'.$_SERVER['HTTP_HOST'],'',$urlParent);

        if (!$this->HDIVConfig->isStartPage($url) && !$this->HDIVConfig->hasExcludedExtension($url)) {
            $url = $this->dataComposerMemory->addUrlToCurrentPage($url);
        } else {
            $url = $urlParent;
        }
        
        return $url;
    }
}