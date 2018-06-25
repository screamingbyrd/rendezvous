<?php

namespace EmployerBundle\Controller;

use AppBundle\Entity\Employer;
use AppBundle\Entity\FeaturedEmployer;
use AppBundle\Entity\FeaturedOffer;
use AppBundle\Entity\Slot;
use AppBundle\Form\EmployerType;
use AppBundle\Form\OfferType;
use Ivory\GoogleMap\Base\Coordinate;
use Ivory\GoogleMap\Overlay\Marker;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Ivory\GoogleMap\Map;
use Ivory\GoogleMap\Service\Geocoder\GeocoderService;
use Http\Adapter\Guzzle6\Client as GuzzleAdapter;
use Http\Message\MessageFactory\GuzzleMessageFactory;
use Ivory\GoogleMap\Service\Geocoder\Request\GeocoderAddressRequest;



class EmployerController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('', array('name' => $name));
    }

    public function createAction(Request $request)
    {

        $session = $request->getSession();

        $employer = new Employer();

        $form = $this->get('form.factory')->create(EmployerType::class);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {

            $data = $form->getData();


            $userRegister = $this->get('app.user_register');
            $user = $userRegister->register($data->getEmail(),$data->getEmail(),$data->getPassword(),$data->getFirstName(),$data->getLastName(), 'ROLE_EMPLOYER');

            if($user != false){
                // the user is now registered !

                $em = $this->getDoctrine()->getManager();

                $employer->setName($data->getName());
                $employer->setDescription($data->getDescription());
                $employer->setCredit(0);
                $employer->setWhyUs($data->getWhyUs());
                $employer->setLocation($data->getLocation());
                $employer->setPhone($data->getPhone());
                $employer->addUser($user);
                $employer->setLogo($data->getLogo());
                $employer->setCoverImage($data->getCoverImage());

                $em->persist($user);
                $em->flush();

                $translated = $this->get('translator')->trans('form.registration.successEmployer');
                $session->getFlashBag()->add('info', $translated);

                return $this->redirectToRoute('jobnow_home');

            }else{

                $translated = $this->get('translator')->trans('form.registration.error');
                $session->getFlashBag()->add('danger', $translated);

                return $this->redirectToRoute('jobnow_home');
            }
        }
        return $this->render('EmployerBundle:form:createEmployer.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function editAction(Request $request ){
        $user = $this->getUser();

        $repository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Employer')
        ;

        $idEmployer = $request->get('id');
        $userRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:User')
        ;

        $employer = $repository->findOneBy(array('id' => isset($idEmployer)?$idEmployer:$user->getEmployer()));

        if(!((isset($user) and $user->getEmployer() == $employer) ||  in_array('ROLE_ADMIN', $user->getRoles()))){
            return $this->redirectToRoute('create_candidate');
        }

        $user = $userRepository->findOneBy(array('employer' => $employer));
        $session = $request->getSession();

        $employer->setFirstName($user->getFirstName());
        $employer->setLastName($user->getLastName());
        $employer->setEmail($user->getEmail());

        $form = $this->get('form.factory')->create(EmployerType::class, $employer);

        $form->remove('password');

        // Si la requête est en POST
        if ($request->isMethod('POST')) {

            $form->handleRequest($request);
            if ($form->isValid()) {

                $data = $form->getData();

                $userManager = $this->get('fos_user.user_manager');

                $user->setUsername($data->getEmail());
                $user->setUsernameCanonical($data->getEmail());
                $user->setEmail($data->getEmail());
                $user->setEmailCanonical($data->getEmail());
                $user->setFirstName($data->getFirstName());
                $user->SetLastName($data->getLastName());
                $userManager->updateUser($user);

                $employer->setName($data->getName());
                $employer->setDescription($data->getDescription());
                $employer->setWhyUs($data->getWhyUs());
                $employer->setLocation($data->getLocation());
                $employer->setPhone($data->getPhone());
                $employer->addUser($user);
                $employer->setLogo($data->getLogo());
                $employer->setCoverImage($data->getCoverImage());

                $em = $this->getDoctrine()->getManager();
                $em->merge($employer);
                $em->flush();

                $session->getFlashBag()->add('info', 'Employer modifié !');

                return $this->redirectToRoute('jobnow_home');
            }
        }

        return $this->render('EmployerBundle:form:editEmployer.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function deleteAction(Request $request, $id)
    {
        $user = $this->getUser();
        $idEmployer = $request->get('id');
        $session = $request->getSession();

        $repository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Employer');

        $employer = $repository->findOneBy(array('id' => isset($idEmployer)?$idEmployer:$user->getEmployer()));

        if(!((isset($user) and $user->getEmployer() == $employer) ||  in_array('ROLE_ADMIN', $user->getRoles()))){
            return $this->redirectToRoute('create_candidate');
        }

        $em = $this->getDoctrine()->getManager();

        $em->remove($employer);
        $em->flush();

        $session->getFlashBag()->add('info', 'Employer supprimé !');

        return $this->redirectToRoute('jobnow_home');

    }

    public function showAction($id)
    {
        $repository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Employer');

        $employer = $repository->find($id);

        $offerRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Offer');

        $offers = $offerRepository->findBy(
            array('employer' => $employer),
            array('startDate' => 'DESC'),
            2
        );

        $map = new Map();


        //workarround to ssl certificat pb curl error 60

        $config = [
            'verify' => false,
        ];

        $adapter = GuzzleAdapter::createWithConfig($config);

        // GeoCoder API
        $geocoder = new GeocoderService($adapter, new GuzzleMessageFactory());

        //try to match string location to get Object with lat long info
        if($employer->getLocation()){
            $request = new GeocoderAddressRequest($employer->getLocation());
        }else{
            $request = new GeocoderAddressRequest('228 Route d\'Esch, Luxembourg');
        }

        $response = $geocoder->geocode($request);


        $status = $response->getStatus();



        foreach ($response->getResults() as $result) {

            $coord = $result->getGeometry()->getLocation();
            continue;

        }


        if(isset($coord)) {
            $marker = new Marker($coord);
            $map->setCenter($coord);
            $map->getOverlayManager()->addMarker($marker);
        }

        $map->setStylesheetOption('width', 1100);
        $map->setStylesheetOption('min-height', 1100);
        $map->setMapOption('zoom', 10);


        return $this->render('EmployerBundle:Employer:show.html.twig', array(
            'employer' => $employer,
            'map' => $map,
            'status' => $status,
            'offers' => $offers
        ));

    }

    public function listAction(){
        $repository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Employer');

        $employers = $repository->findAll();

        return $this->render('EmployerBundle:Employer:list.html.twig', array(
            'employers' => $employers
        ));
    }

    public function dashboardAction(Request $request, $archived = 0){
        $user = $this->getUser();

        if(!isset($user) || !in_array('ROLE_EMPLOYER', $user->getRoles())){
            return $this->redirectToRoute('create_employer');
        }

        $repository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Employer')
        ;
        $idEmployer = $request->get('id');
        $userRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:User')
        ;

        $employer = $repository->findOneBy(array('id' => isset($idEmployer)?$idEmployer:$user->getEmployer()));
        $user = $userRepository->findOneBy(array('employer' => $employer));

        $OfferRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Offer')
        ;
        $slotRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Slot')
        ;
        $currentSlot = $slotRepository->getCurrentSlotEmployer($user->getEmployer()->getId());
        $searchArray = array('employer' => $user->getEmployer());

        if($archived == 0){
            $searchArray['archived'] = 0;
        }

        $_SESSION['archived'] = $archived;

        $offers = $OfferRepository->findBy($searchArray);

        $creditInfo = $this->container->get('app.credit_info');

        return $this->render('EmployerBundle::dashboard.html.twig', array(
            'offers' => $offers,
            'publishedOffer' => $creditInfo->getPublishOffer(),
            'boostOffers' => $creditInfo->getBoostOffers(),
            'buySlot' => $creditInfo->getBuySlot(),
            'slots' => $currentSlot
        ));
    }

    public function featuredEmployerPageAction(Request $request){
        $user = $this->getUser();

        $featuredEmployerRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:FeaturedEmployer')
        ;

        $featuredEmployer = $featuredEmployerRepository->findBy(array('archived' => 0));
        $featuredArray = array();

        foreach ($featuredEmployer as $item) {

            $featuredArray[$item->getStartDate()->format('d/m/Y')]['id'][] = $item->getEmployer()->getId();
            $featuredArray[$item->getStartDate()->format('d/m/Y')]['featured'][] = $item;
        }

        $creditInfo = $this->container->get('app.credit_info');

        return $this->render('EmployerBundle::featuredEmployer.html.twig', array(
            'featuredEmployerArray' => $featuredArray,
            'user' => $user,
            'featuredEmployerCredit' => $creditInfo->getFeaturedEmployer(),
        ));
    }

    public function reserveFeaturedEmployerAction(Request $request){
        $date = $request->get('date');
        $userId = $request->get('user');
        $session = $request->getSession();

        $user = $this->getUser();
        if(!isset($user) || !in_array('ROLE_EMPLOYER', $user->getRoles())|| $user->getId() != (int)$userId){
            return $this->redirectToRoute('create_employer');
        }

        $creditInfo = $this->container->get('app.credit_info');

        $employer = $user->getEmployer();

        $creditEmployer = $employer->getCredit();
        $creditFeaturedEmployer = $creditInfo->getFeaturedEmployer();

        if($creditEmployer < $creditFeaturedEmployer){
            $translated = $this->get('translator')->trans('form.offer.activate.error');
            $session->getFlashBag()->add('danger', $translated);
            return $this->redirectToRoute('jobnow_home');
        }

        $employer->setCredit($creditEmployer - $creditFeaturedEmployer);

        $featuredEmployer = new FeaturedEmployer();
        $featuredEmployer->setEmployer($employer);
        $startDate = new \DateTime($date['date']);
        $endDate = new \DateTime($date['date']);

        $featuredEmployer->setStartDate($startDate);
        $featuredEmployer->setEndDate($endDate->modify( '+ 1 week' ));
        $featuredEmployer->setArchived(0);

        $em = $this->getDoctrine()->getManager();
        $em->persist($featuredEmployer);
        $em->flush();

        return $this->redirectToRoute('featured_employer_page');
    }

    public function deleteFeaturedEmployerAction(Request $request){

        $session = $request->getSession();
        $featuredId = $request->get('id');
        $user = $this->getUser();

        if(!isset($user) || !in_array('ROLE_ADMIN', $user->getRoles())){
            return $this->redirectToRoute('create_candidate');
        }

        $featuredEmployerRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:FeaturedEmployer')
        ;
        $featuredEmployer = $featuredEmployerRepository->findOneBy(array('id' => $featuredId));

        $em = $this->getDoctrine()->getManager();
        $em->remove($featuredEmployer);
        $em->flush();

        $session->getFlashBag()->add('info', 'featured employer deleted');

        return $this->redirectToRoute('featured_employer_page');
    }

    public function featuredOfferPageAction(Request $request){
        $user = $this->getUser();
        $employer = $user->getEmployer();
        $featuredOfferRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:FeaturedOffer')
        ;
        $featuredOffer = $featuredOfferRepository->findBy(array('archived' => 0));
        $featuredArray = array();

        $offerRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Offer')
        ;

        $offers = $offerRepository->findBy(array('employer' => $user->getEmployer()));

        foreach ($featuredOffer as $item) {
            $featuredArray[$item->getStartDate()->format('d/m/Y')]['ids'][] = $item->getOffer()->getId();
            if($item->getOffer()->getEmployer() == $employer){
                $featuredArray[$item->getStartDate()->format('d/m/Y')]['features'][] = $item;
            }
        }

        $creditInfo = $this->container->get('app.credit_info');

        return $this->render('EmployerBundle::featuredOffer.html.twig', array(
            'featuredOfferArray' => $featuredArray,
            'user' => $user,
            'featuredOfferCredit' => $creditInfo->getFeaturedOffer(),
            'offers' => $offers
        ));
    }

    public function reserveFeaturedOfferAction(Request $request){
        $date = $request->get('date');
        $userId = $request->get('user');
        $offerId = $request->get('offerId');
        $session = $request->getSession();

        $user = $this->getUser();
        if(!isset($user) || !in_array('ROLE_EMPLOYER', $user->getRoles())|| $user->getId() != (int)$userId){
            return $this->redirectToRoute('create_employer');
        }

        $offerRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Offer')
        ;

        $offer = $offerRepository->findById($offerId);

        $creditInfo = $this->container->get('app.credit_info');

        $employer = $user->getEmployer();

        $creditEmployer = $employer->getCredit();
        $creditFeaturedOffer = $creditInfo->getFeaturedOffer();

        if($creditEmployer < $creditFeaturedOffer){
            $translated = $this->get('translator')->trans('form.offer.activate.error');
            $session->getFlashBag()->add('danger', $translated);
            return $this->redirectToRoute('jobnow_home');
        }

        $employer->setCredit($creditEmployer - $creditFeaturedOffer);

        $featuredOffer = new FeaturedOffer();
        $featuredOffer->setOffer($offer[0]);
        $startDate = new \DateTime($date['date']);
        $endDate = new \DateTime($date['date']);

        $featuredOffer->setStartDate($startDate);
        $featuredOffer->setEndDate($endDate->modify( '+ 1 week' ));
        $featuredOffer->setArchived(0);

        $em = $this->getDoctrine()->getManager();
        $em->persist($featuredOffer);
        $em->flush();

        return $this->redirectToRoute('featured_offer_page');
    }

    public function deleteFeaturedOfferAction(Request $request){

        $session = $request->getSession();
        $featuredId = $request->get('id');
        $user = $this->getUser();

        if(!isset($user) || !in_array('ROLE_ADMIN', $user->getRoles())){
            return $this->redirectToRoute('create_candidate');
        }

        $featuredOfferRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:FeaturedOffer')
        ;
        $featuredOffer = $featuredOfferRepository->findOneBy(array('id' => $featuredId));

        $em = $this->getDoctrine()->getManager();
        $em->remove($featuredOffer);
        $em->flush();

        $session->getFlashBag()->add('info', 'featured offer deleted');

        return $this->redirectToRoute('featured_offer_page');
    }

    public function buySlotAction(Request $request){
        $session = $request->getSession();
        $user = $this->getUser();

        if(!isset($user) || !in_array('ROLE_EMPLOYER', $user->getRoles())){
            return $this->redirectToRoute('create_employer');
        }

        $employer = $user->getEmployer();

        $creditInfo = $this->container->get('app.credit_info');

        $creditEmployer = $employer->getCredit();
        $buySlot = $creditInfo->getBuySlot();

        if($creditEmployer < $buySlot){
            $translated = $this->get('translator')->trans('form.offer.activate.error');
            $session->getFlashBag()->add('danger', $translated);
            return $this->redirectToRoute('dashboard_employer', array('archived' => $_SESSION['archived']));
        }

        $employer->setCredit($creditEmployer - $buySlot);

        $em = $this->getDoctrine()->getManager();
        $em->merge($employer);
        $em->flush();

        $slot = new Slot();

        $slot->setEmployer($employer);
        $now =  new \DateTime();
        $next = new \DateTime();
        $slot->setStartDate($now);
        $slot->setEndDate($next->modify( '+ 1 year' ));

        $em->persist($slot);
        $em->flush();


        $translated = $this->get('translator')->trans('slot.buy.success');
        $session->getFlashBag()->add('info', $translated);

        return $this->redirectToRoute('dashboard_employer', array('archived' => $_SESSION['archived']));
    }

    public function addToSlotAction(Request $request){
        $session = $request->getSession();
        $id = $request->get('id');
        $user = $this->getUser();

        if(!isset($user) || !in_array('ROLE_EMPLOYER', $user->getRoles())){
            return $this->redirectToRoute('create_employer');
        }

        $offerRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Offer')
        ;
        $currentOffer = $offerRepository->findOneBy(array('id' => $id));

        $slotRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Slot')
        ;
        $currentSlot = $slotRepository->getCurrentSlotEmployer($user->getEmployer()->getId());

        $em = $this->getDoctrine()->getManager();

        foreach ($currentSlot as $slot){
            $offer = $slot->getOffer();
            if(!isset($offer)){
                $now =  new \DateTime();
                $slot->setOffer($currentOffer);
                $currentOffer->setSlot($slot);
                $currentOffer->setUpdateDate($now);
                $em->merge($slot);
                $em->merge($currentOffer);
                $em->flush();

                $translated = $this->get('translator')->trans('slot.add.success');
                $session->getFlashBag()->add('info', $translated);

                return $this->redirectToRoute('dashboard_employer', array('archived' => $_SESSION['archived']));
            }
        }

        $translated = $this->get('translator')->trans('slot.add.error');
        $session->getFlashBag()->add('danger', $translated);

        return $this->redirectToRoute('dashboard_employer', array('archived' => $_SESSION['archived']));
    }

    public function removeFromSlotAction(Request $request){
        $session = $request->getSession();
        $id = $request->get('id');
        $user = $this->getUser();

        if(!isset($user) || !in_array('ROLE_EMPLOYER', $user->getRoles())){
            return $this->redirectToRoute('create_employer');
        }

        $offerRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Offer')
        ;
        $offer = $offerRepository->findOneBy(array('id' => $id));

        $slotRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Slot')
        ;
        $slot = $slotRepository->findOneBy(array('offer' => $offer));

        $em = $this->getDoctrine()->getManager();

        $slot->setOffer(null);
        $offer->setSlot(null);
        $em->merge($slot);
        $em->merge($offer);
        $em->flush();

        $translated = $this->get('translator')->trans('slot.remove.success');
        $session->getFlashBag()->add('info', $translated);

        return $this->redirectToRoute('dashboard_employer', array('archived' => $_SESSION['archived']));
    }

    public function EmptySlotAction(Request $request){
        $session = $request->getSession();
        $id = $request->get('id');
        $user = $this->getUser();

        if(!isset($user) || !in_array('ROLE_EMPLOYER', $user->getRoles())){
            return $this->redirectToRoute('create_employer');
        }

        $slotRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Slot')
        ;
        $slot = $slotRepository->findOneBy(array('id' => $id));

        $offerRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Offer')
        ;
        $offer = $offerRepository->findOneBy(array('slot' => $slot));

        $em = $this->getDoctrine()->getManager();

        $slot->setOffer(null);
        $offer->setSlot(null);
        $em->merge($slot);
        $em->merge($offer);
        $em->flush();

        $translated = $this->get('translator')->trans('slot.empty.success');
        $session->getFlashBag()->add('info', $translated);

        return $this->redirectToRoute('dashboard_employer', array('archived' => $_SESSION['archived']));
    }

}
