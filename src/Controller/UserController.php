<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Form\RegistrationType;
use App\Repository\AnnonceRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
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
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/edition/{id}', name: 'admin.edit', methods: ['GET', 'POST'])]
    public function editAdmin(User $user, Request $request, EntityManagerInterface $manager, UserPasswordHasherInterface $hasher): Response
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
                $user = $form->getData();

                $manager->persist($user);
                $manager->flush();

                $this->addFlash('success', 'Votre compte a bien été modifié !');
                return $this->redirectToRoute('user.index');
            } else {
                $this->addFlash('danger', 'Votre mot de passe est incorrect !');
            }
        }
        return $this->render('pages/admin/editAdmin.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/createconsultant', name: 'admin.createConsultant', methods: ['GET', 'POST'])]
    public function createConsultant(Request $request, EntityManagerInterface $manager) : Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationType::class, $user);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $user = $form->getData();
            $user->setRoles(array('ROLE_CONSULTANT'));
            $user->setIsValid(true);
            $manager->persist($user);
            $manager->flush();

            $this->addFlash('success', 'Le consultant a bien été créé !');

            return $this->redirectToRoute('admin.consultantListe');
        }

        return $this->render('pages/admin/createConsultant.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/suppressionconsultant/{id}', name: 'admin.consultantDelete', methods: ['GET', 'POST'])]
    public function consultantDeleteAdmin(User $user, ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();
        $entityManager->remove($user);
        $entityManager->flush();
        $this->addFlash('danger', 'Le consultant à bien été supprimé !');
        return $this->redirectToRoute('admin.consultantListe');
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/activateconsultant/{id}', name: 'admin.consultantActivate', methods: ['GET', 'POST'])]
    public function consultantActivateAdmin(User $user, EntityManagerInterface $manager): Response
    {
        if ($user->getIsValid() == 0) {
            $user->setIsValid(1);
            $manager->persist($user);
            $manager->flush();
            $this->addFlash('success', 'Le consultant à bien été activé !');
            return $this->redirectToRoute('admin.consultantDetail', [
                'id' => $user->getId(),
            ]);
        }
        return $this->render('pages/admin/detailConsultant.html.twig', [
            'user' => $user,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/desactivateonsultant/{id}', name: 'admin.consultantDesactivate', methods: ['GET', 'POST'])]
    public function consultantDesactivateAdmin(User $user, EntityManagerInterface $manager): Response
    {
        if ($user->getIsValid() == 1) {
            $user->setIsValid(0);
            $manager->persist($user);
            $manager->flush();
            $this->addFlash('success', 'Le consultant à bien été désactivé !');
            return $this->redirectToRoute('admin.consultantDetail', [
                'id' => $user->getId(),
            ]);
        }
        return $this->render('pages/admin/detailConsultant.html.twig', [
            'user' => $user,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/detailconsultant/{id}', name: 'admin.consultantDetail', methods: ['GET', 'POST'])]
    public function consultantDetailAdmin(User $user): Response
    {
        return $this->render('pages/admin/detailConsultant.html.twig', [
            'user' => $user,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/listeconsultant', name: 'admin.consultantListe', methods: ['GET', 'POST'])]
    public function consultantListeAdmin(UserRepository $repositery, PaginatorInterface $paginator, Request $request): Response
    {
        $users = $paginator->paginate(
            $repositery->findBy([
                'roles' => ['["ROLE_CONSULTANT"]'],
            ]),/* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            6 /*limit per page*/
        );
        return $this->render('pages/admin/listeConsultant.html.twig', [
            'users' => $users,
        ]);
    }
    
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/suppressioncandidat/{id}', name: 'admin.candidatDelete', methods: ['GET', 'POST'])]
    public function candidatDeleteAdmin(User $user, ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();
        $entityManager->remove($user);
        $entityManager->flush();
        $this->addFlash('danger', 'Le candidat à bien été supprimé !');
        return $this->redirectToRoute('admin.candidatListe');
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/candidat/{id}', name: 'admin.candidatDetail', methods: ['GET', 'POST'])]
    public function candidatDetailAdmin(User $user): Response
    {
        return $this->render('pages/admin/detailCandidat.html.twig', [
            'user' => $user,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/candidat', name: 'admin.candidatListe', methods: ['GET', 'POST'])]
    public function candidatListeAdmin(UserRepository $repositery, PaginatorInterface $paginator, Request $request): Response
    {
        $users = $paginator->paginate(
            $repositery->findBy([
                'roles' => ['["ROLE_CANDIDAT"]'],
            ]),/* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            6 /*limit per page*/
        );
        return $this->render('pages/admin/listeCandidat.html.twig', [
            'users' => $users,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/suppressionrecruteur/{id}', name: 'admin.recruteurDelete', methods: ['GET', 'POST'])]
    public function recruteurDeleteAdmin(User $user, ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();
        $entityManager->remove($user);
        $entityManager->flush();
        $this->addFlash('danger', 'Le recruteur à bien été supprimé !');
        return $this->redirectToRoute('admin.recruteurListe');
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/recruteur/{id}', name: 'admin.recruteurDetail', methods: ['GET', 'POST'])]
    public function recruteurDetailAdmin(User $user): Response
    {
        return $this->render('pages/admin/detailRecruteur.html.twig', [
            'user' => $user,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/recruteur', name: 'admin.recruteurListe', methods: ['GET', 'POST'])]
    public function recruteurListeAdmin(UserRepository $repositery, PaginatorInterface $paginator, Request $request): Response
    {
        $users = $paginator->paginate(
            $repositery->findBy([
                'roles' => ['["ROLE_RECRUTEUR"]'],
            ]),/* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            6 /*limit per page*/
        );
        return $this->render('pages/admin/listeRecruteur.html.twig', [
            'users' => $users,
        ]);
    }
    
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

    #[Route('/utilisateur/suppression/{id}', name: 'user.delete', methods: ['GET', 'POST'])]
    public function deleteUser(User $user, ManagerRegistry $doctrine): Response
    {
        if (!$this->getUser()) {
            $this->addFlash('danger', 'Impossible de supprimer ce compte !');
            return $this->redirectToRoute('app_home');
        }
        $this->container->get('security.token_storage')->setToken(null);
        $entityManager = $doctrine->getManager();
        $entityManager->remove($user);
        $entityManager->flush();
        $this->addFlash('danger', 'Votre compte à bien été supprimé !');
        return $this->redirectToRoute('app_home');
    }

    #[IsGranted('ROLE_CONSULTANT')]
    #[Route('/consultant/uservalider/{id}', name: 'consultant.validUser', methods: ['GET', 'POST'])]
    public function validAd(User $user, EntityManagerInterface $manager): Response
    {
        if ($user->getIsValid() == 0) {
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
    public function board(UserRepository $repositery, AnnonceRepository $repositeryAd): Response
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
            6 /*limit per page*/
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

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/board', name: 'admin.index', methods: ['GET', 'POST'])]
    public function indexAdmin(): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('security.login');
        }
        return $this->render('pages/admin/index.html.twig', [
            'user' => $this->getUser(),
        ]);
    }
    
}
