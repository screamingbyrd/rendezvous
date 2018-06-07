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
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
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




            ->add('experience', ChoiceType::class, array('choices' => array(
                'Haut' => 'Haut',
                'Moyen' => 'Moyen',
                'Bas' => 'Bas'),
                'placeholder' => '-- Choisir le niveau d\'urgence --'
            ))


            ->add('location', PlaceAutocompleteType::class)
            ->add('contractType', EntityType::class, array(
                'required' => false,
                'class' => ContractType::class,
                'choice_label' =>  'name',
                'placeholder' => 'form.offer.contractType',
                'choice_translation_domain' => 'messages',
            ))
            ->add('image', ImageType::class, array(
                'required' => false,
            ))

            ->add('tag', EntityType::class, array(
                'required' => false,
                'class' => Tag::class,
                'choice_label' =>  'name',
                'placeholder' => 'Category',
                'multiple' => true,
                'expanded' => true,
            ))


            ->add('wage',      TextType::class, array('required' => false))
            ->add('benefits',      TextType::class, array('required' => false))
            ->add('diploma',      TextType::class, array('required' => false))
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