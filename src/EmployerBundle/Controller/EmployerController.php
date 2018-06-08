<?php

namespace EmployerBundle\Controller;

use AppBundle\Entity\Employer;
use AppBundle\Entity\FeaturedEmployer;
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
                $employer->setLatLong($data->getLatlong());
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
        if(!isset($user) || !in_array('ROLE_EMPLOYER', $user->getRoles())){
            return $this->redirectToRoute('create_employer');
        }

        $repository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Employer')
        ;
        $employer = $repository->findOneBy(array('id' => $user->getEmployer()));

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

                $user->setEmail($data->getEmail());
                $user->setEmailCanonical($data->getEmail());
                $user->setFirstName($data->getFirstName());
                $user->SetLastName($data->getLastName());
                $userManager->updateUser($user);

                $employer->setName($data->getName());
                $employer->setDescription($data->getDescription());
                $employer->setCredit(0);
                $employer->setWhyUs($data->getWhyUs());
                $employer->setLocation($data->getLocation());
                $employer->setLatLong($data->getLatlong());
                $employer->setPhone($data->getPhone());
                $employer->addUser($user);
                $employer->setLogo($data->getLogo());
                $employer->setCoverImage($data->getCoverImage());

                $em = $this->getDoctrine()->getManager();
                $em->merge($employer);
                $em->flush();

                $session->getFlashBag()->add('info', 'Candidat modifié !');

                return $this->redirectToRoute('jobnow_home');
            }
        }

        return $this->render('EmployerBundle:form:editEmployer.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function deleteAction(Request $request, $id)
    {

        $session = $request->getSession();

        $repository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Employer');

        $employer = $repository->find($id);

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

        $map = new Map();


        //workarround to ssl certificat pb curl error 60

        $config = [
            'verify' => false,
        ];

        $adapter = GuzzleAdapter::createWithConfig($config);

        // GeoCoder API
        $geocoder = new GeocoderService($adapter, new GuzzleMessageFactory());

        //try to match string location to get Object with lat long info
        $request = new GeocoderAddressRequest($employer->getLocation());
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
        $map->setMapOption('zoom', 10);








        return $this->render('EmployerBundle:Employer:show.html.twig', array(
            'employer' => $employer,
            'map' => $map,
            'status' => $status


        ));

    }

    public function dashboardAction($archived = 0){
        $user = $this->getUser();

        $OfferRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Offer')
        ;

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

            $featuredArray[$item->getStartDate()->format('d/m/Y')][] = $item->getEmployer()->getId();
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

}
