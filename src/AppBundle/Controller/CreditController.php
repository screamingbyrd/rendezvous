<?php
/**
 * Created by PhpStorm.
 * User: Altea IT
 * Date: 30/05/2018
 * Time: 16:21
 */

namespace AppBundle\Controller;

use AppBundle\Entity\LogCredit;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\NotBlank;


class CreditController extends Controller
{

    public function creditAction(Request $request, $id = 0)
    {
        switch ($id) {
            case 0: {
                $employer = $this->getUser()->getEmployer();

                $creditEmployer = $employer->getCredit();

                return $this->render('AppBundle:Credit:credit.html.twig', array(
                    'credit' => $creditEmployer,

                ));
                break;
            }
            case 1: {
                $logCredit = new LogCredit();










                $form = $this->get('form.factory')
                    ->createNamedBuilder('payment-form')
                    ->add('token', HiddenType::class, [
                        'constraints' => [new NotBlank()],
                    ])
                    ->add('submit', SubmitType::class)
                    ->getForm();
                if ($request->isMethod('POST')) {
                    $form->handleRequest($request);
                    if ($form->isValid()) {
                        // TODO: charge the card
                    }
                }
                return $this->render('AppBundle:Credit:stripePayment.html.twig', [
                    'form' => $form->createView(),
                    'stripe_public_key' => $this->getParameter('stripe_public_key'),
                ]);





                break;
            }
            case 2: {


                break;
            }

        }








    }


}