<?php

namespace Novuscom\CMFUserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('username', 'text', array(
            'label' => 'Логин',
            'attr' => array(
                'class' => 'form-control'
            )
        ));
        $builder->add('email', 'email', array(
            'label' => 'Email',
            'attr' => array(
                'class' => 'form-control'
            )
        ));
        /*$builder->add('locked', 'checkbox', array(
            'label'     => 'Блокировка',
            'required'  => false,

        ));*/


        $builder->add('password', 'repeated', array(
            'type' => 'password',
            'invalid_message' => 'Пароли не совпадают',
            'options' => array('attr' => array('class' => 'form-control')),
            'required' => false,
            'first_options' => array('label' => 'Новый пароль'),
            'second_options' => array('label' => 'Повторить новый пароль'),
            'mapped' => false,
        ));


        $builder->add('enabled', 'checkbox', array(
            'label' => 'Активность',
            'required' => false,
        ));
        $builder->add('roles', 'choice', array(
            'choices' => array(
                'ROLE_ADMIN' => 'Администратор',
                'ROLE_EDITOR' => 'Редактор',
            ),
            'required' => false,
            'attr' => array(
                'class' => 'form-control'
            ),
            'mapped' => true,
            'multiple' => true,
        ));
        $builder->add('groups', 'entity', array(
            'class' => 'NovuscomCMFUserBundle:Group',
            'property' => 'name',
            'expanded' => false,
            'multiple' => true,
            'required' => false,
            'attr' => array(
                'class' => 'form-control'
            )
        ));
        $builder->add('sites', 'entity', array(
            'class' => 'NovuscomCMFBundle:Site',
            'property' => 'name',
            'expanded' => false,
            'multiple' => true,
            'required' => false,
            'attr' => array(
                'class' => 'form-control'
            )
        ));


    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Novuscom\CMFUserBundle\Entity\User'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'cmf_NovuscomCMFUserBundle_user';
    }
}
