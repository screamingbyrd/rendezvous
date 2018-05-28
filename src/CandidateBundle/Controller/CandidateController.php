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
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\HttpFoundation\Request;

class CandidateController extends Controller
{
    public function createAction(Request $request)
    {
        $candidate = new Candidate();

        $form = $this->get('form.factory')->createBuilder(FormType::class)
            ->add('firstName',      TextType::class, array('required' => true))
            ->add('lastName',      TextType::class, array('required' => true))
            ->add('email',      EmailType::class, array('required' => true))
            ->add('password',      PasswordType::class, array('required' => true))
            ->add('description',      TextType::class, array('required' => false))
            ->add('age',      TextType::class, array('required' => false))
            ->add('experience',      TextType::class, array('required' => false))
            ->add('license',      TextType::class, array('required' => false))
            ->add('diploma',      TextType::class, array('required' => false))
            ->add('socialMedia',      TextType::class, array('required' => false))
            ->add('phone',      TextType::class, array('required' => true))
            ->add('save',      SubmitType::class)
            ->getForm()
        ;

        // Si la requête est en POST
        if ($request->isMethod('POST')) {


            // On fait le lien Requête <-> Formulaire
            // À partir de maintenant, la variable $advert contient les valeurs entrées dans le formulaire par le visiteur
            $form->handleRequest($request);
            // On vérifie que les valeurs entrées sont correctes
            // (Nous verrons la validation des objets en détail dans le prochain chapitre)
            if ($form->isValid()) {

                $data = $form->getData();

                $userManager = $this->get('fos_user.user_manager');

                $email_exist = $userManager->findUserByEmail($data['email']);

                // Check if the user exists to prevent Integrity constraint violation error in the insertion
                if($email_exist){
                    return false;
                }
                $user = $userManager->createUser();
                $user->setUsername($data['email']);
                $user->setEmail($data['email']);
                $user->setEmailCanonical($data['email']);
                $user->setFirstName($data['firstName']);
                $user->setLastName($data['lastName']);
                $user->setPlainPassword($data['password']);
                $userManager->updateUser($user);
                $user = $userManager->findUserByEmail($data['email']);
                $candidate->setUser($user);
                $candidate->setDescription($data['description']);
                $candidate->setAge($data['age']);
                $candidate->setExperience($data['experience']);
                $candidate->setLicense($data['license']);
                $candidate->setDiploma($data['diploma']);
                $candidate->setSocialMedia($data['socialMedia']);
                $candidate->setPhone($data['phone']);

                // On enregistre notre objet $advert dans la base de données, par exemple
                $em = $this->getDoctrine()->getManager();
                $em->persist($candidate);
                $em->flush();

                $request->getSession()->getFlashBag()->add('notice', 'Annonce bien enregistrée.');

                // On redirige vers la page de visualisation de l'annonce nouvellement créée
                return $this->redirectToRoute('/', array('_locale' => $candidate->getId()));
            }
        }



        // On passe la méthode createView() du formulaire à la vue
        // afin qu'elle puisse afficher le formulaire toute seule
        return $this->render('CandidateBundle:Candidate:create.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}