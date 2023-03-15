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

class UserController extends AbstractController
{
    #[Route('/utilisateur/edition/{id}', name: 'user.edit', methods: ['GET', 'POST'])]
    public function edit(User $user, Request $request, EntityManagerInterface $manager): Response
    {
        if(!$this->getUser()){
            return $this->redirectToRoute('security.login');
        }
        if($this->getUser() !== $user){
            return $this->redirectToRoute('app_home');
        }
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $file = $form->get('cv')->getData();
            if($file){
                $fileName = md5(uniqid()).'.'.$file->guessExtension();
                try {
                    $file->move($this->getParameter('upload_destination'),$fileName);
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }
                $user->setCv($fileName);
            }
            
            $manager->persist($user);
            $manager->flush();

            $this->addFlash('success', 'Votre compte a bien été modifié !');

            return $this->redirectToRoute('app_home');
        }
        return $this->render('pages/user/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
