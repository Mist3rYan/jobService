<?php

namespace App\Controller;

use App\Repository\AnnonceRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
}
