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

                $request->getSession()->getFlashBag()->add('notice', 'Annonce bien enregistrée.');

                $session->getFlashBag()->add('info', 'Candidat enregistrée !');

                return $this->redirectToRoute('jobnow_home');
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
                $candidate->setExperience($data->getExperience());
                $candidate->setLicense($data->getLicense());
                $candidate->setDiploma($data->getDiploma());
                $candidate->setSocialMedia($data->getSocialMedia());
                $candidate->setPhone($data->getPhone());

                $em = $this->getDoctrine()->getManager();
                $em->persist($candidate);
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

        return $this->render('CandidateBundle:Candidate:dashboard.html.twig', array());
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
}