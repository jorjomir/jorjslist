<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, array('data_class' => null, 'label' => false,
                'attr' => array('style' => 'width: 500px')))
            ->add('content', TextareaType::class, array('data_class' => null, 'label' => false,
                'attr' => array('class' => 'ckeditor')))
            ->add('image', FileType::class, array('data_class' => null, 'label' => false,
                'required' => false));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Article'
        ));
    }
}
