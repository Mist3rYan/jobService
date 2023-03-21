<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Annonce;
use App\Repository\UserRepository;
use App\Repository\AnnonceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\Constraints\NotNull;

class ConsultantController extends AbstractController
{

    //Valide un utilisateur
    #[IsGranted('ROLE_CONSULTANT')]
    #[Route('/consultant/uservalider/{id}/{idConsultant}', name: 'consultant.validUser', methods: ['GET', 'POST'])]
    public function validUser(User $user, $idConsultant, EntityManagerInterface $manager): Response
    {
        if ($user->getIsValid() == 0) {
            $user->setIsValid(1);
            $user->setIdConsultantValidate($idConsultant);
            $manager->persist($user);
            $manager->flush();
            $this->addFlash('success', 'Le prodil a bien été validé !');
            return $this->redirectToRoute('consultant.listeUser');
        }
        return $this->render('pages/consultant/detailUser.html.twig', [
            'user' => $user,
        ]);
    }

    //affiche la page administration consultant
    #[IsGranted('ROLE_CONSULTANT')]
    #[Route('/consultant/panneaudecontrole', name: 'consultant.board', methods: ['GET', 'POST'])]
    public function board(UserRepository $repositery, AnnonceRepository $repositeryAd): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('security.login');
        }

        $nbCandidatures = array_filter(
            $repositeryAd->findAll(),
            function ($annonce) {
                return !empty($annonce->getId_candidat_attente());
            }
        );
        $i=0;
        foreach ($nbCandidatures as $candidature){
            foreach ($candidature->getId_candidat_attente() as $candidat){
                $i++;
            }
        }
        return $this->render('pages/consultant/board.html.twig', [
            'user' => $this->getUser(),
            'nbCandidature' => $i,
            'nbAd' => count($repositeryAd->findBy([
                "isValid" => "0"
            ])),
            'nbUser' => count($repositery->findBy([
                'roles' => ['["ROLE_CANDIDAT"]', '["ROLE_RECRUTEUR"]'],
                "isValid" => "0"
            ])),
        ]);
    }

    //affiche la liste des utilisateurs à valider
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

    //affiche un utilisateurs à valider
    #[IsGranted('ROLE_CONSULTANT')]
    #[Route('/consultant/detailutilisateur/{id}', name: 'consultant.detailUser', methods: ['GET', 'POST'])]
    public function detailUser(User $user): Response
    {
        return $this->render('pages/consultant/detailUser.html.twig', [
            'user' => $user,
        ]);
    }

    //Valide une annonce
    #[IsGranted('ROLE_CONSULTANT')]
    #[Route('/consultant/annoncevalider/{id}/{idConsultant}', name: 'consultant.validAd', methods: ['GET', 'POST'])]
    public function validAd(Annonce $annonce, $idConsultant, EntityManagerInterface $manager): Response
    {
        if ($annonce->getIsValid() == 0) {
            $annonce->setIsValid(1);
            $annonce->setIdValidatonConsultant($idConsultant);
            $manager->persist($annonce);
            $manager->flush();
            $this->addFlash('success', "L'annonce a bien été validée !");
            return $this->redirectToRoute('consultant.listeAd');
        }
        return $this->render('pages/consultant/detailAd.html.twig', [
            'annonce' => $annonce,
        ]);
    }

    //Affiche la liste des annonces à valider
    #[IsGranted('ROLE_CONSULTANT')]
    #[Route('/consultant/annonce', name: 'consultant.listeAd', methods: ['GET', 'POST'])]
    public function listeAd(AnnonceRepository $repositery, PaginatorInterface $paginator, Request $request): Response
    {
        $annonces = $paginator->paginate(
            $repositery->findBy(["isValid" => "0"]), /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            6 /*limit per page*/
        );
        return $this->render('pages/consultant/listeAd.html.twig', [
            'annonces' => $annonces,
        ]);
    }

    //Affiche une annonce à valider
    #[IsGranted('ROLE_CONSULTANT')]
    #[Route('/consultant/detail/{id}', name: 'consultant.detailAd', methods: ['GET', 'POST'])]
    public function detailAd(Annonce $annonce): Response
    {
        $nomRecuteur = $annonce->getRecruteur()->getEmail();
        return $this->render('pages/consultant/detailAd.html.twig', [
            'annonce' => $annonce,
            'nomRecuteur' => $nomRecuteur,
        ]);
    }

    //Affiche une annonce qui a un candidat en attente
    #[IsGranted('ROLE_CONSULTANT')]
    #[Route('/consultant/detailadcandidature/{id}', name: 'consultant.detailAdCand', methods: ['GET', 'POST'])]
    public function detailAdCand(Annonce $annonce): Response
    {
        $nomRecuteur = $annonce->getRecruteur()->getEmail();
        return $this->render('pages/consultant/detailAdCand.html.twig', [
            'annonce' => $annonce,
            'nomRecuteur' => $nomRecuteur,
        ]);
    }

    //affiche un utilisateurs à valider
    #[IsGranted('ROLE_CONSULTANT')]
    #[Route('/consultant/detailutilisateurcandidature/{id}', name: 'consultant.detailUserCan', methods: ['GET', 'POST'])]
    public function detailUserCan(User $user): Response
    {
        return $this->render('pages/consultant/detailUserCan.html.twig', [
            'user' => $user,
        ]);
    }
    //affiche la liste des candidatures à valider
    #[IsGranted('ROLE_CONSULTANT')]
    #[Route('/consultant/listeCandidature', name: 'consultant.listeCandidature', methods: ['GET', 'POST'])]
    public function listeCandidature(AnnonceRepository $repositeryAd, UserRepository $repositery, PaginatorInterface $paginator, Request $request): Response
    {
        $annonces = array_filter(
            $repositeryAd->findAll(),
            function ($annonce) {
                return !empty($annonce->getId_candidat_attente());
            }
        );
        $users = $repositery->findAll();
        return $this->render('pages/consultant/listeCandidature.html.twig', [
            'users' => $users,
            'annonces' => $annonces,
        ]);
    }

        //Candidature VALIDE
        #[IsGranted('ROLE_CONSULTANT')]
        #[Route('/consultant/candidatureaccepter/{id}/{idCandidat}', name: 'user.candidatAccept', methods: ['GET', 'POST'])]
        public function candidatAccept(Annonce $annonce, $idCandidat, EntityManagerInterface $manager): Response
        {
            $tab = $annonce->getIdCandidatValid();
            $occurrences = array_count_values($tab);
            if (!isset($occurrences[$idCandidat]) || $occurrences[$idCandidat] < 1) {
                array_push($tab, $idCandidat);
            }
            $annonce->setIdCandidatValid($tab);

            $tab = $annonce->getId_candidat_attente();
            $tab = array_filter($tab,function($valeur) use ($idCandidat) {
                return $valeur != $idCandidat;
            });
            $annonce->setId_candidat_attente($tab);

            $manager->persist($annonce);
            $manager->flush();
            $this->addFlash('success', "La candidature a bien été acceptée !");
            return $this->redirectToRoute('consultant.listeCandidature');
        }

    //Candidature refusee
    #[IsGranted('ROLE_CONSULTANT')]
    #[Route('/consultant/candidature refuser/{id}/{idCandidat}', name: 'user.candidatDenied', methods: ['GET', 'POST'])]
    public function candidatDenied(Annonce $annonce, $idCandidat, EntityManagerInterface $manager): Response
    {
        $tab = $annonce->getIdCandidatInvalid();
        $occurrences = array_count_values($tab);
        if (!isset($occurrences[$idCandidat]) || $occurrences[$idCandidat] < 1) {
            array_push($tab, $idCandidat);
        }
        $annonce->setIdCandidatInvalid($tab);

        $tab = $annonce->getId_candidat_attente();
        $tab = array_filter($tab,function($valeur) use ($idCandidat) {
            return $valeur != $idCandidat;
        });
        $annonce->setId_candidat_attente($tab);

        $manager->persist($annonce);
        $manager->flush();
        $this->addFlash('success', "La candidature a été refusée !");
        return $this->redirectToRoute('consultant.listeCandidature');
    }
}
