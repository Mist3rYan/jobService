<?php

namespace App\Controller;

use App\Entity\Annonce;
use App\Form\AnnonceType;
use App\Repository\AnnonceRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class AnnonceController extends AbstractController
{

    #[Route('/consultant/annonce', name: 'consultant.annonce', methods: ['GET'])]
    public function annonce(AnnonceRepository $repositery, PaginatorInterface $paginator, Request $request): Response
    {
        $annonces = $paginator->paginate(
            $repositery->findBy(["isValid" => "0"]), /* query NOT result */
            $request->query->getInt('page',1), /*page number*/
            7 /*limit per page*/
        );
        return $this->render('pages/annonce/index.html.twig', [
            'annonces' => $annonces,
        ]);
    }

    #[Route('/annonce', name: 'annonce.index', methods: ['GET'])]
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

            $this->addFlash('success', 'Votre annnpnce a bien été créé. Elle sera visible après validation par un consultant !');

            return $this->redirectToRoute('annonce.index');
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

    #[IsGranted('ROLE_CONSULTANT')]
    #[Route('/consultant/annoncevalider/{id}', name: 'annonce.valid', methods: ['GET','POST'])]
    public function validAd(Annonce $annonce, EntityManagerInterface $manager): Response
    {
        if($annonce->getIsValid() == 0){
            $annonce->setIsValid(1);
            $manager->persist($annonce);
            $manager->flush();
            $this->addFlash('success', 'Votre annonce a bien été validée !');
            return $this->redirectToRoute('consultant.annonce');
        }
        return $this->render('pages/annonce/detail.html.twig', [
            'annonce' => $annonce,
        ]);
    }
}
