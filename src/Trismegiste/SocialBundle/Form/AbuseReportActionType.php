<?php

/*
 * iinano
 */

namespace Trismegiste\SocialBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList;

/**
 * AbuseReportActionType is a form for action choices in abuse report listing
 */
class AbuseReportActionType extends AbstractType
{

    protected $listing;

    public function __construct(\Iterator $listing)
    {
        $this->listing = $listing;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('selection', 'choice', [
                    'choice_list' => new AdminSelectionChoice($this->listing),
                    'expanded' => true,
                    'multiple' => true
                ])
                ->add('action', 'choice', [
                    'empty_value' => '---',
                    'choices' => ['reset report', 'delete content']
                ])
                ->add('makeItSo', 'submit');
    }

    public function getName()
    {
        return 'admin_abusereport_action';
    }

}
