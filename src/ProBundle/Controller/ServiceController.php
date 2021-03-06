<?php
/**
 * Created by PhpStorm.
 * User: Altea IT
 * Date: 12/09/2018
 * Time: 16:16
 */

namespace ProBundle\Controller;

use AppBundle\Entity\Client;
use AppBundle\Entity\Pro;
use AppBundle\Entity\FeaturedPro;
use AppBundle\Entity\Rendezvous;
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
use AppBundle\Form\ClientType;

class ServiceController extends Controller
{

    public function manageServiceAction(Request $request)
    {
        $user = $this->getUser();

        $session = $request->getSession();

        if(!(isset($user) and  in_array('ROLE_PRO', $user->getRoles()))){
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
            if(isset($categoryToRemove)){
                foreach ($categoryToRemove as $category){
                    if($category != ''){
                        $services = $serviceRepository->findBy(array('category' => $category, 'pro' => $pro));
                        foreach ($services as $service){
                            $em->remove($service);
                        }

                    }
                }
            }

            if(isset($serviceToRemove)) {
                foreach ($serviceToRemove as $id) {
                    if ($id != '') {
                        $service = $serviceRepository->findOneBy(array('id' => $id));
                        $em->remove($service);
                    }
                }
            }
            $em->flush();

            $translated = $this->get('translator')->trans('service.manageService.success');
            $session->getFlashBag()->add('info', $translated);

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
        $userId = $request->get('userId');
        $userId = isset($userId)?$userId:0;
        $session = $request->getSession();

        if(!(isset($user) and  in_array('ROLE_PRO', $user->getRoles()))){
            $translated = $this->get('translator')->trans('redirect.pro');
            $session->getFlashBag()->add('danger', $translated);
            return $this->redirectToRoute('create_pro');
        }


        $scheduleArray = $finalColorArray = array();

        $colorArray = ['#3A87AD','red','orange','green','black'];

        $userRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:User')
        ;
        $scheduleRepository = $this
        ->getDoctrine()
        ->getManager()
        ->getRepository('AppBundle:Schedule')
    ;

        if ($userId == 0){
            $givenUser = $userRepository->findBy(array('pro' => $user->getPro()));
            $username = null;
        }else{
            $givenUser = $userRepository->findBy(array('id' => $userId));
            $username = $givenUser[0]->getUsername();
        }

        $schedules = $scheduleRepository->findBy(array('user' => $givenUser));
        for ($i = 0; $i < count($givenUser); $i++){
            $finalColorArray[$givenUser[$i]->getUsername()] = $colorArray[$i];
        }

        $users = $userRepository->findBy(array('pro' => $user->getPro(), 'enabled' => 1));

        foreach ($schedules as $schedule){
            $scheduleArray[]  = array('color' => $finalColorArray[$schedule->getUser()->getUsername()], 'title' => $schedule->getUser()->getUsername(), 'id' => $schedule->getId(), 'start' => $schedule->getStartDate()->format('Y-m-d H:i:s'), 'end' => $schedule->getEndDate()->format('Y-m-d H:i:s'));
        }

        return $this->render('ProBundle::manageSchedules.html.twig', array(
            'schedules' => $scheduleArray,
            'userId' => $userId,
            'collaborators' => $users,
            'username' => $username
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

        if(!(isset($user) and  in_array('ROLE_PRO', $user->getRoles()))){
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

        if(!(isset($user) and  in_array('ROLE_PRO', $user->getRoles()))){
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

    public function replicateScheduleAction(Request $request){
        $startDate = $request->get('startDate');
        $endDate = $request->get('endDate');
        $userId = $request->get('userId');

        $user = $this->getUser();

        $session = $request->getSession();

        if(!(isset($user) and  in_array('ROLE_PRO', $user->getRoles()))){
            $translated = $this->get('translator')->trans('redirect.pro');
            $session->getFlashBag()->add('danger', $translated);
            return $this->redirectToRoute('create_pro');
        }

        $em = $this->getDoctrine()->getManager();

        $userRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:User')
        ;
        $scheduleRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Schedule')
        ;

        $nextWeekStartDate = strtotime($startDate);
        $nextWeekStartDate = strtotime("+7 day", $nextWeekStartDate);

        $nextWeekEndDate = strtotime($endDate);
        $nextWeekEndDate = strtotime("+7 day", $nextWeekEndDate);

        if ($userId == 0){
            $givenUser = $userRepository->findBy(array('pro' => $user->getPro()));
            $i = 0;
            foreach ($givenUser as $singleUser){
                $schedules = $scheduleRepository->findBetween($singleUser, $startDate, $endDate);
                $nextWeekSchedule = $scheduleRepository->findBetween($singleUser, date('Y-m-d 00:00:00', $nextWeekStartDate), date('Y-m-d 00:00:00', $nextWeekEndDate));

                if(empty($nextWeekSchedule)){
                    foreach ($schedules as $schedule){
                        for($i = 1;$i <=1; $i++){
                            $newStartDate = new DateTime($schedule->getStartDate()->format("Y-m-d H:i:s"));
                            $newEndDate = new DateTime($schedule->getEndDate()->format("Y-m-d H:i:s"));
                            $newSchedule = new Schedule();

                            $newSchedule->setUser($singleUser);
                            $newSchedule->setStartDate($newStartDate->modify('+'.$i.'week'));
                            $newSchedule->setEndDate($newEndDate->modify('+'.$i.'week'));

                            $em->persist($newSchedule);
                        }
                    }
                }else{
                    $i++;
                }
            }
            if($i == count($givenUser)){
                $translated = $this->get('translator')->trans('service.manageSchedule.duplicate.error');
                $session->getFlashBag()->add('danger', $translated);

                return new JsonResponse($this->generateUrl('manage_schedule', array('userId' => $userId)));
            }
        }else{
            $givenUser = $userRepository->findOneBy(array('id' => $userId));

            $nextWeekSchedule = $scheduleRepository->findBetween($givenUser, date('Y-m-d 00:00:00', $nextWeekStartDate), date('Y-m-d 00:00:00', $nextWeekEndDate));

            if(!empty($nextWeekSchedule)){
                $translated = $this->get('translator')->trans('service.manageSchedule.duplicate.error');
                $session->getFlashBag()->add('danger', $translated);

                return new JsonResponse($this->generateUrl('manage_schedule', array('userId' => $userId)));
            }

            $schedules = $scheduleRepository->findBetween($givenUser, $startDate, $endDate);
            foreach ($schedules as $schedule){
                for($i = 1;$i <=1; $i++){
                    $newStartDate = new DateTime($schedule->getStartDate()->format("Y-m-d H:i:s"));
                    $newEndDate = new DateTime($schedule->getEndDate()->format("Y-m-d H:i:s"));
                    $newSchedule = new Schedule();

                    $newSchedule->setUser($givenUser);
                    $newSchedule->setStartDate($newStartDate->modify('+'.$i.'week'));
                    $newSchedule->setEndDate($newEndDate->modify('+'.$i.'week'));

                    $em->persist($newSchedule);
                }
            }
        }


        $em->flush();

        $translated = $this->get('translator')->trans('service.manageSchedule.duplicate.success');
        $session->getFlashBag()->add('info', $translated);

        return new JsonResponse($this->generateUrl('manage_schedule', array('userId' => $userId)));

    }

    public function reservationPageAction(Request $request)
    {
        $user = $this->getUser();
        $proId = $request->get('proId');
        $serviceId = $request->get('serviceId');
        $collaboratorId = $request->get('collaboratorId');
        isset($collaboratorId)?$collaboratorId:null;
        $session = $request->getSession();

        $scheduleArray = $rendezvousArray = $finalColorArray = $globalScheduleArray = $globalRendezvousArray = array();

        $scheduleRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Schedule')
        ;
        $rendezvousRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Rendezvous')
        ;
        $serviceRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Service')
        ;
        $service = $serviceRepository->findOneBy(array('id' => $serviceId));

        $proRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Pro')
        ;
        $pro = $proRepository->findOneBy(array('id' => $proId));

        $repository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:User');

        $users = $repository->findBy(array('pro' => $pro, 'enabled' => 1));

        foreach ($users as $collaborator){
            $reorderedSchedule = array();
            $reorderedRendezvous = array();
            $schedulesOfUser = $scheduleRepository->findNext(array('user' => $collaborator));
            $rendezvousOfUser = $rendezvousRepository->findNext(array('user' => $collaborator));

            foreach ($schedulesOfUser as $schedule){
                $reorderedSchedule[$schedule->getStartDate()->format('Y-m-d')][] = $schedule;
                $globalScheduleArray[$schedule->getStartDate()->format('Y-m-d')][] = $schedule;
            }
            foreach ($rendezvousOfUser as $rendezvous){
                $reorderedRendezvous[$rendezvous->getStartDate()->format('Y-m-d')][] = $rendezvous;
                $globalRendezvousArray[$rendezvous->getStartDate()->format('Y-m-d')][] = $rendezvous;
            }
            $scheduleArray[$collaborator->getId()] = $reorderedSchedule;
            $rendezvousArray[$collaborator->getId()] = $reorderedRendezvous;
        }

        $finalGlobalScheduleArray = array();
        foreach ($globalScheduleArray as $date => $dailyArray){
            $dateArray = array();
            foreach ($dailyArray as $schedule){
                $dateArray[] = array('startDate' => $schedule->getStartDate(), 'endDate' => $schedule->getEndDate());
            }
            usort($dateArray, function($a, $b)
            {
                return $a['startDate'] > $b['startDate'];
            });

            $n = 0; $len = count($dateArray);
            for ($i = 1; $i < $len; ++$i)
            {
                if ($dateArray[$i]['startDate'] > $dateArray[$n]['endDate'])
                    $n = $i;
                else
                {
                    if ($dateArray[$n]['endDate'] < $dateArray[$i]['endDate'])
                        $dateArray[$n]['endDate'] = $dateArray[$i]['endDate'];
                    unset($dateArray[$i]);
                }
            }
            $finalGlobalScheduleArray[$date] = $dateArray;
        }
        end($finalGlobalScheduleArray);         // move the internal pointer to the end of the array
        $key = key($finalGlobalScheduleArray);

        $now = time(); // or your date as well
        $your_date = strtotime($key);
        $datediff = $your_date - $now;

        $numberOfDays = round($datediff / (60 * 60 * 24));

        return $this->render('ProBundle::reservationPage.html.twig', array(
            'schedules' => $scheduleArray,
            'rendezvous' => $rendezvousArray,
            'collaborators' => $users,
            'service' => $service,
            'pro' => $pro,
            'collaboratorId' => $collaboratorId,
            'globalSchedules' => $finalGlobalScheduleArray,
            'numberOfDays' => $numberOfDays,
        ));
    }

    public function reserveAction(Request $request){
        $session = $request->getSession();

        $user = $this->getUser();

        $serviceId = $request->get('serviceId');
        $collaboratorId = $request->get('collaboratorId');
        $collaboratorList = $request->get('list');
        $date = $request->get('date');
        $hour = $request->get('hour');
        $email = $request->get('email');
        $password = $request->get('password');
        $firstName = $request->get('firstName');
        $lastName = $request->get('lastName');
        $phone = $request->get('phone');
        $connectionType = $request->get('connectionType');

        $userRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:User')
        ;
        if(isset($collaboratorId) && $collaboratorId != ''){
            $collaborator = $userRepository->findOneBy(array('id' => $collaboratorId));
        }else{
            $collaboratorList = rtrim($collaboratorList,"-");
            $collaboratorList = explode('-', $collaboratorList);
            shuffle($collaboratorList);
            $collaborator = $userRepository->findOneBy(array('id' => $collaboratorList[0]));
        }

        $mainCollaborator = $userRepository->findOneBy(array('pro' => $collaborator->getPro(), 'main' => 1));

        if(!isset($user) || in_array('ROLE_PRO', $user->getRoles())){
            if($connectionType == 'register'){
                $userRegister = $this->get('app.user_register');
                $user = $userRegister->register($email,$email,$password,$firstName,$lastName,'ROLE_CLIENT');

                $client = new Client();
                $client->setUser($user);
                $client->setPhone($phone);
                $em = $this->getDoctrine()->getManager();
                $em->persist($client);
                $em->flush();
            }else{
                $user_manager = $this->get('fos_user.user_manager');
                $factory = $this->get('security.encoder_factory');
                $user = $user_manager->findUserByUsername($email);

                $translated = $this->get('translator')->trans('service.reserve.login.error');

                if(!isset($user)){
                    $session->getFlashBag()->add('danger', $translated);
                    return $this->redirectToRoute('show_pro', array('id' => $collaborator->getPro()->getId()));
                }

                $encoder = $factory->getEncoder($user);
                $bool = ($encoder->isPasswordValid($user->getPassword(),$password,$user->getSalt())) ? "true" : "false";

                if($bool == "false"){
                    $session->getFlashBag()->add('danger', $translated);
                    return $this->redirectToRoute('show_pro', array('id' => $collaborator->getPro()->getId()));
                }

                $token = new \Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken($user, $password, "main", array('ROLE_CLIENT'));
                $this->get('security.token_storage')->setToken($token);

                $session->set('_security_secured_area', serialize($token));
            }
        }

        $serviceRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Service')
        ;
        $service = $serviceRepository->findOneBy(array('id' => $serviceId));

        $clientRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Client')
        ;
        $client = $clientRepository->findOneBy(array('user' => $user));

        $rendezvous = new Rendezvous();

        $em = $this->getDoctrine()->getManager();

        $datetime = new DateTime($date.' '.$hour);
        $endDatetime = new DateTime($date.' '.$hour);

        $rendezvous->setClient($client);
        $rendezvous->setUser($collaborator);
        $rendezvous->setService($service);
        $rendezvous->setStartDate($datetime);
        $rendezvous->setEndDate($endDatetime->modify('+ '.$service->getLength().'minute'));

        $em->persist($rendezvous);

        $em->flush();

        $mailer = $this->container->get('swiftmailer.mailer');

        $translatedPro = $this->get('translator')->trans('service.reserve.mail.pro');

        $messagePro = (new \Swift_Message($translatedPro))
            ->setFrom('jobnowlu@noreply.lu')
            ->setTo($mainCollaborator->getEmail())
            ->setCc($collaborator->getEmail())
            ->setBody(
                $this->renderView(
                    'AppBundle:Emails:reserve.html.twig',
                    array('rendezvous' => $rendezvous)
                ),
                'text/html'
            );
        ;

        $translatedClient = $this->get('translator')->trans('service.reserve.mail.client');
        $messageClient = (new \Swift_Message($translatedClient . ' ' . $collaborator->getPro()->getName()))
            ->setFrom('jobnowlu@noreply.lu')
            ->setTo($client->getEmail())
            ->setBody(
                $this->renderView(
                    'AppBundle:Emails:reserved.html.twig',
                    array('rendezvous' => $rendezvous)
                ),
                'text/html'
            );
        ;
        $messagePro->getHeaders()->addTextHeader(
            CssInlinerPlugin::CSS_HEADER_KEY_AUTODETECT
        );
        $messageClient->getHeaders()->addTextHeader(
            CssInlinerPlugin::CSS_HEADER_KEY_AUTODETECT
        );

        $mailer->send($messageClient);
        $mailer->send($messagePro);


        $translated = $this->get('translator')->trans('service.reserve.success');
        $session->getFlashBag()->add('info', $translated);

        return $this->redirectToRoute('dashboard_client');
    }

    public function rendezvousPageAction(Request $request)
    {
        $user = $this->getUser();
        $userId = $request->get('userId');
        $userId = isset($userId)?$userId:0;
        $session = $request->getSession();

        if(!(isset($user) and  in_array('ROLE_PRO', $user->getRoles()))){
            $translated = $this->get('translator')->trans('redirect.pro');
            $session->getFlashBag()->add('danger', $translated);
            return $this->redirectToRoute('create_pro');
        }


        $finalArray = $finalColorArray = array();

        $colorArray = ['#3A87AD','red','orange','green','black'];

        $userRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:User')
        ;
        $rendezvouvRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Rendezvous')
        ;

        if ($userId == 0){
            $givenUser = $userRepository->findBy(array('pro' => $user->getPro()));
            $username = null;
        }else{
            $givenUser = $userRepository->findBy(array('id' => $userId));
            $username = $givenUser[0]->getUsername();
        }

        $rendezvousArray = $rendezvouvRepository->findBy(array('user' => $givenUser));
        for ($i = 0; $i < count($givenUser); $i++){
            $finalColorArray[$givenUser[$i]->getUsername()] = $colorArray[$i];
        }

        $users = $userRepository->findBy(array('pro' => $user->getPro(), 'enabled' => 1));

        foreach ($rendezvousArray as $rendezvous){
            $finalArray[]  = array(
                'color' => $finalColorArray[$rendezvous->getUser()->getUsername()],
                'title' => $rendezvous->getUser()->getUsername().' - '.$rendezvous->getClient()->getUser()->getLastname().' - '.$rendezvous->getService()->getCategory().' - '.$rendezvous->getService()->getName(),
                'id' => $rendezvous->getId(),
                'start' => $rendezvous->getStartDate()->format('Y-m-d H:i:s'),
                'end' => $rendezvous->getEndDate()->format('Y-m-d H:i:s')
            );
        }

        return $this->render('ProBundle::rendezvousPage.html.twig', array(
            'rendezvousArray' => $finalArray,
            'userId' => $userId,
            'collaborators' => $users,
            'username' => $username
        ));
    }

    public function cancelRendezvousAction(Request $request)
    {
        $user = $this->getUser();

        $idRendezvous = $request->get('id');
        $session = $request->getSession();

        $clientRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Client')
        ;
        $client = $clientRepository->findOneBy(array('user' => $user->getId()));

        $rendezvousRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Rendezvous')
        ;
        $userRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:User')
        ;
        $rendezvous = $rendezvousRepository->findOneBy(array('id' => $idRendezvous));

        $mainCollaborator = $userRepository->findOneBy(array('pro' => $rendezvous->getUser()->getPro(), 'main' => 1));

        if(!((isset($user) and in_array('ROLE_CLIENT', $user->getRoles()) and $client == $rendezvous->getClient()) ||  in_array('ROLE_ADMIN', $user->getRoles()))){
            $translated = $this->get('translator')->trans('redirect.client');
            $session->getFlashBag()->add('danger', $translated);
            return $this->redirectToRoute('create_client');
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($rendezvous);
        $em->flush();

        $mailer = $this->container->get('swiftmailer.mailer');

        $translatedPro = $this->get('translator')->trans('email.cancel.pro');

        $messagePro = (new \Swift_Message($translatedPro))
            ->setFrom('jobnowlu@noreply.lu')
            ->setTo($mainCollaborator->getEmail())
            ->setCc($rendezvous->getUser()->getEmail())
            ->setBody(
                $this->renderView(
                    'AppBundle:Emails:cancel.html.twig',
                    array('rendezvous' => $rendezvous)
                ),
                'text/html'
            );
        ;

        $translatedClient = $this->get('translator')->trans('email.cancel.client');
        $messageClient = (new \Swift_Message($translatedClient . ' : ' . $rendezvous->getUser()->getPro()->getName()))
            ->setFrom('jobnowlu@noreply.lu')
            ->setTo('arthur.regnault@altea.lu')
            ->setBody(
                $this->renderView(
                    'AppBundle:Emails:canceled.html.twig',
                    array('rendezvous' => $rendezvous)
                ),
                'text/html'
            );
        ;
        $messagePro->getHeaders()->addTextHeader(
            CssInlinerPlugin::CSS_HEADER_KEY_AUTODETECT
        );
        $messageClient->getHeaders()->addTextHeader(
            CssInlinerPlugin::CSS_HEADER_KEY_AUTODETECT
        );

        $mailer->send($messageClient);
        $mailer->send($messagePro);


        $translated = $this->get('translator')->trans('service.cancel.success');
        $session->getFlashBag()->add('info', $translated);
        return $this->redirectToRoute('dashboard_client');
    }

    public function manageGeneralScheduleAction(Request $request)
    {
        $user = $this->getUser();

        $session = $request->getSession();

        if(!(isset($user) and  in_array('ROLE_PRO', $user->getRoles()))){
            $translated = $this->get('translator')->trans('redirect.pro');
            $session->getFlashBag()->add('danger', $translated);
            return $this->redirectToRoute('create_pro');
        }

        $pro = $user->getPro();

        if ($request->isMethod('POST')) {
            $arraySchedule = $request->get('schedule');
            $em = $this->getDoctrine()->getManager();

            $pro->setGeneralSchedule($arraySchedule);
            $em->merge($pro);

            $em->flush();

            $translated = $this->get('translator')->trans('service.manageGeneralSchedule.success');
            $session->getFlashBag()->add('info', $translated);

            return $this->redirectToRoute('manage_general_schedule');
        }

        return $this->render('ProBundle::manageGeneralSchedule.html.twig', array(
            'generalSchedule' => $pro->getGeneralSchedule(),
        ));
    }

    //@TODO put in CRON
    public function sendReminderAction(Request $request){

        $session = $request->getSession();

        $now =  new \DateTime();

        $tomorrow = $now->modify('+1 day');

        $rendezvousRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Rendezvous')
        ;
        $rendezvousArray = $rendezvousRepository->findAllNext();

        foreach ($rendezvousArray as $rendezvous){
            if($rendezvous->getStartDate()->format('Y-m-d') == $tomorrow->format('Y-m-d')){
                $mail = $rendezvous->getClient()->getUser()->getEmail();
                $mailer = $this->container->get('swiftmailer.mailer');

                $translated = $this->get('translator')->trans('email.reservation.reminder');
                $message = (new \Swift_Message($translated))
                    ->setFrom('jobnowlu@noreply.lu')
                    ->setTo('arthur.regnault@altea.lu')
                    ->setBody(
                        $this->renderView(
                            'AppBundle:Emails:reserved.html.twig',
                            array('rendezvous' => $rendezvous,
                                'reminder' => 1)
                        ),
                        'text/html'
                    )
                ;

                $message->getHeaders()->addTextHeader(
                    CssInlinerPlugin::CSS_HEADER_KEY_AUTODETECT
                );
                $mailer->send($message);
            }

        }

        return new Response();
    }

}