<?php

namespace AppBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, array('label' => false))
            ->add('summary', TextType::class, array('label' => false,
                'attr' => array('maxLength' => 38)))
            ->add('description', TextareaType::class, array('label' => false))
            ->add('categoryId', EntityType::class, array(
                'class' =>'AppBundle\Entity\Category',
                'choice_label' => 'name', 'placeholder' =>'Choose...', 'label' => false))
            ->add('town', TextType::class, array('label' => false))
            ->add('phoneNumber', TextType::class, array('label' => false))
            ->add('price', TextType::class, array('label' => false))
            ->add('images', FileType::class, array('label' => false, 'required' => false));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Ad'
        ));
    }
}
