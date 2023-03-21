<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Annonce;
use App\Form\RegistrationType;
use App\Repository\UserRepository;
use App\Repository\AnnonceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AdminController extends AbstractController
{
    //Edite compte admin
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

    //création d'un consultant
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/createconsultant', name: 'admin.createConsultant', methods: ['GET', 'POST'])]
    public function createConsultant(Request $request, EntityManagerInterface $manager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
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

    //suppression d'un consultant
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/suppressionconsultant/{id}', name: 'admin.consultantDelete', methods: ['GET', 'POST'])]
    public function consultantDeleteAdmin(User $user, UserRepository $utilisateur, AnnonceRepository $annonce, EntityManagerInterface $manager,  ManagerRegistry $doctrine): Response
    {
        $annonces = $annonce->findBy(['id_validaton_consultant' => $user->getId()]);
        foreach ($annonces as $annonce) {
            $annonce->setIdValidatonConsultant('');
            $manager->persist($annonce);
            $manager->flush();
        }
        $utilisateurs = $utilisateur->findBy(['idConsultantValidate' => $user->getId()]);
        foreach ($utilisateurs as $utilisateur) {
            $utilisateur->setIdConsultantValidate(null);
            $manager->persist($utilisateur);
            $manager->flush();
        }
        $entityManager = $doctrine->getManager();
        $entityManager->remove($user);
        $entityManager->flush();
        $this->addFlash('danger', 'Le consultant à bien été supprimé !');
        return $this->redirectToRoute('admin.consultantListe');
    }

    //activer un consultant
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

    //desactiver un consultant
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

    //Fiche consultant
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/detailconsultant/{id}', name: 'admin.consultantDetail', methods: ['GET', 'POST'])]
    public function consultantDetailAdmin(User $user): Response
    {
        return $this->render('pages/admin/detailConsultant.html.twig', [
            'user' => $user,
        ]);
    }

    //Fiche consultant qui a valider l'annonce
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/voirconsultant/{id}/{idAnnonce}', name: 'admin.viewConsultant', methods: ['GET', 'POST'])]
    public function viewConsultantAdmin($id, $idAnnonce, AnnonceRepository $annonce, UserRepository $user): Response
    {
        $user = $user->find($id);
        $annonce = $annonce->find($idAnnonce);
        return $this->render('pages/admin/viewConsultant.html.twig', [
            'user' => $user,
            'annonce' => $annonce,
        ]);
    }

    //Fiche consultant qui a valide le candidat
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/voiruser/{id}/{idUser}', name: 'admin.viewConsultantUser', methods: ['GET', 'POST'])]
    public function viewConsultantUserAdmin($id, $idUser, UserRepository $user2, UserRepository $user): Response
    {
        $user = $user->find($id);
        $userBis = $user2->find($idUser);
        return $this->render('pages/admin/viewUser.html.twig', [
            'user' => $user,
            'userBis' => $userBis,
        ]);
    }

    //Fiche consultant qui a valide le recruteur
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/voirrecruteur/{id}/{idUser}', name: 'admin.viewConsultantRecruteur', methods: ['GET', 'POST'])]
    public function viewConsultantRecruteurAdmin($id, $idUser, UserRepository $user2, UserRepository $user): Response
    {
        $user = $user->find($id);
        $userBis = $user2->find($idUser);
        return $this->render('pages/admin/viewRecruteur.html.twig', [
            'user' => $user,
            'userBis' => $userBis,
        ]);
    }

    //liste des consultants
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

    //Supprimer un candidat
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

    //Fiche candidat
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/candidat/{id}', name: 'admin.candidatDetail', methods: ['GET', 'POST'])]
    public function candidatDetailAdmin(User $user): Response
    {
        return $this->render('pages/admin/detailCandidat.html.twig', [
            'user' => $user,
        ]);
    }

    //liste des candidats
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

    //Supprimer un recruteur
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

    //Fiche recruteur
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/recruteur/{id}', name: 'admin.recruteurDetail', methods: ['GET', 'POST'])]
    public function recruteurDetailAdmin(User $user): Response
    {
        return $this->render('pages/admin/detailRecruteur.html.twig', [
            'user' => $user,
        ]);
    }

    //liste des recruteurs
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

    //PAGE TABLEAU ADMINISTRATION
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

    //Supprimer une annonce
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/annonce/delete/{id}', name: 'annonce.deleteAdmin', methods: ['GET'])]
    public function deleteAdmin(Annonce $annonce, ManagerRegistry $doctrine): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY'); // on verifie que l'utilisateur est connecté
        $em = $doctrine->getManager();
        $em->remove($annonce); // on supprime l'objet $post
        $em->flush();
        $this->addFlash(
            'danger',
            "L' annonce a été supprimée !"
         );
        //redirection vers la page d'accueil
        return $this->redirectToRoute('admin.listeAd');
    }

    //Fiche annonce
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/detail/{id}', name: 'admin.detailAd', methods: ['GET', 'POST'])]
    public function adDetailAdmin(Annonce $annonce): Response
    {
        $nomRecuteur = $annonce->getRecruteur()->getEmail();
        return $this->render('pages/admin/detailAd.html.twig', [
            'annonce' => $annonce,
            'nomRecuteur' => $nomRecuteur,
        ]);
    }

    //liste des annonces
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/annonce', name: 'admin.listeAd', methods: ['GET', 'POST'])]
    public function adListeAdmin(AnnonceRepository $repositery, PaginatorInterface $paginator, Request $request): Response
    {
        $annonces = $paginator->paginate(
            $repositery->findAll(), /* query NOT result */
            $request->query->getInt('page',1), /*page number*/
            6 /*limit per page*/
        );
        return $this->render('pages/admin/listeAd.html.twig', [
            'annonces' => $annonces,
        ]);
    }
}
