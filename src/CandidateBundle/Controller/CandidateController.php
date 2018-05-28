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
                $user = $this->register($data->getEmail(),$data->getEmail(),$data->getPassword(),$data->getFirstName(),$data->getLastName());
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

    /**
     * This method registers an user in the database manually.
     *
     * @return User
     **/
    private function register($email,$username,$password,$firstName,$lastName){
        $userManager = $this->get('fos_user.user_manager');

        // Or you can use the doctrine entity manager if you want instead the fosuser manager
        // to find
        //$em = $this->getDoctrine()->getManager();
        //$usersRepository = $em->getRepository("mybundleuserBundle:User");
        // or use directly the namespace and the name of the class
        // $usersRepository = $em->getRepository("mybundle\userBundle\Entity\User");
        //$email_exist = $usersRepository->findOneBy(array('email' => $email));

        $email_exist = $userManager->findUserByEmail($email);

        // Check if the user exists to prevent Integrity constraint violation error in the insertion
        if($email_exist){
            return false;
        }

        $user = $userManager->createUser();
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setEmailCanonical($email);
        $user->setFirstName($firstName);
        $user->SetLastName($lastName);
        $user->setPlainPassword($password);
        $userManager->updateUser($user);

        return $user;
    }
}