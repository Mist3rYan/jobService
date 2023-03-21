<?php

namespace App\Controller;

use App\Entity\Annonce;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\AnnonceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class UserController extends AbstractController
{

    #[IsGranted('ROLE_USER')]
    #[Route('/utilisateur/moncompte', name: 'user.index', methods: ['GET', 'POST'])]
    public function index(AnnonceRepository $repositery): Response
    {
        $annonceValides = $repositery->findBy(["id_candidat_valid" => $this->getUser()]);
        $annonceInvalides = $repositery->findBy(["id_candidat_invalid" => $this->getUser()]);
        $annonceEnAttentes = $repositery->findBy(["id_candidat_attente" => $this->getUser()]);
        if (!$this->getUser()) {
            return $this->redirectToRoute('security.login');
        }
        return $this->render('pages/user/index.html.twig', [
            'user' => $this->getUser(),
            'annonceValides' => $annonceValides,
            'annonceInvalides' => $annonceInvalides,
            'annonceEnAttentes' => $annonceEnAttentes,
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/utilisateur/moncomptecandidat', name: 'user.boardCandidat', methods: ['GET', 'POST'])]
    public function boardCandidat(AnnonceRepository $repositery): Response
    {
        $annonces = $repositery->findAll();
        if (!$this->getUser()) {
            return $this->redirectToRoute('security.login');
        }
        return $this->render('pages/user/boardCandidat.hmtl.twig', [
            'user' => $this->getUser(),
            'annonces' => $annonces,
        ]);
    }

    #[IsGranted('ROLE_USER')]
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

    #[IsGranted('ROLE_USER')]
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

    //Decandidature
    #[IsGranted('ROLE_CANDIDAT')]
    #[Route('/utilisateur/annulationcandidature/{id}/{idCandidat}', name: 'user.decandidater', methods: ['GET', 'POST'])]
    public function decandidater(Annonce $annonce, $idCandidat, EntityManagerInterface $manager): Response
    {
        $tab = $annonce->getIdCandidatInvalid();
        $tab = array_filter($tab,function($valeur) use ($idCandidat) {
            return $valeur != $idCandidat;
        });
        $annonce->setIdCandidatInvalid($tab);

        $tab = $annonce->getIdCandidatValid();
        $tab = array_filter($tab,function($valeur) use ($idCandidat) {
            return $valeur != $idCandidat;
        });
        $annonce->setIdCandidatValid($tab);

        $tab = $annonce->getId_candidat_attente();
        $tab = array_filter($tab,function($valeur) use ($idCandidat) {
            return $valeur != $idCandidat;
        });
        $annonce->setId_candidat_attente($tab);
        $manager->persist($annonce);
        $manager->flush();
        $this->addFlash('success', "Votre candidature à bien été retirée !");
        return $this->redirectToRoute('user.boardCandidat');
    }

    //Candidature
    #[IsGranted('ROLE_CANDIDAT')]
    #[Route('/utilisateur/candidature/{id}/{idCandidat}', name: 'user.candidater', methods: ['GET', 'POST'])]
    public function candidater(Annonce $annonce, $idCandidat, EntityManagerInterface $manager): Response
    {
        $tab = $annonce->getId_candidat_attente();
        $occurrences = array_count_values($tab);
        if (!isset($occurrences[$idCandidat]) || $occurrences[$idCandidat] < 1) {
            array_push($tab, $idCandidat);
        }
        $annonce->setId_candidat_attente($tab);
        $manager->persist($annonce);
        $manager->flush();
        $this->addFlash('success', "Votre candidature à bien été enregistrée ! Un consultant doit la valider.");
        return $this->redirectToRoute('user.boardCandidat');
    }

}
