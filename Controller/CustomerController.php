<?php

namespace CanalTP\SamCoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use CanalTP\SamCoreBundle\Entity\Customer as CustomerEntity;
use CanalTP\SamCoreBundle\Entity\Application as ApplicationEntity;
use CanalTP\SamCoreBundle\Entity\Perimeter;
use CanalTP\SamCoreBundle\Form\Type\CustomerType;
use Doctrine\Common\Collections\Criteria;

/**
 * Description of CustomerController
 *
 * @author Kévin ZIEMIANSKI <kevin.ziemianski@canaltp.fr>
 */
class CustomerController extends AbstractController
{
    public function listAction()
    {
        $this->isGranted('BUSINESS_MANAGE_CLIENT');

        $customers = $this->getDoctrine()
            ->getManager()
            ->getRepository('CanalTPSamCoreBundle:Customer')
            ->findAll();

        return $this->render(
            'CanalTPSamCoreBundle:Customer:list.html.twig',
            array(
                'customers' => $customers
            )
        );
    }

    public function editAction(Request $request, CustomerEntity $customer = null)
    {
        $this->isGranted(array('BUSINESS_MANAGE_CLIENT', 'BUSINESS_CREATE_CLIENT'));

        $coverage = $this->get('sam_navitia')->getCoverages();
        $form = $this->createForm(
            new CustomerType(
                $coverage->regions,
                $this->get('sam_navitia'),
                $this->get('sam_core.customer.application.transformer')
            ),
            $customer
        );

        $form->handleRequest($request);
        if ($form->isValid()) {
            $this->get('sam_core.customer')->save($form->getData());
            $this->addFlashMessage('success', 'customer.flash.edit.success');

            return $this->redirect($this->generateUrl('sam_customer_list'));
        }

        return $this->render(
            'CanalTPSamCoreBundle:Customer:form.html.twig',
            array(
                'title' => 'customer.edit.title',
                'logoPath' => $customer->getWebLogoPath(),
                'form' => $form->createView()
            )
        );
    }

    public function newAction(Request $request)
    {
        $this->isGranted('BUSINESS_CREATE_CLIENT');

        $coverage = $this->get('sam_navitia')->getCoverages();
        $form = $this->createForm(
            new CustomerType(
                $coverage->regions,
                $this->get('sam_navitia'),
                $this->get('sam_core.customer.application.transformer')
            )
        );

        $form->handleRequest($request);
        if ($form->isValid()) {
            $this->get('sam_core.customer')->save($form->getData());
            $this->addFlashMessage('success', 'customer.flash.creation.success');

            return $this->redirect($this->generateUrl('sam_customer_list'));
        }

        return $this->render(
            'CanalTPSamCoreBundle:Customer:form.html.twig',
            array(
                'logoPath' => null,
                'title' => 'customer.new.title',
                'form' => $form->createView()
            )
        );
    }

    // TODO: Duplicate in CanalTPMttBundle:Network (controller)
    public function byCoverageAction($externalCoverageId)
    {
        $response = new JsonResponse();
        $navitia = $this->get('sam_navitia');
        $nmmToken = $this->get('service_container')->getParameter('nmm.navitia.token');
        $status = Response::HTTP_FORBIDDEN;

        $navitia->setToken($nmmToken);
        try {
            $networks = $navitia->getNetworks($externalCoverageId);
            asort($networks);
        } catch (\Navitia\Component\Exception\NavitiaException $e) {
            $response->setData(array('status' => $status));
            $response->setStatusCode($status);

            return $response;
        }

        $status = Response::HTTP_OK;
        $response->setData(
            array(
                'status' => $status,
                'networks' => $networks
            )
        );
        $response->setStatusCode($status);

        return $response;
    }

    public function checkAllowedToNetworkAction($externalCoverageId, $externalNetworkId, $token)
    {
        return;

        $response = new JsonResponse();
        $navitia = $this->get('sam_navitia');
        $status = Response::HTTP_FORBIDDEN;

        $navitia->setToken($token);
        try {
            $networks = $navitia->getNetworks($externalCoverageId);
        } catch (\Navitia\Component\Exception\NavitiaException $e) {
            $response->setData(array('status' => $status));
            $response->setStatusCode($status);

            return $response;
        }

        if (isset($networks[$externalNetworkId])) {
            $status = Response::HTTP_OK;
        }

        $response->setData(array('status' => $status));
        $response->setStatusCode($status);

        return $response;
    }
}
