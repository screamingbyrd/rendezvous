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
            $offer->setEmployerId($employer->getId());
            $offer->setDescription($data->getDescription());
            $offer->setImage($data->getImage());
            $offer->setTitle($data->getTitle());
            $offer->setWage($data->getWage());
            $offer->setExperience($data->getExperience());
            $offer->setDiploma($data->getDiploma());
            $offer->setBenefits($data->getBenefits());
            $offer->setCountView(0);
            $offer->setCountContact(0);
            $now = new \DateTime();
            $offer->setStartDate($now);
            $offer->setEndDate($now->modify( '+ 1 month' ));

            $em->persist($offer);
            $em->flush();

            $translated = $this->get('translator')->trans('form.offer.creation.success');
            $session->getFlashBag()->add('info', $translated);

            return $this->redirectToRoute('jobnow_home');

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

        if(!isset($user) || !in_array('ROLE_EMPLOYER', $user->getRoles()) || $offer->getEmployerId() != $employer->getId()){
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

            return $this->redirectToRoute('jobnow_home');

        }
        return $this->render('EmployerBundle:form:editOffer.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function deleterAction(Request $request){

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

        if(!isset($user) || !in_array('ROLE_EMPLOYER', $user->getRoles()) || $offer->getEmployerId() != $employer->getId()){
            $translated = $this->get('translator')->trans('form.offer.edition.error');
            $session->getFlashBag()->add('danger', $translated);
            return $this->redirectToRoute('jobnow_home');
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($offer);
        $em->flush();

        $translated = $this->get('translator')->trans('form.offer.delete.success');
        $session->getFlashBag()->add('info', $translated);

        return true;
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

        if(!isset($user) || !in_array('ROLE_EMPLOYER', $user->getRoles()) || $offer->getEmployerId() != $employer->getId()){
            $translated = $this->get('translator')->trans('form.offer.edition.error');
            $session->getFlashBag()->add('danger', $translated);
            return $this->redirectToRoute('jobnow_home');
        }

        $creditInfo = $this->container->get('app.credit_info');

        $creditEmployer = $employer->getCrediit();
        $creditOffer = $creditInfo->getPublishOffer();

        if($employer->getCrediit() < $creditInfo->getPublishOffer()){
            $translated = $this->get('translator')->trans('form.offer.activate.error');
            $session->getFlashBag()->add('danger', $translated);
            return $this->redirectToRoute('jobnow_home');
        }

        $employer->setCountCredit($creditEmployer - $creditOffer);

        $now = new \DateTime();
        $offer->setStartDate($now);
        $offer->setEndDate($now->modify( '+ 1 month' ));

        $em = $this->getDoctrine()->getManager();
        $em->merge($offer);
        $em->merge($employer);
        $em->flush();

        $translated = $this->get('translator')->trans('form.offer.activate.success');
        $session->getFlashBag()->add('info', $translated);

        return true;
    }

}