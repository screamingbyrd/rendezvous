<?php

namespace AdminBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;


class AdminController extends Controller
{

    public function indexAction()
    {
        return $this->render('AdminBundle::index.html.twig');
    }

    public function listEmployerAction(){

        $repository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:User')
        ;
        $users = $repository->findAll();

        $employers = [];
        foreach($users as $user)
        {
            if($user->getEmployer() != NULL)
            {
                $employers[] = $user;
            }
        }

        return $this->render('AdminBundle::list.html.twig', array(
            'employers' => $employers,
        ));

    }



}