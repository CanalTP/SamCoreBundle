<?php

namespace CanalTP\SamCoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Description of SamController
 *
 * @author akambi fagbohoun <contact@akambi-fagbohoun.com>
 * @author Kévin Ziemianski <kevin.ziemianski@canaltp.fr>
 */
class SamController extends Controller
{
    /**
     * Check permission for an object
     *
     * @param Object $object
     * @param string $permission The permission to check for
     *
     * @throws AccessDeniedException
     */
    protected function checkPermission($object, $permission)
    {
        if (false === $this->container->get('security.context')->isGranted($permission, $object)) {
            throw new AccessDeniedException();
        }
    }

    public function indexAction()
    {
        return $this->render('CanalTPSamCoreBundle:Sam:index.html.twig');
    }

//    public function appRenderAction()
//    {
//        $request = $this->get('request');
//        $businessComponent = $this->get('sam.business_component');
//
//        $find = array();
//        if (preg_match('/\/(\w*)/', $request->getPathInfo(), $find) == 1 && isset($find[1])) {
//            $appBusinessComponent = $businessComponent->getBusinessComponent($find[1]);
////            var_dump($appBusinessComponent->getMenuItems());
////            die(__CLASS__ . ' : ' . __LINE__);
//
//        }
//
//        $response = $this->get('kernel')->handle($request, \Symfony\Component\HttpKernel\HttpKernelInterface::SUB_REQUEST);
//
//        return $response->setContent($this->render('CanalTPSamCoreBundle:Default:index.html.twig', array(
//            'content' => $response->getContent(),
//        )));
//    }
}
