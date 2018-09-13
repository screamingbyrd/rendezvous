<?php
/**
 * Created by PhpStorm.
 * User: Altea IT
 * Date: 12/09/2018
 * Time: 16:16
 */

namespace ProBundle\Controller;

use AppBundle\Entity\Pro;
use AppBundle\Entity\FeaturedPro;
use AppBundle\Entity\Service;
use AppBundle\Form\ProType;
use Ivory\GoogleMap\Base\Coordinate;
use Ivory\GoogleMap\Overlay\Marker;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Ivory\GoogleMap\Map;
use Ivory\GoogleMap\Service\Geocoder\GeocoderService;
use Http\Adapter\Guzzle6\Client as GuzzleAdapter;
use Http\Message\MessageFactory\GuzzleMessageFactory;
use Ivory\GoogleMap\Service\Geocoder\Request\GeocoderAddressRequest;
use Ivory\GoogleMap\Overlay\InfoWindow;
use Symfony\Component\HttpFoundation\JsonResponse;
use Trt\SwiftCssInlinerBundle\Plugin\CssInlinerPlugin;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Response;
use Ivory\GoogleMap\Place\Autocomplete;
use Ivory\GoogleMap\Place\AutocompleteType;
use Ivory\GoogleMap\Helper\Builder\PlaceAutocompleteHelperBuilder;
use Ivory\GoogleMap\Helper\Builder\ApiHelperBuilder;
use Ivory\GoogleMap\Event\Event;

class ServiceController extends Controller
{

    public function manageServiceAction(Request $request)
    {
        $user = $this->getUser();

        $session = $request->getSession();

        if(!(isset($user) and  in_array('ROLE_EMPLOYER', $user->getRoles()))){
            $translated = $this->get('translator')->trans('redirect.pro');
            $session->getFlashBag()->add('danger', $translated);
            return $this->redirectToRoute('create_pro');
        }

        $pro = $user->getPro();

        $services = array();

        $serviceRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Service')
        ;

        $services = $serviceRepository->findBy(array('pro' => $user->getPro()));

        if ($request->isMethod('POST')) {

            $servicesArray = $request->get('service');
            $categoryNames = $request->get('category-name');
            $em = $this->getDoctrine()->getManager();
            foreach ($servicesArray as $key => $value){
                foreach ($value as $data){
                    $service = new Service();

                    $service->setName($data['name']);
                    $service->setCategory($categoryNames[$key]);
                    $service->setPrice($data['price']);
                    $service->setLength($data['length']);
                    $service->setPro($pro);

                    $em->persist($service);
                }
            }
            $em->flush();

            return $this->redirectToRoute('manage_service');
        }

        $categoryArray = array();
        foreach ($services as $service){
            $categoryArray[$service->getCategory()][] = $service;
        }

        return $this->render('ProBundle::manageServices.html.twig', array(
            'services' => $categoryArray,
        ));
    }

}