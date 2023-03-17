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
    #[Route('/annonce', name: 'annonce.index', methods: ['GET'])]
    public function index(AnnonceRepository $repositery, PaginatorInterface $paginator, Request $request): Response
    {
        $annonces = $paginator->paginate(
            $repositery->findAll(), /* query NOT result */
            $request->query->getInt('page',1), /*page number*/
            5 /*limit per page*/
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

            $manager->persist($annonce);
            $manager->flush();

            $this->addFlash('success', 'Votre annnpnce a bien été créé. Elle sera visible après validation par un consultant !');

            return $this->redirectToRoute('annonce.index');
        }

        return $this->render('pages/annonce/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
