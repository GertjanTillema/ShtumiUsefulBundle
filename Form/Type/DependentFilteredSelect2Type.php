<?php

namespace Shtumi\UsefulBundle\Form\Type;

use Shtumi\UsefulBundle\Form\DataTransformer\EntityToIdTransformer;
use Shtumi\UsefulBundle\Form\DataTransformer\EntityToSelect2ValueTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class DependentFilteredSelect2Type extends AbstractType
{

    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'empty_value'       => '',
            'entity_alias'      => null,
            'parent_field'      => null,
            'compound'          => false,
            'preferred_value'    => null,
            'preferred_text'    => null,
        ));
    }

    public function getParent()
    {
        return 'form';
    }

    public function getName()
    {
        return 'shtumi_dependent_filtered_select2';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $entities = $this->container->getParameter('shtumi.dependent_filtered_entities');
        $options['class'] = $entities[$options['entity_alias']]['class'];
        $options['property'] = $entities[$options['entity_alias']]['property'];

        $options['no_result_msg'] = $entities[$options['entity_alias']]['no_result_msg'];

        $builder->addViewTransformer(new EntityToSelect2ValueTransformer(
            $this->container->get('doctrine')->getManager(),
            $options['class']
        ), true);

        $builder->setAttribute("parent_field", $options['parent_field']);
        $builder->setAttribute("entity_alias", $options['entity_alias']);
        $builder->setAttribute("no_result_msg", $options['no_result_msg']);
        $builder->setAttribute("empty_value", $options['empty_value']);

    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['parent_field'] = $form->getConfig()->getAttribute('parent_field');
        $view->vars['entity_alias'] = $form->getConfig()->getAttribute('entity_alias');
        $view->vars['no_result_msg'] = $form->getConfig()->getAttribute('no_result_msg');
        $view->vars['empty_value'] = $form->getConfig()->getAttribute('empty_value');
        $view->vars['preferred_value'] = $form->getConfig()->getOption('preferred_value');
        $view->vars['preferred_text'] = $form->getConfig()->getOption('preferred_text');
    }

}
