<?php

namespace Novuscom\CMFUserBundle\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Controller\RegistrationController as BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class RegistrationController extends BaseController
{
	public function registerAction(Request $request)
	{
		/** @var $formFactory \FOS\UserBundle\Form\Factory\FactoryInterface */
		$formFactory = $this->get('fos_user.registration.form.factory');
		/** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
		$userManager = $this->get('fos_user.user_manager');
		/** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
		$dispatcher = $this->get('event_dispatcher');

		$user = $userManager->createUser();
		$user->setEnabled(true);

		$event = new GetResponseUserEvent($user, $request);
		$dispatcher->dispatch(FOSUserEvents::REGISTRATION_INITIALIZE, $event);

		if (null !== $event->getResponse()) {
			return $event->getResponse();
		}
		$em = $this->getDoctrine()->getManager();
		$userCount = $em
			->createQuery('SELECT COUNT(n.id) FROM NovuscomCMFUserBundle:User n')
			->getSingleScalarResult();
		//echo '<pre>' . print_r($userCount, true) . '</pre>';

		$form = $formFactory->createForm();

		if ($userCount==0) {
			$form->add('check', TextType::class, array(
				'label' => 'Пароль к базе данных проекта',
				'attr' => array(
					'class' => 'form-control'
				),
				'mapped' => false,
				'constraints'=>new Constraints\EqualTo(array(
					'value' => $this->container->getParameter('database_password'),
					'message'=>'Пароль к базе данных указан неверно'
				))
			));
		}

		$form->setData($user);
		$form->handleRequest($request);



		if ($form->isValid()) {
			$user->addRole('ROLE_USER');
			$userAdmin = false;
			if ($form->has('check') && $form->get('check')->isValid()) {
				$user->addRole('ROLE_ADMIN');
				$userAdmin = true;
			}

			$event = new FormEvent($form, $request);
			$dispatcher->dispatch(FOSUserEvents::REGISTRATION_SUCCESS, $event);

			$userManager->updateUser($user);

			if (!$userAdmin && null === $response = $event->getResponse()) {
				$url = $this->generateUrl('fos_user_registration_confirmed');
				$response = new RedirectResponse($url);
			}

			$dispatcher->dispatch(FOSUserEvents::REGISTRATION_COMPLETED, new FilterUserResponseEvent($user, $request, $response));

			return $response;
		}

		return $this->render('FOSUserBundle:Registration:register.html.twig', array(
			'form' => $form->createView(),
		));
	}
	/**
	 * Tell the user to check his email provider
	 */
	public function checkEmailAction()
	{
		$email = $this->get('session')->get('fos_user_send_confirmation_email/email');

		if (!$email) {
			throw new NotFoundHttpException(sprintf('Не найден email'));
		}

		//$this->get('session')->remove('fos_user_send_confirmation_email/email');
		$user = $this->get('fos_user.user_manager')->findUserByEmail($email);
		$this->get('security.context')->setToken(null);

		if (null === $user) {
			throw new NotFoundHttpException(sprintf('The user with email "%s" does not exist', $email));
		}

		return $this->render('FOSUserBundle:Registration:checkEmail.html.twig', array(
			'user' => $user,
		));
	}
	/**
	 * Receive the confirmation token from user email provider, login the user
	 */
	public function confirmAction(Request $request, $token)
	{
		/** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
		$userManager = $this->get('fos_user.user_manager');

		$user = $userManager->findUserByConfirmationToken($token);

		if (null === $user) {
			throw new NotFoundHttpException(sprintf('The user with confirmation token "%s" does not exist', $token));
		}

		/** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
		$dispatcher = $this->get('event_dispatcher');

		$user->setConfirmationToken(null);
		$user->setEnabled(true);

		$event = new GetResponseUserEvent($user, $request);
		$dispatcher->dispatch(FOSUserEvents::REGISTRATION_CONFIRM, $event);

		$userManager->updateUser($user);
		$this->get('security.context')->setToken(null);
		if (null === $response = $event->getResponse()) {
			$url = $this->generateUrl('fos_user_registration_confirmed');
			$response = new RedirectResponse($url);
		}

		$dispatcher->dispatch(FOSUserEvents::REGISTRATION_CONFIRMED, new FilterUserResponseEvent($user, $request, $response));

		return $response;
	}
}