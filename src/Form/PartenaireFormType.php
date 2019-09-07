<?php

namespace App\Form;

use App\Entity\Partenaire;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PartenaireFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('raison_social')
            ->add('ninea')
            ->add('adresse')
            ->add('tel')
            // ->add('status')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Partenaire::class,
        ]);
    }
}
