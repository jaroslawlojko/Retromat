<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class Activity2Type extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('retromatId')
            ->add('phase', ChoiceType::class, ['choices' => array_combine(['Set the stage', 'Gather data', 'Generate Insight', 'Decide what to do', 'Close', 'Something completely different'], range(0, 5))])
            ->add('name', TextareaType::class, ['attr' => ['cols' => '100', 'rows' => 1]])
            ->add('summary', TextareaType::class, ['attr' => ['cols' => '100', 'rows' => 1]])
            ->add('desc', TextareaType::class, ['label' => 'Description', 'attr' => ['cols' => '100', 'rows' => '10'],])
            ->add('duration', TextareaType::class, ['attr' => ['cols' => '100', 'rows' => 1]])
            ->add('source', TextareaType::class, ['attr' => ['cols' => '100', 'rows' => 1]])
            ->add('more', TextareaType::class, ['attr' => ['cols' => '100', 'rows' => 1]])
            ->add('suitable', TextareaType::class, ['attr' => ['cols' => '100', 'rows' => 1]]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Activity2'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_activity2';
    }


}
