<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'register')]
    public function register(Request $request, UserPasswordHasherInterface $hasher): Response
    {
        $form = $this->createFormBuilder()
            ->add('username')
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'required' => true,
                'first_options' => ['label' => 'Password'],
                'second_options' => ['label' => 'Confirm password']
            ])
            ->add('Register', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-primary float-right success'
                ]
            ])
            ->getForm()
        ;
        
        $form->handleRequest($request);
        if($form->isSubmitted())
        {
            try
            {
                $data = $form->getData();
                $user = new User();
                $user->setUsername($data['username']);
                $user->setPassword($hasher->hashPassword($user, $data['password']));
                
                $em = $this->getDoctrine()->getManager(); 
                $em->persist($user);
                $em->flush();
    
                return $this->redirect($this->generateUrl('app_login'));
            } 
            catch (UniqueConstraintViolationException $exp) // TODO: is this correct?
            {
                $this->addFlash('fail', 'User with this username already registered');
            }
            
        }

        return $this->render('registration/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
