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

        $creditInfo = $this->container->get('app.credit_info');

        switch ($id) {

            case 1: {

                return $this->buyPack($request,$creditInfo->getOneCredit(),1);
                break;
            }
            case 2: {
                return $this->buyPack($request,$creditInfo->getTenCredit(),10);
                break;

            }
            case 3: {
                return $this->buyPack($request,$creditInfo->getFiftyCredit(),50);
                break;

            }
            default: {
                $employer = $this->getUser()->getEmployer();

                $creditEmployer = $employer->getCredit();

                $repository = $this
                    ->getDoctrine()
                    ->getManager()
                    ->getRepository('AppBundle:LogCredit')
                ;
                $logsCredit = $repository->findBy(array('employer' => $employer));


                return $this->render('AppBundle:Credit:credit.html.twig', array(
                    'credit' => $creditEmployer,
                    'logsCredit' => $logsCredit,
                    'creditService' => $creditInfo
                ));
            }

        }

    }

    private function buyPack(Request $request, $price, $nbrCredit ){

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
                try {

                    $data = $form->getData();

                    $this->get('app.client.stripe')->createPremiumCharge($this->getUser(), $data['token'] ,$price,$this->getUser()->getEmployer(),$nbrCredit);

                    //Logging credit purchase in db
                    $logCredit = new LogCredit();
                    $em = $this->getDoctrine()->getManager();

                    $logCredit->setDate(new \DateTime());
                    $logCredit->setCredit($nbrCredit);
                    $logCredit->setEmployer($this->getUser()->getEmployer());
                    $logCredit->setPrice($price);

                    $em->persist($logCredit);
                    $em->flush();

                    return $this->redirectToRoute('jobnow_credit');

                } catch (\Stripe\Error\Base $e) {
                    $this->addFlash('warning', sprintf('Unable to take payment, %s', $e instanceof \Stripe\Error\Card ? lcfirst($e->getMessage()) : 'please try again.'));
                    return $this->redirectToRoute('jobnow_payment', array('id' => 1));
                }

            }
        }
        return $this->render('AppBundle:Credit:stripePayment.html.twig', [
            'form' => $form->createView(),
            'stripe_public_key' => $this->getParameter('stripe_public_key'),
        ]);

    }

}