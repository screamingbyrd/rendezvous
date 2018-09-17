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
use AppBundle\Entity\Schedule;
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
use DateTime;

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
            $categoryToRemove = $request->get('removeCategory');
            $serviceToRemove = $request->get('removeService');
            $em = $this->getDoctrine()->getManager();
            foreach ($servicesArray as $key => $value){
                foreach ($value as $data){

                    if($data['id'] == ''){
                        if($data['name'] != '' || $data['price'] != '' || $data['length'] != ''){
                            $service = new Service();

                            $service->setName($data['name']);
                            $service->setCategory($categoryNames[$key]);
                            $service->setPrice($data['price']);
                            $service->setLength($data['length']);
                            $service->setPro($pro);

                            $em->persist($service);
                        }

                    }else{
                        $service = $serviceRepository->findOneBy(array('id' => $data['id']));
                        if($data['name'] != '' || $data['price'] != '' || $data['length'] != '') {
                            $service->setName($data['name']);
                            $service->setCategory($categoryNames[$key]);
                            $service->setPrice($data['price']);
                            $service->setLength($data['length']);

                            $em->merge($service);
                        }else{
                            $em->remove($service);
                        }
                    }
                }
            }
            foreach ($categoryToRemove as $category){
                if($category != ''){
                    $services = $serviceRepository->findBy(array('category' => $category, 'pro' => $pro));
                    foreach ($services as $service){
                        $em->remove($service);
                    }

                }
            }
            foreach ($serviceToRemove as $id){
                if($id != ''){
                    $service = $serviceRepository->findOneBy(array('id' => $id));
                    $em->remove($service);
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

    public function manageScheduleAction(Request $request)
    {
        $user = $this->getUser();

        $session = $request->getSession();

        if(!(isset($user) and  in_array('ROLE_EMPLOYER', $user->getRoles()))){
            $translated = $this->get('translator')->trans('redirect.pro');
            $session->getFlashBag()->add('danger', $translated);
            return $this->redirectToRoute('create_pro');
        }


        $scheduleArray = array();

        $scheduleRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Schedule')
        ;

        $schedules = $scheduleRepository->findBy(array('user' => $user));

        foreach ($schedules as $schedule){
            $scheduleArray[]  = array('title' => 'horraire de travail', 'id' => $schedule->getId(), 'start' => $schedule->getStartDate()->format('Y-m-d H:i:s'), 'end' => $schedule->getEndDate()->format('Y-m-d H:i:s'));
        }

        return $this->render('ProBundle::manageSchedules.html.twig', array(
            'schedules' => $scheduleArray,
        ));
    }

    public function saveScheduleAction(Request $request){
        $userId = $request->get('userId');
        $startDate = $request->get('startDate');
        $endDate = $request->get('endDate');
        $id = $request->get('id');

        $startDate = \DateTime::createFromFormat('Y-m-d H:i:s', $startDate);
        $endDate = \DateTime::createFromFormat('Y-m-d H:i:s', $endDate);

        $user = $this->getUser();

        $session = $request->getSession();

        if(!(isset($user) and  in_array('ROLE_EMPLOYER', $user->getRoles()))){
            $translated = $this->get('translator')->trans('redirect.pro');
            $session->getFlashBag()->add('danger', $translated);
            return $this->redirectToRoute('create_pro');
        }

        $em = $this->getDoctrine()->getManager();

        if(isset($id)){
            $scheduleRepository = $this
                ->getDoctrine()
                ->getManager()
                ->getRepository('AppBundle:Schedule')
            ;

            $schedule = $scheduleRepository->findOneBy(array('id' => $id));
            $schedule->setStartDate($startDate);
            $schedule->setEndDate($endDate);

            $em->merge($schedule);
        }else{
            $userRepository = $this
                ->getDoctrine()
                ->getManager()
                ->getRepository('AppBundle:User')
            ;

            $schedule = new Schedule();

            $user = $userRepository->findOneBy(array('id' => $userId));

            $schedule->setUser($user);
            $schedule->setStartDate($startDate);
            $schedule->setEndDate($endDate);

            $em->persist($schedule);
        }


        $em->flush();

        return new Response($schedule->getId());
    }

    public function removeScheduleAction(Request $request){
        $id = $request->get('id');

        $user = $this->getUser();

        $session = $request->getSession();

        if(!(isset($user) and  in_array('ROLE_EMPLOYER', $user->getRoles()))){
            $translated = $this->get('translator')->trans('redirect.pro');
            $session->getFlashBag()->add('danger', $translated);
            return $this->redirectToRoute('create_pro');
        }

        $em = $this->getDoctrine()->getManager();

        if(isset($id)){
            $scheduleRepository = $this
                ->getDoctrine()
                ->getManager()
                ->getRepository('AppBundle:Schedule')
            ;

            $schedule = $scheduleRepository->findOneBy(array('id' => $id));

            $em->remove($schedule);
        }

        $em->flush();

        return new Response();
    }

}