<?php
/**
 * Created by PhpStorm.
 * User: Altea IT
 * Date: 01/06/2018
 * Time: 12:10
 */

namespace EmployerBundle\Controller;

use AppBundle\Entity\Employer;
use AppBundle\Entity\Offer;
use AppBundle\Entity\PostulatedOffers;
use AppBundle\Form\EmployerType;
use AppBundle\Form\OfferType;
use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Ivory\GoogleMap\Map;
use Ivory\GoogleMap\Service\Geocoder\GeocoderService;
use Http\Adapter\Guzzle6\Client as GuzzleAdapter;
use Http\Message\MessageFactory\GuzzleMessageFactory;
use Ivory\GoogleMap\Service\Geocoder\Request\GeocoderAddressRequest;
use Ivory\GoogleMap\Overlay\InfoWindow;
use Ivory\GoogleMap\Base\Coordinate;
use Ivory\GoogleMap\Overlay\Marker;
use Ivory\GoogleMap\Event\Event;
use Ivory\GoogleMap\Place\Autocomplete;
use Ivory\GoogleMap\Place\AutocompleteType;
use Ivory\GoogleMap\Helper\Builder\PlaceAutocompleteHelperBuilder;
use Ivory\GoogleMap\Helper\Builder\ApiHelperBuilder;


class OfferController extends Controller
{

