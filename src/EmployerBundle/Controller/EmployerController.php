<?php

namespace EmployerBundle\Controller;

use AppBundle\Entity\Employer;
use AppBundle\Entity\Offer;
use AppBundle\Form\EmployerType;
use AppBundle\Form\OfferType;
use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;


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

                $user->setEmployer($employer);

                $em->persist($employer);
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

                $employer->setName($data->getEmail());
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

    public function dashboardAction(){



        return $this->render('EmployerBundle::dashboard.html.twig', array(

        ));
    }

    public function postOfferAction(Request $request)
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

    public function editOfferAction(Request $request)
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

    public function deleteOfferAction(Request $request){

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

    public function activateOfferAction(Request $request){
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
