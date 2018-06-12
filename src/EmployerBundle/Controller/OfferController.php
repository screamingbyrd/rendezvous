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

        $user = $this->getUser();
        $employerRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Employer')
        ;
        $employer = $employerRepository->findOneBy(array('id' => $user->getEmployer()));

        $form = $this->get('form.factory')->create(OfferType::class);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            $data = $form->getData();

            $em = $this->getDoctrine()->getManager();

            $offer = new Offer();
            $offer->setEmployer($employer);
            $offer->setDescription($data->getDescription());
            $offer->setLocation($data->getLocation());
            $offer->setContractType($data->getContractType());
            $offer->setImage($data->getImage());
            $offer->setTitle($data->getTitle());
            $offer->setWage($data->getWage());
            $offer->setExperience($data->getExperience());
            $offer->setDiploma($data->getDiploma());
            $offer->setBenefits($data->getBenefits());
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

        $form = $this->get('form.factory')->create(OfferType::class, $offer);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {

            $data = $form->getData();

            $em = $this->getDoctrine()->getManager();

            $offer->setDescription($data->getDescription());
            $offer->setImage($data->getImage());
            $offer->setTitle($data->getTitle());
            $offer->setLocation($data->getLocation());
            $offer->setWage($data->getWage());
            $offer->setExperience($data->getExperience());
            $offer->setDiploma($data->getDiploma());
            $offer->setBenefits($data->getBenefits());
            $offer->setCountView(0);
            $offer->setCountContact(0);

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

        if(!isset($user) || !in_array('ROLE_EMPLOYER', $user->getRoles()) || $offer->getEmployer()->getId() != $employer->getId()){
            $translated = $this->get('translator')->trans('form.offer.edition.error');
            $session->getFlashBag()->add('danger', $translated);
            return $this->redirectToRoute('dashboard_employer', array('archived' => $_SESSION['archived']));
        }

        $bool = boolval($offer->isArchived());
        $offer->setArchived(!$bool);

        $em = $this->getDoctrine()->getManager();
        $em->merge($offer);
        $em->flush();

        $translated = $this->get('translator')->trans('form.offer.delete.success');
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
            return $this->redirectToRoute('jobnow_home');
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

        $finalArray = array_slice($data, ($currentPage - 1 ) * $numberOfItem, $numberOfItem);

        $totalPage = ceil (count($data) / $numberOfItem);

        return $this->render('EmployerBundle:Offer:search-data.html.twig', array('data' => $finalArray, 'page' => $currentPage, 'total' => $totalPage));
    }

    public function searchPageAction(Request $request){
        $keywords = $request->get('keyword');
        $location = $request->get('location');

        $contractTypeRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:ContractType')
        ;
        $contractType = $contractTypeRepository->findAll();

        return $this->render('EmployerBundle:Offer:searchPage.html.twig', array('contractType' => $contractType, 'keyword' => $keywords, 'location' => $location));
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
            return $this->redirectToRoute('jobnow_home');
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
                // templates/emails/registration.html.twig
                    'Emails/apply.html.twig',
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

        $em->persist($postulatedOffer);
        $em->flush();

        $translated = $this->get('translator')->trans('offer.applied.success', array('title' => $offer->getTitle()));
        $session->getFlashBag()->add('info', $translated);

        return $this->redirectToRoute('dashboard_candidate');
    }

}