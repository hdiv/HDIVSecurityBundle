<?php

/*
 * This file is part of the HDIV bundle.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HDIV\SecurityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class UnauthorizedController
 * @package HDIV\SecurityBundle\Controller
 */
class UnauthorizedController extends Controller
{
    public function indexAction()
    {
        return $this->render('HDIVSecurityBundle:Unauthorized:unauthorized.html.twig');
    }
}
