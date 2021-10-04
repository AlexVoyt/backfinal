<?php

namespace App\Controller;

use App\Entity\Group;
use App\Entity\User;
use App\Repository\GroupRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;


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
    public function show(GroupRepository $rep, $name): Response
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
        
        return $this->render('group/show.html.twig', [
            "group" => $group
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

    
    #[Route('/edit/{name}', name: '.edit')]
    public function edit(Request $request, $name, GroupRepository $group_rep): Response
    {
        // TODO
        return $this->redirect($this->generateUrl('groups.browse'));
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
