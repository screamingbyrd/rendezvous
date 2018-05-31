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

    public function registerAction(Request $request)
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

                $em->persist($employer);
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

    public function postOfferAction(Request $request)
    {

        $session = $request->getSession();

        $user = $this->getUser();
        $employerRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Employer')
        ;
        $employer = $employerRepository->findBy(array('user' => $user));

        $form = $this->get('form.factory')->create(OfferType::class);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {

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

            $em->persist($offer);
            $em->flush();

            $translated = $this->get('translator')->trans('form.registration.successEmployer');
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

        $form = $this->get('form.factory')->create(OfferType::class);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {

            $data = $form->getData();

            $em = $this->getDoctrine()->getManager();

            $offer = new Offer();
            $offer->setDescription($data->getDescription());
            $offer->setImage($data->getImage());
            $offer->setTitle($data->getTitle());
            $offer->setWage($data->getWage());
            $offer->setExperience($data->getExperience());
            $offer->setDiploma($data->getDiploma());
            $offer->setBenefits($data->getBenefits());
            $offer->setCountView(0);
            $offer->setCountContact(0);

            $em->persist($offer);
            $em->flush();

            $translated = $this->get('translator')->trans('form.registration.successEmployer');
            $session->getFlashBag()->add('info', $translated);

            return $this->redirectToRoute('jobnow_home');

        }
        return $this->render('EmployerBundle:form:postOfferr.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}