    public function postAction(Request $request)
    {

        $session = $request->getSession();

        $translator = $this->get('translator');

        $user = $this->getUser();

        if(isset($_SESSION['request'])){
            $request = $_SESSION['request'];
            unset($_SESSION['request']);
        }

        $offer = new Offer();


        $form = $this->get('form.factory')->create(OfferType::class, $offer, array(
            'translator' => $translator,
        ));

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if(!isset($user)){
                $_SESSION['request'] = $request;
                return $this->redirectToRoute('create_employer', array('postOffer' => true));
            }

            $em = $this->getDoctrine()->getManager();

            $offer->setEmployer($user->getEmployer());

            $offer->setCountView(0);
            $offer->setCountContact(0);
            $past = new \DateTime('01-01-1900');
            $offer->setStartDate($past);
            $offer->setEndDate($past);
            $offer->setUpdateDate($past);

            $em->persist($offer);
            $em->flush();

            $translated = $this->get('translator')->trans('form.offer.creation.success');
            $session->getFlashBag()->add('info', $translated);

            return $this->redirectToRoute('dashboard_employer');

        }
        return $this->render('EmployerBundle:form:postOffer.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function editAction(Request $request)
    {

        $translator = $this->get('translator');

        $session = $request->getSession();

        $id = $request->get('id');

        $user = $this->getUser();

        $employer = $user->getEmployer();

        $offerRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Offer')
        ;
        $offer = $offerRepository->findOneBy(array('id' => $id));

        if(!((isset($user) and in_array('ROLE_EMPLOYER', $user->getRoles()) and $offer->getEmployer()->getId() == $employer->getId()) || in_array('ROLE_ADMIN', $user->getRoles()))){
            $translated = $this->get('translator')->trans('redirect.employer');
            $session->getFlashBag()->add('danger', $translated);
            return $this->redirectToRoute('create_employer');
        }

        $form = $this->get('form.factory')->create(OfferType::class, $offer, array(
            'translator' => $translator,
        ));

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {

            $em = $this->getDoctrine()->getManager();

            $offer->setCountView($offer->getCountView());
            $offer->setCountContact($offer->getCountContact());


            $em->merge($offer);
            $em->flush();

            $translated = $this->get('translator')->trans('form.offer.edition.success');
            $session->getFlashBag()->add('info', $translated);

            return $this->redirectToRoute('dashboard_employer', array('archived' => $_SESSION['archived']));

        }
        return $this->render('EmployerBundle:form:editOffer.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function deleteAction(Request $request){

        $session = $request->getSession();

        $id = $request->get('id');

        $user = $this->getUser();

        $employerRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Employer')
        ;
        $employer = $employerRepository->findOneBy(array('id' => $user->getEmployer()));

        $offerRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Offer')
        ;
        $offer = $offerRepository->findOneBy(array('id' => $id));

        if(!((isset($user) and in_array('ROLE_EMPLOYER', $user->getRoles()) and $offer->getEmployer()->getId() == $employer->getId()) || in_array('ROLE_ADMIN', $user->getRoles()))){
            $translated = $this->get('translator')->trans('form.offer.edition.error');
            $session->getFlashBag()->add('danger', $translated);
            return $this->redirectToRoute('dashboard_employer', array('archived' => $_SESSION['archived']));
        }

        $bool = boolval($offer->isArchived());
        $offer->setArchived(!$bool);

        $em = $this->getDoctrine()->getManager();
        $em->merge($offer);
        $em->flush();

        $translated = $this->get('translator')->trans(!$bool?'form.offer.archived.success':'form.offer.unarchived.success');
        $session->getFlashBag()->add('info', $translated);

        return $this->redirectToRoute('dashboard_employer', array('archived' => $_SESSION['archived']));
    }

    public function showAction($id){
        $offerRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Offer')
        ;
        $offer = $offerRepository->findOneBy(array('id' => $id));

        if($offer->getArchived() == 1){
            return $this->redirectToRoute('offer_archived', array('id' => $id));
        }

        $similarOfferArray = $this->getSimilarOffers($offer);

        $offer->setCountView($offer->getCountView() +1);

        $em = $this->getDoctrine()->getManager();
        $em->merge($offer);
        $em->flush();

        $map = new Map();

        //workarround to ssl certificat pb curl error 60

        $config = [
            'verify' => false,
        ];

        $adapter = GuzzleAdapter::createWithConfig($config);

        // GeoCoder API
        $geocoder = new GeocoderService($adapter, new GuzzleMessageFactory());

        //try to match string location to get Object with lat long info
        if($offer->getLocation()){
            $request = new GeocoderAddressRequest($offer->getLocation());
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
            $marker->setVariable('marker');
            $map->setCenter($coord);
            $map->getOverlayManager()->addMarker($marker);
        }

        $map->setStylesheetOption('width', 1100);
        $map->setStylesheetOption('min-height', 1100);
        $map->setMapOption('zoom', 10);

        return $this->render('EmployerBundle:Offer:show.html.twig', array(
            'offer' => $offer,
            'similarOfferArray' => $similarOfferArray['offers'],
            'tags' => $similarOfferArray['tags'],
            'map' => $map,
            'status' => $status
        ));
    }

    public function activateAction(Request $request){
        $session = $request->getSession();

        $id = $request->get('id');

        $user = $this->getUser();

        $employerRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Employer')
        ;
        $employer = $employerRepository->findOneBy(array('id' => $user->getEmployer()));

        $offerRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Offer')
        ;
        $offer = $offerRepository->findOneBy(array('id' => $id));

        if(!isset($user) || !in_array('ROLE_EMPLOYER', $user->getRoles()) || $offer->getEmployer()->getId() != $employer->getId()){
            $translated = $this->get('translator')->trans('redirect.employer');
            $session->getFlashBag()->add('danger', $translated);
            return $this->redirectToRoute('create_employer');
        }

        $creditInfo = $this->container->get('app.credit_info');

        $creditEmployer = $employer->getCredit();
        $creditOffer = $creditInfo->getPublishOffer();

        if($creditEmployer < $creditOffer){
            $translated = $this->get('translator')->trans('form.offer.activate.error');
            $session->getFlashBag()->add('danger', $translated);
            return $this->redirectToRoute('jobnow_credit');
        }

        $employer->setCredit($creditEmployer - $creditOffer);

        $now =  new \DateTime();
        $next = new \DateTime();

        $offer->setStartDate($now);
        $offer->setUpdateDate($now);

        $offer->setEndDate($next->modify( '+ 2 month' ));

        $em = $this->getDoctrine()->getManager();
        $em->merge($offer);
        $em->merge($employer);
        $em->flush();

        $translated = $this->get('translator')->trans('form.offer.activate.success');
        $session->getFlashBag()->add('info', $translated);

        return $this->redirectToRoute('dashboard_employer', array('archived' => $_SESSION['archived']));
    }

    public function searchAction(Request $request){
        $keywords = $request->get('keyword');
        $location = $request->get('location');
        $employer = $request->get('employer');
        $tags = $request->get('tags');
        $type =  $request->get('type');
        $currentPage = $request->get('row');
        $numberOfItem =  $request->get('number');

        $searchParam = array(
            'keywords' => $keywords,
            'location' => $location,
            'employer' => $employer,
            'tags' => $tags,
            'type' => $type
        );
        $searchParam = json_encode($searchParam);

        $searchService = $this->get('app.search.offer');

        $data = $searchService->searchOffer($searchParam);

        $countResult = count($data);

        $generateUrlService = $this->get('app.offer_generate_url');

        foreach ($data as &$offer){
            $offer->setOfferUrl($generateUrlService->generateOfferUrl($offer));
        }

//        $locationArray =array();
//        foreach ($data as $offer){
//            $locationArray[$offer->getLocation()][] = $offer;
//        }
//
//        $map = new Map();
//
//        //workarround to ssl certificat pb curl error 60
//
//        $config = [
//            'verify' => false,
//        ];
//
//        $adapter = GuzzleAdapter::createWithConfig($config);
//
//        // GeoCoder API
//        $geocoder = new GeocoderService($adapter, new GuzzleMessageFactory());
//        $markers = array();
//        $i = 1;
//
//        foreach ($locationArray as $location => $offers){
//
//            //try to match string location to get Object with lat long info
//            if($location){
//                $request = new GeocoderAddressRequest($location);
//            }else{
//                $request = new GeocoderAddressRequest('228 Route d\'Esch, Luxembourg');
//            }
//
//            $response = $geocoder->geocode($request);
//
//            $status = $response->getStatus();
//
//            foreach ($response->getResults() as $result) {
//
//                $coord = $result->getGeometry()->getLocation();
//                continue;
//
//            }
//
//            if(isset($coord)) {
//                $marker = new Marker($coord);
//
//                $marker->setVariable('marker' . $i);
//                $content = '<p class="map-offer-container">';
//                foreach ($offers as $offer){
//                    $content .=  '<a class="map-offer" href="'.$this->generateUrl('show_offer', array('id' => $offer->getId(),'url' => $this->generateOfferUrl($offer))).'">'.$offer->getTitle().'</a>';
//                }
//                $content .= '</p>';
//                $infoWindow = new InfoWindow($content);
//                $infoWindow->setAutoOpen(true);
//                $infoWindow->setAutoClose(true);
//                $infoWindow->setOption('maxWidth', 400);
//                $marker->setInfoWindow($infoWindow);
//
//                $markers[] = $marker;
//            }
//            $i++;
//        }
//        $map->getOverlayManager()->addMarkers($markers);
//
//        $event = new Event(
//            $map->getVariable(),
//            'zoom_changed',
//            'function(){'.
//            $marker->getVariable().'.setMap(null)'
//            .'}'
//        );
//        $map->getEventManager()->addEvent($event);
//        $map->setCenter($coord);
//        $map->setStylesheetOption('width', 1000);
//        $map->setStylesheetOption('min-height', 1100);
//        $map->setMapOption('zoom', 2);


        $finalArray = array_slice($data, ($currentPage - 1 ) * $numberOfItem, $numberOfItem);

        $totalPage = ceil ($countResult / $numberOfItem);

        return $this->render('EmployerBundle:Offer:search-data.html.twig',
            array(
                'data' => $finalArray,
                'page' => $currentPage,
                'total' => $totalPage,
                'numberOfItem' =>($numberOfItem > $countResult? $countResult:$numberOfItem),
                'countResult' => $countResult,
                'searchParam' => $searchParam
            )
        );
    }

    public function searchPageAction(Request $request){
        $keywords = $request->get('keyword');
        $location = $request->get('location');
        //$chosenEmployer = $request->get('employer');
        $chosenTags = $request->get('tags');

        $contractTypeRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:ContractType')
        ;
        $contractType = $contractTypeRepository->findAll();

        $employerRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Employer')
        ;
        $employers = $employerRepository->findAll();


        $tagRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Tag')
        ;
        $tags = $tagRepository->findAll();


        $featuredOfferRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:FeaturedOffer')
        ;

        $featuredOffers = $featuredOfferRepository->getCurrentFeaturedOffer();

        $generateUrlService = $this->get('app.offer_generate_url');

        foreach ($featuredOffers as $featuredOffer){
            $featuredOffer->getOffer()->setOfferUrl($generateUrlService->generateOfferUrl($featuredOffer->getOffer()));
        }

        if(isset($chosenTags)){
            foreach ($chosenTags as &$tag){
                $newTag = array();
                $newTag['text'] = $this->get('translator')->trans($tag);
                $newTag['value'] = $tag;
                $tag = $newTag;
            }
        }

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

        return $this->render('EmployerBundle:Offer:searchPage.html.twig', array(
            'contractType' => $contractType,
            'keyword' => $keywords,
            'employers' => $employers,
//            'chosenEmployer'=>$chosenEmployer,
            'tags' => $tags,
            'chosenTags' => $chosenTags,
            'featuredOffer' => $featuredOffers,
            'autoComplete' => $autoCompleteHelper->render($autoComplete),
            'autoCompleteScript' => $apiHelper->render([$autoComplete])
        ));
    }

    public function boostAction(Request $request){
        $session = $request->getSession();

        $user = $this->getUser();

        $employerRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Employer')
        ;
        $employer = $employerRepository->findOneBy(array('id' => $user->getEmployer()));

        $offerRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Offer')
        ;

        if(!isset($user) || !in_array('ROLE_EMPLOYER', $user->getRoles())){
            $translated = $this->get('translator')->trans('redirect.employer');
            $session->getFlashBag()->add('danger', $translated);
            return $this->redirectToRoute('create_employer');
        }

        $creditInfo = $this->container->get('app.credit_info');

        $creditEmployer = $employer->getCredit();
        $creditBoost = $creditInfo->getBoostOffers();

        if($creditEmployer < $creditBoost){
            $translated = $this->get('translator')->trans('form.offer.boost.error');
            $session->getFlashBag()->add('danger', $translated);
            return $this->redirectToRoute('jobnow_credit');
        }

        $employer->setCredit($creditEmployer - $creditBoost);

        $offers = $offerRepository->findBy(array('employer' => $employer, 'archived' => false));
        $em = $this->getDoctrine()->getManager();
        if(count($offers) > 0){
            $now =  new \DateTime();
            foreach ($offers as $offer){
                $offer->setUpdateDate($now);
                $em->merge($offer);
            }
        }


        $em->merge($employer);
        $em->flush();

        $translated = $this->get('translator')->trans('form.offer.boost.success');
        $session->getFlashBag()->add('info', $translated);

        return $this->redirectToRoute('dashboard_employer', array('archived' => $_SESSION['archived']));
    }

    public function applyAction(Request $request){
        $session = $request->getSession();

        $user = $this->getUser();
        $id = $request->get('id');

        if(!isset($user) || in_array('ROLE_EMPLOYER', $user->getRoles())){
            return $this->redirectToRoute('create_candidate', array('offerId' => $id));
        }

        $comment = $request->get('comment');
        $target_dir = "uploads/images/candidate/";
        $target_file = $target_dir . md5(uniqid()) . basename($_FILES["cv"]["name"]);
        move_uploaded_file($_FILES["cv"]["tmp_name"], $target_file);

        $candidateRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Candidate')
        ;
        $candidate = $candidateRepository->findOneBy(array('user' => $user->getId()));

        $offerRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Offer')
        ;
        $offer = $offerRepository->findOneBy(array('id' => $id));

        $postulatedOfferRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:PostulatedOffers')
        ;
        $postulatedOffer = $postulatedOfferRepository->findBy(array('candidate' => $candidate, 'offer' => $offer));

        if(isset($postulatedOffer) && count($postulatedOffer) > 0){
            $translated = $this->get('translator')->trans('offer.apply.already');
            $session->getFlashBag()->add('danger', $translated);
            return $this->redirectToRoute('dashboard_candidate');
        }

        $userRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:User')
        ;
        $users = $userRepository->findBy(array('employer' => $offer->getEmployer()));

        $arrayEmail = array();

        foreach ($users as $user){
            $arrayEmail[] = $user->getEmail();
        }
        $firstUser = $arrayEmail[0];

        $mailer = $this->container->get('swiftmailer.mailer');

        $message = (new \Swift_Message('A candidate applied to the offer: ' . $offer->getTitle()))
            ->setFrom('jobnowlu@noreply.lu')
            ->setTo($firstUser)
            ->setCc(array_shift($arrayEmail))
            ->setBody(
                $this->renderView(
                    'AppBundle:Emails:apply.html.twig',
                    array('comment' => $comment)
                ),
                'text/html'
            )
            ->attach(\Swift_Attachment::fromPath($target_file));
        ;

        $mailer->send($message);

        $cv = $candidate->getCv();
        if(isset($cv) && $cv != ''){
            unlink($cv);
        }
        $candidate->setCv($target_file);

        $em = $this->getDoctrine()->getManager();

        $postulatedOffer = new PostulatedOffers();
        $postulatedOffer->setCandidate($candidate);
        $postulatedOffer->setOffer($offer);
        $now =  new \DateTime();
        $postulatedOffer->setDate($now);
        $postulatedOffer->setCoverLetter($comment);


        $offer->setCountContact($offer->getCountContact() +1);

        $em->merge($offer);
        $em->persist($postulatedOffer);
        $em->merge($candidate);
        $em->flush();

        $translated = $this->get('translator')->trans('offer.applied.success', array('title' => $offer->getTitle()));
        $session->getFlashBag()->add('info', $translated);

        return $this->redirectToRoute('dashboard_candidate');
    }

    public function offerNotFoundAction($id)
    {
        $offerRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Offer')
        ;
        $offer = $offerRepository->findOneBy(array('id' => $id));
        $similarOfferArray = $this->getSimilarOffers($offer);

        return $this->render('EmployerBundle:Offer:offerNotFound.html.twig', array(
            'similarOfferArray' => $similarOfferArray['offers'],
            'tags' => $similarOfferArray['tags'],
        ));
    }

    private function getSimilarOffers($offer){
        $tags = $offer->getTag();
        $similarOfferArray = array();
        $tagsArray = array();
        if(isset($tags)){
            $finder = $this->container->get('fos_elastica.finder.app.offer');
            $boolQuery = new \Elastica\Query\BoolQuery();

            $newBool = new \Elastica\Query\BoolQuery();


            foreach($tags as $tag){
                $tagsArray[] = $tag->getName();
                $tagQuery = new \Elastica\Query\Match();
                $tagQuery->setFieldQuery('tag.name', $tag->getName());
                $newBool->addShould($tagQuery);
            }

            $boolQuery->addMust($newBool);

            $fieldQuery = new \Elastica\Query\Match();
            $fieldQuery->setFieldQuery('archived', false);
            $boolQuery->addMust($fieldQuery);

            $fieldQuery = new \Elastica\Query\Match();
            $fieldQuery->setFieldQuery('id', $offer->getId());
            $boolQuery->addMustNot($fieldQuery);

            $query = new \Elastica\Query($boolQuery);

            $query->setSort(array('updateDate' => 'desc'));
            $similarOfferArray = $finder->find($query);
        }
        $generateUrlService = $this->get('app.offer_generate_url');
        foreach ($similarOfferArray as &$offer){
            $offer->setOfferUrl($generateUrlService->generateOfferUrl($offer));
        }

        return array('offers' => $similarOfferArray, 'tags' => $tagsArray);
    }

}