<?php

namespace EmployerBundle\Controller;

use AppBundle\Entity\Employer;
use AppBundle\Form\EmployerType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\BrowserKit\Request;

class EmployerController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('', array('name' => $name));
    }

    public function registerAction(Request $request)
    {
        $employer = new Employer();

        $form = $this->get('form.factory')->create(EmployerType::class);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {


            $data = $form->getData();

            $user = $this->register($data['email'],$data['email'],$data['password']);

            if(isset($user)){
                // the user is now registered !

                $em = $this->getDoctrine()->getManager();

                $employer->setName($data['name']);
                $employer->setDescription($data['description']);
                $employer->setCredit(0);
                $employer->setWhyUs($data['whyUs']);
                $employer->setLocation($data['location']);
                $employer->setLatLong($data['latLong']);
                $employer->setPhone($data['phone']);
                $employer->addUser($user);
                $employer->setLogo($data['logo']);
                $employer->setCoverImage($data['coverImage']);

                $em->persist($employer);
                $em->flush();

                return $this->redirectToRoute('ai_gestion_view', array('id' => $residence->getId()));

            }else{
                // the user exists already !
            }

            // $session->getFlashBag()->add('info', 'Résidence enregistrée !');


        }

        return $this->render('AIGestionBundle:Residence:addResidenceForm.html.twig', array(
            'form' => $form->createView(),
        ));
    }






    /**
     * This method registers an user in the database manually.
     *
     * @return boolean User registered / not registered
     **/
    private function register($email,$username,$password){
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
        $user->setLocked(0); // don't lock the user
        $user->setEnabled(1); // enable the user or enable it later with a confirmation token in the email
        // this method will encrypt the password with the default settings :)
        $user->setPlainPassword($password);
        $userManager->updateUser($user);

        return $user;
    }









}
