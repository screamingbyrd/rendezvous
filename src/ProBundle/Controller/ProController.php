<?php

namespace ProBundle\Controller;

use AppBundle\Entity\Pro;
use AppBundle\Entity\FeaturedPro;
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


class ProController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('', array('name' => $name));
    }

    public function createAction(Request $request)
    {

        $session = $request->getSession();

        $pro = new Pro();

        $form = $this->get('form.factory')->create(ProType::class);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {

            $data = $form->getData();


            $userRegister = $this->get('app.user_register');
            $user = $userRegister->register($data->getEmail(),$data->getEmail(),$data->getPassword(),$data->getFirstName(),$data->getLastName(), 'ROLE_PRO');

            if($user != false){
                // the user is now registered !

                $em = $this->getDoctrine()->getManager();

                $pro->setName($data->getName());
                $pro->setPhone($data->getPhone());
                $pro->addUser($user);
                $user->setPro($pro);

                $em->persist($user);
                $em->persist($pro);
                $em->flush();

                $translated = $this->get('translator')->trans('form.registration.successPro');
                $session->getFlashBag()->add('info', $translated);

                return $this->redirectToRoute('edit_pro');

            }else{

                $translated = $this->get('translator')->trans('form.registration.error');
                $session->getFlashBag()->add('danger', $translated);

                return $this->redirectToRoute('rendezvous_home');
            }
        }
        return $this->render('ProBundle:Form:createPro.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function editAction(Request $request ){
        $user = $this->getUser();

        $session = $request->getSession();
        $repository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Pro')
        ;

        $idPro = $request->get('id');
        $userRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:User')
        ;

        $pro = $repository->findOneBy(array('id' => isset($idPro)?$idPro:$user->getPro()));

        if(!((isset($user) and $user->getPro() == $pro) ||  (isset($user) and in_array('ROLE_ADMIN', $user->getRoles())))){
            $translated = $this->get('translator')->trans('redirect.pro');
            $session->getFlashBag()->add('danger', $translated);
            return $this->redirectToRoute('create_pro');
        }

        if(in_array('ROLE_ADMIN', $user->getRoles())){
            $user = $userRepository->findOneBy(array('pro' => $pro));
        }

        $session = $request->getSession();

        $pro->setFirstName($user->getFirstName());
        $pro->setLastName($user->getLastName());
        $pro->setEmail($user->getEmail());

        $form = $this->get('form.factory')->create(ProType::class, $pro);

        $form->remove('password');
        $form->remove('terms');

        // Si la requête est en POST
        if ($request->isMethod('POST')) {

            $form->handleRequest($request);
            if ($form->isValid()) {
                if ($pro->getImages()) {
                    foreach ($pro->getImages() as $image) $image->setOffer($pro);
                }
                $data = $form->getData();

                $userManager = $this->get('fos_user.user_manager');

                $user->setUsername($data->getEmail());
                $user->setUsernameCanonical($data->getEmail());
                $user->setEmail($data->getEmail());
                $user->setEmailCanonical($data->getEmail());
                $user->setFirstName($data->getFirstName());
                $user->SetLastName($data->getLastName());
                $userManager->updateUser($user);

                $pro->setName($data->getName());
                $pro->setDescription($data->getDescription());
                $pro->setLocation($data->getLocation());
                $pro->setPhone($data->getPhone());
                $pro->addUser($user);

                $em = $this->getDoctrine()->getManager();
                $em->merge($pro);
                $em->flush();

                $translated = $this->get('translator')->trans('form.registration.edited');
                $session->getFlashBag()->add('info', $translated);


                return $this->redirectToRoute('dashboard_pro');

            }
        }

        return $this->render('ProBundle:Form:editPro.html.twig', array(
            'form' => $form->createView(),
            'user' => $user,
            'images' => $form->getData()->getImages()
        ));
    }

    public function deleteAction(Request $request, $id)
    {
        $user = $this->getUser();
        $session = $request->getSession();

        $repository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Pro');

        $userRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:User');

        $offerRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Offer');

        $featuredProRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:FeaturedPro');

        $pro = $repository->findOneBy(array('id' => isset($id)?$id:$user->getPro()));

        if(!((isset($user) and $user->getPro() == $pro) ||  (isset($user) and in_array('ROLE_ADMIN', $user->getRoles())))){
            $translated = $this->get('translator')->trans('redirect.pro');
            $session->getFlashBag()->add('danger', $translated);
            return $this->redirectToRoute('create_pro');
        }

        $userArray = $userRepository->findBy(array('pro' => $pro));

        $featuredProArray = $featuredProRepository->findBy(array('pro' => $pro));

        $offers = $offerRepository->findBy(array('pro' => $pro, 'archived' => false));

        $em = $this->getDoctrine()->getManager();

        $pro->setPhone(null);
        $em->merge($pro);

        foreach ($offers as $offer) {
            $offer->setArchived(true);
            $em->merge($offer);
        }

        foreach ($featuredProArray as $featuredPro) {
            $featuredPro->setArchived(true);
            $em->merge($featuredPro);
        }
        $mailer = $this->container->get('swiftmailer.mailer');
        foreach ($userArray as $user){
            $mail = $user->getEmail();
            $em->remove($user);

            $translated = $this->get('translator')->trans('candidate.delete.deleted');
            $message = (new \Swift_Message($translated))
                ->setFrom('rendezvouslu@noreply.lu')
                ->setTo($mail)
                ->setBody(
                    $this->renderView(
                        'AppBundle:Emails:userDeleted.html.twig',
                        array()
                    ),
                    'text/html'
                )
            ;


            $message->getHeaders()->addTextHeader(
                CssInlinerPlugin::CSS_HEADER_KEY_AUTODETECT
            );
            $mailer->send($message);
        }

        $message = (new \Swift_Message($pro->getName().' has archived his account'))
            ->setFrom('rendezvouslu@noreply.lu')
            ->setTo('contact@rendezvous.lu')
            ->setBody(
                $this->renderView(
                    'AppBundle:Emails:userDeleted.html.twig',
                    array()
                ),
                'text/html'
            )
        ;

        $message->getHeaders()->addTextHeader(
            CssInlinerPlugin::CSS_HEADER_KEY_AUTODETECT
        );

        $mailer->send($message);

        $em->flush();
        $translated = $this->get('translator')->trans('candidate.delete.deleted');
        $session->getFlashBag()->add('info', $translated);

        return $this->redirectToRoute('rendezvous_home');

    }

    public function showAction($id)
    {
        $repository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Pro');

        $pro = $repository->find($id);

        $phone = $pro->getPhone();
        if(!isset($phone)){
            return $this->redirectToRoute('rendezvous_home');
        }

        $address = $pro->getLocation() . ', ' . $pro->getCity();

        $location = $this->get('app.find_latlong')->geocode($address);

        $serviceRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Service');

        $services = $serviceRepository->findBy(array('pro' => $pro));

        $serviceArray = array();

        foreach ($services as $service){
            $serviceArray[$service->getCategory()][] = $service;
        }

        return $this->render('ProBundle:Pro:show.html.twig', array(
            'pro' => $pro,
            'serviceArray' => $serviceArray,
            'location' => $location
        ));

    }

    public function listAction(){
        $repository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Pro');

        $pros = $repository->findAll();

        return $this->render('ProBundle:Pro:list.html.twig', array(
            'pros' => $pros
        ));
    }

    public function dashboardAction(Request $request){
        $user = $this->getUser();
        $session = $request->getSession();

        if(!isset($user) || !in_array('ROLE_PRO', $user->getRoles())){
            $translated = $this->get('translator')->trans('redirect.pro');
            $session->getFlashBag()->add('danger', $translated);
            return $this->redirectToRoute('create_pro');
        }

        $repository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Pro')
        ;
        $idPro = $request->get('id');
        $userRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:User')
        ;

        $pro = $repository->findOneBy(array('id' => isset($idPro)?$idPro:$user->getPro()));
        $user = $userRepository->findOneBy(array('pro' => $pro));


        return $this->render('ProBundle::dashboard.html.twig', array(
            'pro' => $pro,
        ));
    }

    public function myOfferPageAction(Request $request, $archived = 0){
        $user = $this->getUser();
        $session = $request->getSession();

        if(!isset($user) || !in_array('ROLE_PRO', $user->getRoles())){
            $translated = $this->get('translator')->trans('redirect.pro');
            $session->getFlashBag()->add('danger', $translated);
            return $this->redirectToRoute('create_pro');
        }

        $repository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Pro')
        ;
        $idPro = $request->get('id');
        $userRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:User')
        ;

        $pro = $repository->findOneBy(array('id' => isset($idPro)?$idPro:$user->getPro()));
        $user = $userRepository->findOneBy(array('pro' => $pro));

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
        $currentSlot = $slotRepository->getCurrentSlotPro($user->getPro()->getId());
        $searchArray = array('pro' => $user->getPro());

        if($archived == 0){
            $searchArray['archived'] = 0;
        }

        $_SESSION['archived'] = $archived;

        $offers = $OfferRepository->findBy($searchArray);
        $generateUrlService = $this->get('app.offer_generate_url');
        foreach ($offers as &$offer){
            $offer->setOfferUrl($generateUrlService->generateOfferUrl($offer));
            $finalArray[$offer->getId()]['offer'] = $offer;
        }

        $countOfferInSlot = $OfferRepository->countOffersInSlot($pro);

        $countActiveOffer = $OfferRepository->countActiveOffer($pro);

        $creditInfo = $this->container->get('app.credit_info');

        return $this->render('ProBundle::myOffers.html.twig', array(
            'offers' => $offers,
            'publishedOffer' => $creditInfo->getPublishOffer(),
            'boostOffers' => $creditInfo->getBoostOffers(),
            'buySlot' => $creditInfo->getBuySlot(),
            'slots' => $currentSlot,
            'pro' => $pro,
            'countOfferInSlot' => $countOfferInSlot,
            'countActiveOffer' => $countActiveOffer
        ));
    }

    public function featuredProPageAction(Request $request){
        $user = $this->getUser();

        $now = new \DateTime();
        $year = $request->get('year');
        $year = isset($year)?$year:$now->format('Y');

        $featuredProRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:FeaturedPro')
        ;

        $featuredPro = $featuredProRepository->findBy(array('archived' => 0));
        $featuredArray = array();

        foreach ($featuredPro as $item) {

            $featuredArray[$item->getStartDate()->format('d/m/Y')]['id'][] = $item->getPro()->getId();
            $featuredArray[$item->getStartDate()->format('d/m/Y')]['featured'][] = $item;
        }

        $creditInfo = $this->container->get('app.credit_info');
        $now = new \DateTime();
        $now->modify( '- 1 week' );

        return $this->render('ProBundle::featuredPro.html.twig', array(
            'featuredProArray' => $featuredArray,
            'user' => $user,
            'featuredProCredit' => $creditInfo->getFeaturedPro(),
            'now' => $now,
            'year' => $year
        ));
    }

    public function reserveFeaturedProAction(Request $request){
        $date = $request->get('date');
        $userId = $request->get('user');
        $session = $request->getSession();

        $user = $this->getUser();
        if(!isset($user) || !in_array('ROLE_PRO', $user->getRoles())|| $user->getId() != (int)$userId){
            return $this->redirectToRoute('create_pro');
        }

        $creditInfo = $this->container->get('app.credit_info');

        $pro = $user->getPro();

        $creditPro = $pro->getCredit();
        $creditFeaturedPro = $creditInfo->getFeaturedPro();

        if($creditPro < $creditFeaturedPro){
            $translated = $this->get('translator')->trans('form.offer.activate.error');
            $session->getFlashBag()->add('danger', $translated);
            return $this->redirectToRoute('rendezvous_credit');
        }

        $pro->setCredit($creditPro - $creditFeaturedPro);

        $featuredPro = new FeaturedPro();
        $featuredPro->setPro($pro);
        $startDate = new \DateTime($date['date']);
        $endDate = new \DateTime($date['date']);

        $featuredPro->setStartDate($startDate);
        $featuredPro->setEndDate($endDate->modify( '+ 1 week' ));
        $featuredPro->setArchived(0);

        $em = $this->getDoctrine()->getManager();
        $em->persist($featuredPro);
        $em->flush();

        return $this->redirectToRoute('featured_pro_page', array('year' => substr($date['date'], 0, strpos($date['date'], '-'))));
    }

    public function deleteFeaturedProAction(Request $request){

        $session = $request->getSession();
        $featuredId = $request->get('id');
        $user = $this->getUser();

        if(!isset($user) || !in_array('ROLE_ADMIN', $user->getRoles())){
            return $this->redirectToRoute('create_candidate');
        }

        $featuredProRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:FeaturedPro')
        ;
        $featuredPro = $featuredProRepository->findOneBy(array('id' => $featuredId));

        $featuredPro->setArchived(true);

        $em = $this->getDoctrine()->getManager();
        $em->merge($featuredPro);
        $em->flush();

        $session->getFlashBag()->add('info', 'featured pro archived');

        return $this->redirectToRoute('featured_pro_page');
    }

    public function addCollaboratorAction(Request $request){

        $user = $this->getUser();

        $session = $request->getSession();

        if(!(isset($user) and  in_array('ROLE_PRO', $user->getRoles()))){
            $translated = $this->get('translator')->trans('redirect.pro');
            $session->getFlashBag()->add('danger', $translated);
            return $this->redirectToRoute('create_pro');
        }

        $form = $this->get('form.factory')
            ->createNamedBuilder('collaborator-form')
            ->add('email',      EmailType::class, array(
                'required' => true,
                'label' => 'form.registration.email'
            ))
            ->add('submit', SubmitType::class)
            ->getForm();
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {

                $data = $form->getData();

                $email = $data['email'];

                $pro = $user->getPro();

                $userRegister = $this->get('app.user_register');
                $collaborator = $userRegister->addCollaborator($email, $pro);

                if(is_bool($collaborator) && !$collaborator){
                    $translated = $this->get('translator')->trans('form.registration.mailAlreadyExist');
                    $session->getFlashBag()->add('danger', $translated);
                    return $this->redirectToRoute('add_collaborator');
                }

                $em = $this->getDoctrine()->getManager();

                $em->persist($collaborator);
                $em->flush();

                $mailer = $this->container->get('swiftmailer.mailer');

                $translated = $this->get('translator')->trans('pro.addCollaborator.youHave');

                $message = (new \Swift_Message($translated . ' ' . $pro->getName()))
                    ->setFrom('rendezvouslu@noreply.lu')
                    ->setTo($email)
                    ->setBody(
                        $this->renderView(
                            'AppBundle:Emails:addedAsCollaborator.html.twig',
                            array(
                                'pro' => $pro,
                            )
                        ),
                        'text/html'
                    )
                ;

                $message->getHeaders()->addTextHeader(
                    CssInlinerPlugin::CSS_HEADER_KEY_AUTODETECT
                );
                $mailer->send($message);

                $translated = $this->get('translator')->trans('pro.addCollaborator.added');
                $session->getFlashBag()->add('info', $translated);

                return $this->redirectToRoute('list_collaborator', array('id' => $pro->getId()));

            }
        }
        return $this->render('ProBundle:Pro:addCollaborator.html.twig', [
            'form' => $form->createView(),
        ]);

    }

    public function listCollaboratorAction(Request $request, $id){

        $session = $request->getSession();

        $currentUser = $this->getUser();

        if(!(isset($currentUser) and  in_array('ROLE_PRO', $currentUser->getRoles()) and $currentUser->isMain())){
            $translated = $this->get('translator')->trans('redirect.pro');
            $session->getFlashBag()->add('danger', $translated);
            return $this->redirectToRoute('create_pro');
        }

        $proRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Pro');

        $pro = $proRepository->findOneBy(array('id' => $id));

        $repository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:User');

        $users = $repository->findBy(array('pro' => $pro, 'enabled' => 1));

        for ($i = 0; $i<= count($users); $i++){
            if($users[$i] == $currentUser){
                unset($users[$i]);
            }
        }

        return $this->render('ProBundle:Pro:collaboratorList.html.twig', array(
            'collaborators' => $users
        ));
    }

    public function archiveCollaboratorAction(Request $request, $id){

        $session = $request->getSession();

        $user = $this->getUser();

        if(!(isset($user) and  in_array('ROLE_PRO', $user->getRoles()) and $user->isMain())){
            $translated = $this->get('translator')->trans('redirect.pro');
            $session->getFlashBag()->add('danger', $translated);
            return $this->redirectToRoute('create_pro');
        }

        $pro = $user->getPro();

        $repository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:User');

        $collaborator = $repository->findOneBy(array('id' => $id));

        $userManager = $this->container->get('fos_user.user_manager');
        $collaborator->setEnabled(false);
        $userManager->updateUser($collaborator);

        $translated = $this->get('translator')->trans('pro.addCollaborator.deleted');
        $session->getFlashBag()->add('info', $translated);

        return $this->redirectToRoute('list_collaborator', array('id' => $pro->getId()));
    }

    public function changeAccessCollaboratorAction(Request $request, $id){

        $session = $request->getSession();

        $user = $this->getUser();

        if(!(isset($user) and  in_array('ROLE_PRO', $user->getRoles()) and $user->isMain())){
            $translated = $this->get('translator')->trans('redirect.pro');
            $session->getFlashBag()->add('danger', $translated);
            return $this->redirectToRoute('create_pro');
        }

        $pro = $user->getPro();

        $repository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:User');

        $collaborator = $repository->findOneBy(array('id' => $id));

        $em = $this->getDoctrine()->getManager();

        $collaborator->setMain(!$collaborator->isMain());

        $em->merge($collaborator);
        $em->flush();

        $translated = $this->get('translator')->trans('pro.addCollaborator.access.changed');
        $session->getFlashBag()->add('info', $translated);

        return $this->redirectToRoute('list_collaborator', array('id' => $pro->getId()));
    }

    public function deleteImageAction(Request $request)
    {
        $imageId = $request->get('id');

        $imageRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Image')
        ;
        $image = $imageRepository->findOneBy(array('id' => $imageId));

        $proRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Pro')
        ;
        $pro = $proRepository->findOneBy(array('id' => $image->getPro()->getId()));

        if(is_object($pro)){
            $pro->removeImage($image);
        }

        $em = $this->getDoctrine()->getManager();

        $em->merge($pro);
        $em->remove($image);
        $em->flush();


        return new Response();
    }


    public function searchAction(Request $request){
        $keywords = $request->get('keyword');
        $location = $request->get('location');
        $currentPage = $request->get('row');
        $numberOfItem =  $request->get('number');

        $searchParam = array(
            'keywords' => $keywords,
            'location' => $location,
        );
        $searchParam = json_encode($searchParam);

        $searchService = $this->get('app.search.pro');

        if(preg_match("/[0-9]/",$keywords)){
            $offerRepository = $this
                ->getDoctrine()
                ->getManager()
                ->getRepository('AppBundle:Offer')
            ;
            $data = $offerRepository->findBy(array('id'=>$keywords));
        }else{
            $data = $searchService->searchPro($searchParam);
        }

        $countResult = count($data);

        $locationArray =array();
        foreach ($data as $pro){
            $address = $pro->getLocation().', '.$pro->getCity();
            $marker = $this->get('app.find_latlong')->geocode($address);
            $marker[] = $pro->getName();
            $marker[] = $this->generateUrl('show_pro', array('id' => $pro->getId()));
            $marker[] = ($pro->getImages()->first()?$pro->getImages()->first():'');
            $marker[] = $pro->getPhone();
            $locationArray[$pro->getId()] = $marker;
        }

        $adRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Ad')
        ;
        $ads = $adRepository->getCurrentAds();
        shuffle($ads);

        $finalArray = array_slice($data, ($currentPage - 1 ) * $numberOfItem, $numberOfItem);

        $totalPage = ceil ($countResult / $numberOfItem);

        return $this->render('ProBundle:Offer:search-data.html.twig',
            array(
                'data' => $finalArray,
                'page' => $currentPage,
                'total' => $totalPage,
                'numberOfItem' =>($numberOfItem > $countResult? $countResult:$numberOfItem),
                'countResult' => $countResult,
                'searchParam' => $searchParam,
                'ads' => $ads,
                'location' => $locationArray
            )
        );
    }

    public function searchPageAction(Request $request){
        $keywords = $request->get('keyword');
        $location = $request->get('location');

        $autoComplete = new Autocomplete();
        $autoComplete->setInputId('place_input');

        $autoComplete->setInputAttributes(array(
            'class' => 'form-control',
            'name' => 'location',
            'placeholder' =>  $this->get('translator')->trans('form.offer.search.location')
        ));

        if(isset($location) && $location != ''){
            $autoComplete->setInputAttributes(array(
                'class' => 'form-control',
                'name' => 'location',
                'placeholder' =>  $this->get('translator')->trans('form.offer.search.location'),
                'value' => $location
            ));
        }

        $autoComplete->setTypes(array(AutocompleteType::CITIES));
        $autoCompleteHelperBuilder = new PlaceAutocompleteHelperBuilder();

        $autoCompleteHelper = $autoCompleteHelperBuilder->build();
        $apiHelperBuilder = ApiHelperBuilder::create();
        $apiHelperBuilder->setKey('AIzaSyBY8KoA6XgncXKSfDq7Ue7R2a1QWFSFxjc');
        $apiHelperBuilder->setLanguage($request->getLocale());

        $apiHelper = $apiHelperBuilder->build();

        return $this->render('ProBundle:Offer:searchPage.html.twig', array(
            'keyword' => $keywords,
            'autoComplete' => $autoCompleteHelper->render($autoComplete),
            'autoCompleteScript' => $apiHelper->render([$autoComplete])
        ));
    }
}
