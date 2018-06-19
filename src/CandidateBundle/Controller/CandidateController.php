<?php
/**
 * Created by PhpStorm.
 * User: Altea IT
 * Date: 28/05/2018
 * Time: 12:00
 */

namespace CandidateBundle\Controller;

use AppBundle\Entity\Candidate;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Form\CandidateType;

class CandidateController extends Controller
{
    public function createAction(Request $request)
    {
        $session = $request->getSession();

        $candidate = new Candidate();

        $form = $this->get('form.factory')->create(CandidateType::class);

        // Si la requête est en POST
        if ($request->isMethod('POST')) {


            // On fait le lien Requête <-> Formulaire
            // À partir de maintenant, la variable $advert contient les valeurs entrées dans le formulaire par le visiteur
            $form->handleRequest($request);
            // On vérifie que les valeurs entrées sont correctes
            // (Nous verrons la validation des objets en détail dans le prochain chapitre)
            if ($form->isValid()) {

                $data = $form->getData();

                $userRegister = $this->get('app.user_register');


                $user = $userRegister->register($data->getEmail(),$data->getEmail(),$data->getPassword(),$data->getFirstName(),$data->getLastName(), 'ROLE_CANDIDATE');


                if($user != false){
                $candidate->setUser($user);
                $candidate->setDescription($data->getDescription());
                $candidate->setAge($data->getAge());
                $candidate->setExperience($data->getExperience());
                $candidate->setLicense($data->getLicense());
                $candidate->setDiploma($data->getDiploma());
                $candidate->setSocialMedia($data->getSocialMedia());
                $candidate->setPhone($data->getPhone());

                // On enregistre notre objet $advert dans la base de données, par exemple
                $em = $this->getDoctrine()->getManager();
                $em->persist($candidate);
                $em->flush();

                $translated = $this->get('translator')->trans('form.registration.successCandidate');
                $session->getFlashBag()->add('info', $translated);

                return $this->redirectToRoute('jobnow_home');

                }else{
                    $translated = $this->get('translator')->trans('form.registration.error');
                    $session->getFlashBag()->add('danger', $translated);

                    return $this->redirectToRoute('jobnow_home');
                }
            }
        }



        // On passe la méthode createView() du formulaire à la vue
        // afin qu'elle puisse afficher le formulaire toute seule
        return $this->render('CandidateBundle:Candidate:create.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function editAction(Request $request ){
        $user = $this->getUser();
        if(!isset($user) || !in_array('ROLE_CANDIDATE', $user->getRoles())){
            return $this->redirectToRoute('create_candidate');
        }

        $candidateRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Candidate')
        ;
        $candidate = $candidateRepository->findOneBy(array('user' => $user->getId()));

        $session = $request->getSession();

        $candidate->setFirstName($user->getFirstName());
        $candidate->setLastName($user->getLastName());
        $candidate->setEmail($user->getEmail());

        $form = $this->get('form.factory')->create(CandidateType::class, $candidate);

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

                $candidate->setDescription($data->getDescription());
                $candidate->setAge($data->getAge());
                $candidate->setExperience($this->get('translator')->trans($data->getExperience()));
                $candidate->setLicense($data->getLicense());
                $candidate->setDiploma($data->getDiploma());
                $candidate->setSocialMedia($data->getSocialMedia());
                $candidate->setPhone($data->getPhone());
                $candidate->setModifiedDate( new \datetime());

                $em = $this->getDoctrine()->getManager();
                $em->merge($candidate);
                $em->flush();

                $session->getFlashBag()->add('info', 'Candidat modifié !');

                return $this->redirectToRoute('jobnow_home');
            }
        }

        return $this->render('CandidateBundle:Candidate:edit.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function dashboardAction(){
        $user = $this->getUser();
        if(!isset($user) || !in_array('ROLE_CANDIDATE', $user->getRoles())){
            return $this->redirectToRoute('create_candidate');
        }

        $candidateRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Candidate')
        ;
        $candidate = $candidateRepository->findOneBy(array('user' => $user));

        $employerRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Employer')
        ;
        $tagRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Tag')
        ;

        $notificationRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Notification')
        ;
        $notifications = $notificationRepository->findBy(array('candidate' => $candidate));

        $notificationsArray = array();

        foreach ($notifications as $notification){
            $newNotification = array();
            $newNotification['id'] = $notification->getId();
            $type = $notification->getTypeNotification();
            $newNotification['type'] = $type;
            if($type == 'employer'){
                $employer = $employerRepository->findOneBy(array('id' => $notification->getElementId()));
                $newNotification['name'] = $employer->getName();
            }elseif ($type == 'tag'){
                $tag = $tagRepository->findOneBy(array('id' => $notification->getElementId()));
                $newNotification['name'] = $tag->getName();
            }
            $notificationsArray[] = $newNotification;
        }

        $postulatedOfferRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:PostulatedOffers')
        ;
        $postulatedOffers = $postulatedOfferRepository->findBy(array('candidate' => $candidate));

        $offerIdArray = $finalArray = array();

        foreach ($postulatedOffers as $postulatedOffer) {
            $offerIdArray[] = $postulatedOffer->getOffer()->getId();
            $finalArray[$postulatedOffer->getOffer()->getId()]['date'] = $postulatedOffer->getDate();
        }

        $offerRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Offer')
        ;
        $offers = $offerRepository->findById($offerIdArray);

        foreach ($offers as $offer){
            $finalArray[$offer->getId()]['offer'] = $offer;
        }

        return $this->render('CandidateBundle:Candidate:dashboard.html.twig',
            array(
                'appliedOffer' => $finalArray,
                'notifications' => $notificationsArray
            ));
    }

    public function deleteAction(Request $request){

        $session = $request->getSession();
        $user = $this->getUser();

        $candidateRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Candidate')
        ;
        $candidate = $candidateRepository->findOneBy(array('user' => $user->getId()));

        $em = $this->getDoctrine()->getManager();
        $em->remove($candidate);
        $em->remove($user);
        $em->flush();

        $session->getFlashBag()->add('info', 'Candidat supprimé !');

        return $this->redirectToRoute('jobnow_home');
    }

    public function showAction($id){
        $candidateRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Candidate')
        ;
        $candidate = $candidateRepository->findOneBy(array('id' => $id));

        return $this->render('CandidateBundle:Candidate:show.html.twig', array(
            'candidate' => $candidate,
        ));
    }

    public function searchAction(){
        $candidateRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Candidate')
        ;
        $candidates = $candidateRepository->findAll();

        return $this->render('CandidateBundle:Candidate:search.html.twig', array(
            'candidates' => $candidates,
        ));
    }
}