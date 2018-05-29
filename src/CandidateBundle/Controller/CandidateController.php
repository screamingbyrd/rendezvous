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

    public function dashboardAction(){
        $user = $this->getUser();
        if(!isset($user) || !in_array('ROLE_CANDIDATE', $user->getRoles())){
            return $this->redirectToRoute('create_candidate');
        }

        return $this->render('CandidateBundle:Candidate:dashboard.html.twig', array(
        ));
    }
}