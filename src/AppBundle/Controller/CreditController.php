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
                return $this->buyPack($request,$creditInfo->getFiveCredit(),5);
                break;

            }
            case 3: {
                return $this->buyPack($request,$creditInfo->getTenCredit(),10);
                break;

            }
            default: {
                $creditEmployer = 0;
                $logsCredit = 0;
                $user =$this->getUser();
                if(isset($user)){
                    $employer = $this->getUser()->getEmployer();

                    if(isset($employer)){
                        $creditEmployer = $employer->getCredit();

                        $repository = $this
                            ->getDoctrine()
                            ->getManager()
                            ->getRepository('AppBundle:LogCredit')
                        ;
                        $logsCredit = $repository->findBy(array('employer' => $employer));
                    }

                }

                return $this->render('AppBundle:Credit:credit.html.twig', array(
                    'credit' => $creditEmployer,
                    'logsCredit' => $logsCredit,
                    'creditService' => $creditInfo
                ));
            }
        }
    }

    public function billsAction(Request $request){
        $currentPage = $request->get('row');
        $sort = $request->get('sort');
        $currentPage = isset($currentPage)?$currentPage:1;
        $sort = isset($sort)?$sort:'ASC';

        $numberOfItem = 50;

        $user = $this->getUser();

        $session = $request->getSession();

        if(!(isset($user) and  in_array('ROLE_EMPLOYER', $user->getRoles()))){
            $translated = $this->get('translator')->trans('redirect.employer');
            $session->getFlashBag()->add('danger', $translated);
            return $this->redirectToRoute('create_employer');
        }

        $employer = $this->getUser()->getEmployer();

        $repository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:LogCredit')
        ;
        $logsCredit = $repository->findBy(array('employer' => $employer),array('date' => $sort));

        $countResult = count($logsCredit);

        $finalArray = array_slice($logsCredit, ($currentPage - 1 ) * $numberOfItem, $numberOfItem);

        $totalPage = ceil ($countResult / $numberOfItem);

        return $this->render('AppBundle:Credit:bills.html.twig', [
            'logsCredit' => $finalArray,
            'page' => $currentPage,
            'total' => $totalPage,
            'sort' => $sort
        ]);

    }

    private function buyPack(Request $request, $price, $nbrCredit ){

        $user = $this->getUser();

        $session = $request->getSession();

        if(!(isset($user) and  in_array('ROLE_EMPLOYER', $user->getRoles()))){
            $translated = $this->get('translator')->trans('redirect.employer');
            $session->getFlashBag()->add('danger', $translated);
            return $this->redirectToRoute('create_employer');
        }

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

                    $translated = $this->get('translator')->trans('price.payment.success', array('%credits%' => $nbrCredit));
                    $session->getFlashBag()->add('info', $translated);

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
            'price' => $price,
            'nbrCredit' => $nbrCredit
        ]);

    }

}