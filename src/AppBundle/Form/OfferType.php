<?php
/**
 * Created by PhpStorm.
 * User: Altea IT
 * Date: 31/05/2018
 * Time: 15:44
 */

namespace AppBundle\Form;

use AppBundle\Entity\ContractType;
use AppBundle\Entity\Tag;
use Ivory\GoogleMapBundle\Form\Type\PlaceAutocompleteType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class OfferType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title',      TextType::class, array('required' => false))
            ->add('description', TextareaType::class, array(
                'required' => false,
            ))

            ->add('tag', EntityType::class, array(
                'required' => false,
                'class' => Tag::class,
                'choice_label' =>  'name',
                'placeholder' => 'Category',
                'multiple' => true,
                'expanded' => false,
            ))


            ->add('location', PlaceAutocompleteType::class)
            ->add('contractType', EntityType::class, array(
                'required' => false,
                'class' => ContractType::class,
                'choice_label' =>  'name',
                'placeholder' => 'form.offer.contractType',
                'choice_translation_domain' => 'messages',
            ))

            ->add('experience', ChoiceType::class, array('choices' => array(
                'form.registration.exp1' => 'form.registration.exp1',
                'form.registration.exp2' => 'form.registration.exp2',
                'form.registration.exp3' => 'form.registration.exp3',

            ),
                'placeholder' => 'form.registration.exp0',
            ))


            ->add('diploma', ChoiceType::class, array('choices' => array(
                'form.registration.exp1' => 'form.registration.exp1',
                'form.registration.exp2' => 'form.registration.exp2',
                'form.registration.exp3' => 'form.registration.exp3',

            ),
                'placeholder' => 'form.registration.exp0',
            ))

            ->add('wage', ChoiceType::class, array('choices' => array(
                'form.registration.exp1' => 'form.registration.exp1',

            ),
                'required' => false,
                'placeholder' => 'form.registration.exp0',
            ))

            ->add('benefits', ChoiceType::class, array('choices' => array(
                'form.registration.exp1' => 'form.registration.exp1',
                'form.registration.exp2' => 'form.registration.exp2',
                'form.registration.exp3' => 'form.registration.exp3',

            ),
                'required' => false,

                'placeholder' => 'form.registration.exp0',
            ))

            ->add('license', ChoiceType::class, array('choices' => array(
                'form.registration.lis1' => 'form.registration.lis1',
                'form.registration.lis2' => 'form.registration.lis2',
                'form.registration.lis3' => 'form.registration.lis3',
                'form.registration.lis4' => 'form.registration.lis4',
                'form.registration.lis5' => 'form.registration.lis5',
                'form.registration.lis6' => 'form.registration.lis6',
                'form.registration.lis7' => 'form.registration.lis7',
                'form.registration.lis8' => 'form.registration.lis8',
                'form.registration.lis9' => 'form.registration.lis9',
                'form.registration.lis10' => 'form.registration.lis10',
                'form.registration.lis11' => 'form.registration.lis11',
                'form.registration.lis12' => 'form.registration.lis12',
                'form.registration.lis13' => 'form.registration.lis13',
            ),
                'placeholder' => 'form.registration.lis0',
                'multiple' => true,
                'required' => false,
            ))


            ->add('image', ImageType::class, array(
                'required' => false,
            ))


            ->add('submit',      SubmitType::class)
            ->getForm()
        ;
    }/**
 * {@inheritdoc}
 */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Offer'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_employer';
    }


}