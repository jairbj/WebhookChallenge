<?php

namespace AppBundle\Form;

use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MessageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('destination', EntityType::class, [
                'class' => 'AppBundle\Entity\Destination',
                'property_path' => 'destination',
                'description' => 'Destination ID'
            ])
            ->add('contentType', TextType::class)
            ->add('msgBody', TextareaType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Message',
            'csrf_protection' => false,
        ));
    }

    public function getName()
    {
        return 'app_bundle_message_type';
    }
}
