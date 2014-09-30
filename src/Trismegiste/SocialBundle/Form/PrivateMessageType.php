<?php

/*
 * iinano
 */

namespace Trismegiste\SocialBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Trismegiste\SocialBundle\Repository\NetizenRepositoryInterface;
use Trismegiste\SocialBundle\Repository\PrivateMessageRepository;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormInterface;

/**
 * PrivateMessageType is a form type for private message
 */
class PrivateMessageType extends AbstractType
{

    /** @var PrivateMessageRepository */
    protected $pmRepo;

    public function __construct(PrivateMessageRepository $p)
    {
        $this->pmRepo = $p;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('target', 'social_follower_type', ['mapped' => false])
                ->add('message', 'textarea')
                ->add('send', 'submit');
    }

    public function getName()
    {
        return 'social_private_message';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $factory = $this->pmRepo;
        $resolver->setDefaults([
            'empty_data' => function(FormInterface $form, $data) use ($factory) {
                $target = $form->get('target')->getData();
                return $form->isEmpty() || is_null($target) ? null : $factory->createNewMessageTo($target->getAuthor());
            },
            'data_class' => 'Trismegiste\Socialist\PrivateMessage'
        ]);
    }

}
