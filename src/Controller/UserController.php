<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{
    #[Route('/utilisateur/moncompte', name: 'user.index', methods: ['GET', 'POST'])]
    public function index(): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('security.login');
        }
        return $this->render('pages/user/index.html.twig' , [
            'user' => $this->getUser(),
        ]) ;
    }

    #[Route('/consultant/panneaudecontrole', name: 'consultant.board', methods: ['GET', 'POST'])]
    public function board(): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('security.login');
        }
        return $this->render('pages/consultant/board.html.twig' , [
            'user' => $this->getUser(),
        ]) ;
    }

    #[Route('/utilisateur/edition/{id}', name: 'user.edit', methods: ['GET', 'POST'])]
    public function edit(User $user, Request $request, EntityManagerInterface $manager, UserPasswordHasherInterface $hasher): Response
    {
        if (!$this->getUser()) {
            $this->addFlash('danger', 'Merci de vous connecter !');
            return $this->redirectToRoute('security.login');
        }
        if ($this->getUser() !== $user) {
            $this->addFlash('danger', 'Ce compte ne vous appartient pas !');
            return $this->redirectToRoute('app_home');
        }
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($hasher->isPasswordValid($user, $user->getPlainPassword())) {
                $file = $form->get('cv')->getData();
                if ($file) {
                    $fileName = md5(uniqid()) . '.' . $file->guessExtension();
                    try {
                        $file->move($this->getParameter('upload_destination'), $fileName);
                    } catch (FileException $e) {
                        // ... handle exception if something happens during file upload
                    }
                    $user->setCv($fileName);
                }

                $manager->persist($user);
                $manager->flush();

                $this->addFlash('success', 'Votre compte a bien été modifié !');
                return $this->redirectToRoute('user.index');
                
            }
            else {
                $this->addFlash('danger', 'Votre mot de passe est incorrect !');
            }
        }
        return $this->render('pages/user/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
