<?php

namespace App\Controller;

use App\Entity\Annonce;
use App\Form\AnnonceType;
use App\Repository\AnnonceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AnnonceController extends AbstractController
{
    #[IsGranted('ROLE_CONSULTANT')]
    #[Route('/consultant/annoncevalider/{id}', name: 'consultant.validAd', methods: ['GET','POST'])]
    public function validAd(Annonce $annonce, EntityManagerInterface $manager): Response
    {
        if($annonce->getIsValid() == 0){
            $annonce->setIsValid(1);
            $manager->persist($annonce);
            $manager->flush();
            $this->addFlash('success', 'Votre annonce a bien été validée !');
            return $this->redirectToRoute('consultant.listeAd');
        }
        return $this->render('pages/consultant/detailAd.html.twig', [
            'annonce' => $annonce,
        ]);
    }

    #[IsGranted('ROLE_CONSULTANT')]
    #[Route('/consultant/annonce', name: 'consultant.listeAd', methods: ['GET', 'POST'])]
    public function listeAd(AnnonceRepository $repositery, PaginatorInterface $paginator, Request $request): Response
    {
        $annonces = $paginator->paginate(
            $repositery->findBy(["isValid" => "0"]), /* query NOT result */
            $request->query->getInt('page',1), /*page number*/
            7 /*limit per page*/
        );
        return $this->render('pages/consultant/listeAd.html.twig', [
            'annonces' => $annonces,
        ]);
    }

    #[IsGranted('ROLE_CONSULTANT')]
    #[Route('/consultant/detail/{id}', name: 'consultant.detailAd', methods: ['GET', 'POST'])]
    public function detailAd(Annonce $annonce): Response
    {
        return $this->render('pages/consultant/detailAd.html.twig', [
            'annonce' => $annonce,
        ]);
    }

    #[Route('/annonces', name: 'annonce.index', methods: ['GET'])]
    public function index(AnnonceRepository $repositery, PaginatorInterface $paginator, Request $request): Response
    {
        $annonces = $paginator->paginate(
            $repositery->findBy(["isValid" => "1"]), /* query NOT result */
            $request->query->getInt('page',1), /*page number*/
            7 /*limit per page*/
        );
        return $this->render('pages/annonce/index.html.twig', [
            'annonces' => $annonces,
        ]);
    }
    
    #[Route('/annonce/offre/{id}', name: 'annonce.detail', methods: ['GET', 'POST'])]
    public function detail(Annonce $annonce): Response
    {
        return $this->render('pages/annonce/detail.html.twig', [
            'annonce' => $annonce,
        ]);
    }

    #[IsGranted('ROLE_RECRUTEUR')]
    #[Route('/annonce/creation', name: 'annonce.create', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $manager) : Response
    {
        $annonce = new Annonce();
        $form = $this->createForm(AnnonceType::class, $annonce);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $annonce = $form->getData();
            $annonce->setRecruteur($this->getUser());

            $manager->persist($annonce);
            $manager->flush();

            $this->addFlash('success', 'Votre annnonce a bien été créé. Elle sera visible après validation par un consultant !');

            return $this->redirectToRoute('annonce.liste');
        }

        return $this->render('pages/annonce/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    
    #[IsGranted('ROLE_RECRUTEUR')]
    #[Route('/annonce/mesannonces', name: 'annonce.liste', methods: ['GET'])]
    public function liste(AnnonceRepository $repositery, PaginatorInterface $paginator, Request $request): Response
    {
        $annonces = $paginator->paginate(
            $repositery->findBy(['recruteur'=> $this->getUser()]), /* query NOT result */
            $request->query->getInt('page',1), /*page number*/
            7 /*limit per page*/
        );
        return $this->render('pages/annonce/liste.html.twig', [
            'annonces' => $annonces,
        ]);
    }

    #[IsGranted('ROLE_RECRUTEUR')]
    #[Route('/annonce/mesannonces/delete/{id}', name: 'annonce.delete', methods: ['GET'])]
    public function delete(Annonce $annonce, ManagerRegistry $doctrine): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY'); // on verifie que l'utilisateur est connecté
        if($this->getUser() !== $annonce->getRecruteur()) {// on verifie que l'utilisateur connecté est bien l'auteur du post
            $this->addFlash(
               'danger',
               'Vous ne pouvez pas supprimer un post qui ne vous appartient pas !'
            );
            return $this->redirectToRoute('app_home');// si ce n'est pas le cas on redirige vers la page d'accueil
        }
        $em = $doctrine->getManager();
        $em->remove($annonce); // on supprime l'objet $post
        $em->flush();
        $this->addFlash(
            'danger',
            'Votre annonce a été supprimée !'
         );
        //redirection vers la page d'accueil
        return $this->redirectToRoute('annonce.liste');
    }

    #[IsGranted('ROLE_RECRUTEUR')]
    #[Route('/annonce/edition/{id}', name: 'annonce.edit', methods: ['GET', 'POST'])]
    public function edit(Annonce $annonce, Request $request, EntityManagerInterface $manager): Response
    {
        $form = $this->createForm(AnnonceType::class, $annonce);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
                $manager->persist($annonce);
                $manager->flush();
                $this->addFlash('success', 'Votre annonce a bien été modifié !');
                return $this->redirectToRoute('annonce.liste');
        }
        return $this->render('pages/annonce/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
