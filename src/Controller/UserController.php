<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\AnnonceRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class UserController extends AbstractController
{
    #[Route('/utilisateur/moncompte', name: 'user.index', methods: ['GET', 'POST'])]
    public function index(): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('security.login');
        }
        return $this->render('pages/user/index.html.twig', [
            'user' => $this->getUser(),
        ]);
    }

    #[IsGranted('ROLE_CONSULTANT')]
    #[Route('/consultant/uservalider/{id}', name: 'consultant.validUser', methods: ['GET','POST'])]
    public function validAd(User $user, EntityManagerInterface $manager): Response
    {
        if($user->getIsValid() == 0){
            $user->setIsValid(1);
            $manager->persist($user);
            $manager->flush();
            $this->addFlash('success', 'Le prodil a bien été validé !');
            return $this->redirectToRoute('consultant.listeUser');
        }
        return $this->render('pages/consultant/detailUser.html.twig', [
            'user' => $user,
        ]);
    }

    #[IsGranted('ROLE_CONSULTANT')]
    #[Route('/consultant/panneaudecontrole', name: 'consultant.board', methods: ['GET', 'POST'])]
    public function board(UserRepository $repositery, AnnonceRepository $repositeryAd, Request $request): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('security.login');
        }
        return $this->render('pages/consultant/board.html.twig', [
            'user' => $this->getUser(),
            'nbAd' => count($repositeryAd->findBy([
                "isValid" => "0"
            ])),
            'nbUser' => count($repositery->findBy([
                'roles' => ['["ROLE_CANDIDAT"]', '["ROLE_RECRUTEUR"]'],
                "isValid" => "0"
            ])),
        ]);
    }

    #[IsGranted('ROLE_CONSULTANT')]
    #[Route('/consultant/listeutilisateur', name: 'consultant.listeUser', methods: ['GET', 'POST'])]
    public function listeUser(UserRepository $repositery, PaginatorInterface $paginator, Request $request): Response
    {
        $users = $paginator->paginate(
            $repositery->findBy([
                'roles' => ['["ROLE_CANDIDAT"]', '["ROLE_RECRUTEUR"]'],
                "isValid" => "0"
            ]),/* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            7 /*limit per page*/
        );
        return $this->render('pages/consultant/listeUser.html.twig', [
            'users' => $users,
        ]);
    }

    #[IsGranted('ROLE_CONSULTANT')]
    #[Route('/consultant/detailutilisateur/{id}', name: 'consultant.detailUser', methods: ['GET', 'POST'])]
    public function detailUser(User $user): Response
    {
        return $this->render('pages/consultant/detailUser.html.twig', [
            'user' => $user,
        ]);
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
            } else {
                $this->addFlash('danger', 'Votre mot de passe est incorrect !');
            }
        }
        return $this->render('pages/user/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
