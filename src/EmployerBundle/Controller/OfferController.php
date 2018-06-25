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


class OfferController extends Controller
{

    public function postAction(Request $request)
    {

        $session = $request->getSession();

        $offer = new Offer();

        $form = $this->get('form.factory')->create(OfferType::class, $offer);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            $em = $this->getDoctrine()->getManager();

            $offer->setEmployer($this->getUser()->getEmployer());

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
            $translated = $this->get('translator')->trans('form.offer.edition.error');
            $session->getFlashBag()->add('danger', $translated);
            return $this->redirectToRoute('jobnow_home');
        }

        $form = $this->get('form.factory')->create(OfferType::class, $offer);

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

        $offer->setCountView($offer->getCountView() +1);

        $em = $this->getDoctrine()->getManager();
        $em->merge($offer);
        $em->flush();

        return $this->render('EmployerBundle:Offer:show.html.twig', array(
            'offer' => $offer,
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
            $translated = $this->get('translator')->trans('form.offer.edition.error');
            $session->getFlashBag()->add('danger', $translated);
            return $this->redirectToRoute('jobnow_home');
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
        $finder = $this->container->get('fos_elastica.finder.app.offer');
        $boolQuery = new \Elastica\Query\BoolQuery();


        if($keywords != ''){
            $fieldQuery = new \Elastica\Query\Match();
            $fieldQuery->setFieldQuery('title', $keywords);
            $boolQuery->addMust($fieldQuery);
        }

        if($location != ''){
            $fieldQuery = new \Elastica\Query\Match();
            $fieldQuery->setFieldQuery('location', $location);
            $boolQuery->addMust($fieldQuery);
        }

        if($employer != ''){
            $fieldQuery = new \Elastica\Query\Match();
            $fieldQuery->setFieldQuery('employer.name', $employer);
            $boolQuery->addMust($fieldQuery);
        }

        if(isset($tags)){

            $newBool = new \Elastica\Query\BoolQuery();


           foreach($tags as $tag){

               $tagQuery = new \Elastica\Query\Match();
               $tagQuery->setFieldQuery('tag.name', $tag);
               $newBool->addShould($tagQuery);
           }

            $boolQuery->addMust($newBool);
        }

        if(isset($type)){
            $categoryQuery = new \Elastica\Query\Terms();
            $categoryQuery->setTerms('contractType.name', $type);
            $boolQuery->addMust($categoryQuery);
        }

        $fieldQuery = new \Elastica\Query\Match();
        $fieldQuery->setFieldQuery('archived', false);
        $boolQuery->addMust($fieldQuery);

        $boolQuery->addMust($fieldQuery);

        $query = new \Elastica\Query($boolQuery);


        $query->setSort(array('updateDate' => 'desc'));
        $data = $finder->find($query);
        $countResult = count($data);

        $finalArray = array_slice($data, ($currentPage - 1 ) * $numberOfItem, $numberOfItem);

        $totalPage = ceil ($countResult / $numberOfItem);

        return $this->render('EmployerBundle:Offer:search-data.html.twig',
            array(
                'data' => $finalArray,
                'page' => $currentPage,
                'total' => $totalPage,
                'numberOfItem' =>($numberOfItem > $countResult? $countResult:$numberOfItem),
                'countResult' => $countResult
            )
        );
    }

    public function searchPageAction(Request $request){
        $keywords = $request->get('keyword');
        $location = $request->get('location');
        $chosenEmployer = $request->get('employer');
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

        $featuredOffer = $featuredOfferRepository->getCurrentFeaturedOffer();

        return $this->render('EmployerBundle:Offer:searchPage.html.twig', array(
            'contractType' => $contractType,
            'keyword' => $keywords,
            'location' => $location,
            'employers' => $employers,
            'chosenEmployer'=>$chosenEmployer,
            'tags' => $tags,
            'chosenTags' => $chosenTags,
            'featuredOffer' => $featuredOffer
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
            $translated = $this->get('translator')->trans('form.offer.edition.error');
            $session->getFlashBag()->add('danger', $translated);
            return $this->redirectToRoute('jobnow_home');
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

        $offerRepository->boostOffer($employer->getId());

        $em = $this->getDoctrine()->getManager();
        $em->merge($employer);
        $em->flush();

        $translated = $this->get('translator')->trans('form.offer.boost.success');
        $session->getFlashBag()->add('info', $translated);

        return $this->redirectToRoute('dashboard_employer', array('archived' => $_SESSION['archived']));
    }

    public function applyAction(Request $request){
        $session = $request->getSession();

        $id = $request->get('id');
        $comment = $request->get('comment');
        $target_dir = "uploads/images/candidate/";
        $target_file = $target_dir . basename($_FILES["cv"]["name"]);
        move_uploaded_file($_FILES["cv"]["tmp_name"], $target_file);
        $user = $this->getUser();

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
        unlink($target_file);

        $em = $this->getDoctrine()->getManager();

        $postulatedOffer = new PostulatedOffers();
        $postulatedOffer->setCandidate($candidate);
        $postulatedOffer->setOffer($offer);
        $now =  new \DateTime();
        $postulatedOffer->setDate($now);

        $offer->setCountContact($offer->getCountContact() +1);

        $em->merge($offer);
        $em->persist($postulatedOffer);
        $em->flush();

        $translated = $this->get('translator')->trans('offer.applied.success', array('title' => $offer->getTitle()));
        $session->getFlashBag()->add('info', $translated);

        return $this->redirectToRoute('dashboard_candidate');
    }

}