<?php

namespace App\Controller;

use App\Entity\Group;
use App\Entity\GroupMessage;
use App\Entity\User;
use App\Repository\GroupRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Exception;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;


#[Route('/groups', name: 'groups')]
/**
 * @IsGranted("ROLE_USER")
 */
class GroupController extends AbstractController
{
    #[Route('/browse', name: '.browse')]
    public function browse(GroupRepository $rep, Request $request): Response
    {
 
        $groups = $rep->findAll();
 
        return $this->render('group/browse.html.twig', [
            "groups" => $groups
        ]);
    }

    // TODO: make name optional and redirect?
    #[Route('/show/{name}', name: '.show')]
    public function show(GroupRepository $rep, $name, Request $request): Response
    {
        $group = $rep->findOneBy([
            'name' => $name
        ]);

        if($group == null)
        {
            // TODO: not found group page
            echo "Group not found";
            die;
        }
        
        $form = $this->createFormBuilder()
        ->add('Message', TextareaType::class, [
            'attr' => [
                'rows' => 2,
            ]
        ])
        ->add('Send_message', SubmitType::class, [
            'attr' => [   
                'class' => 'btn btn-primary float-right success'
            ]
        ])
        ->getForm();

        $form->handleRequest($request);
        if($form->isSubmitted())
        {
            try
            {
                $data = $form->getData();
                $user = $this->getUser();
                 
                $message = new GroupMessage();
                $message->setMessage($data["Message"]);
                $message->setSender($user);
                $message->setLocation($group);


                $em = $this->getDoctrine()->getManager(); 
                $em->persist($message);
                $em->flush();

                return $this->redirect($this->generateUrl('groups.browse'));
                return $this->redirect($this->generateUrl('groups.show', $name));
            } 
            catch (Exception $exp) // TODO: is this correct?
            {
                // TODO: well, something nasty could be here (for example, message submitted when group disbanded)
                echo "something went wrong during message sending";
                die;
            }
        }

        return $this->render('group/show.html.twig', [
            "group" => $group,
            "message_form" => $form->createView()
        ]);
    }
    

    #[Route('/create', name: '.create')]
    public function create(Request $request): Response
    {
        $form = $this->createFormBuilder()
            ->add('Name')
            ->add('Create_new_group', SubmitType::class, [
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
                $user = $this->getUser();
                 
                $group = new Group();
                $group->setName($data["Name"]);
                $group->setLeader($user);
                $group->setMotd("");

                $em = $this->getDoctrine()->getManager(); 
                $em->persist($group);
                $em->flush();

                return $this->redirect($this->generateUrl('groups.browse'));
            } 
            catch (UniqueConstraintViolationException $exp) // TODO: is this correct?
            {

                $this->addFlash('fail', 'Group with this name already exists');
            }
            
        }

        return $this->render('group/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/delete/{name}', name: '.delete')]
    public function delete(Request $request, $name, GroupRepository $group_rep): Response
    {
        $group = $group = $group_rep->findOneBy([
            'name' => $name,
        ]);
        $authenticated_user = $this->getUser();
        $authenticated_user->disbandGroup($group);
        $this->getDoctrine()->getManager()->flush();

        return $this->redirect($this->generateUrl('groups.browse'));
    }

    #[Route('/leave/{name}', name: '.leave')]
    public function leave(Request $request, $name, GroupRepository $group_rep): Response
    {
        $group = $group = $group_rep->findOneBy([
            'name' => $name,
        ]);
        $authenticated_user = $this->getUser();
        $authenticated_user->leaveGroup($group);
        $this->getDoctrine()->getManager()->flush();

        return $this->redirect($this->generateUrl('groups.browse'));
    }

    #[Route('/join/{name}', name: '.join')]
    public function join(Request $request, $name, GroupRepository $group_rep): Response
    {
        $group = $group_rep->findOneBy([
            'name' => $name,
        ]);
        $authenticated_user = $this->getUser();
        $authenticated_user->joinGroup($group);
        $this->getDoctrine()->getManager()->flush();

        return $this->redirect($this->generateUrl('groups.browse'));
    }
    
}
