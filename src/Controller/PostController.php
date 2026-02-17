<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/post')]
#[IsGranted('ROLE_USER')] // Sécurité globale : il faut être connecté pour accéder au CRUD
class PostController extends AbstractController
{
    #[Route('/', name: 'app_post_index', methods: ['GET'])]
    public function index(PostRepository $postRepository): Response
    {
        // On ne récupère que les articles de l'utilisateur connecté
        return $this->render('post/index.html.twig', [
            'posts' => $postRepository->findBy(['author' => $this->getUser()]),
        ]);
    }

    #[Route('/new', name: 'app_post_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $post = new Post();
        
        // IMPORTANT : On définit l'auteur avant de créer le formulaire
        $post->setAuthor($this->getUser());

        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($post);
            $entityManager->flush();

            return $this->redirectToRoute('app_post_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('post/new.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_post_show', methods: ['GET'])]
    public function show(Post $post): Response
    {
        // Sécurité : Vérifie que l'utilisateur est bien l'auteur
        if ($post->getAuthor() !== $this->getUser()) {
            throw $this->createAccessDeniedException("Vous n'avez pas le droit de voir cet article privé.");
        }

        return $this->render('post/show.html.twig', [
            'post' => $post,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_post_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {
        // Sécurité : Empêche de modifier le blog d'un autre
        if ($post->getAuthor() !== $this->getUser()) {
            throw $this->createAccessDeniedException("Ce n'est pas votre article !");
        }

        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_post_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('post/edit.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_post_delete', methods: ['POST'])]
    public function delete(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {
        // Sécurité : Empêche de supprimer le blog d'un autre
        if ($post->getAuthor() !== $this->getUser()) {
            throw $this->createAccessDeniedException("Suppression interdite !");
        }

        if ($this->isCsrfTokenValid('delete'.$post->getId(), $request->request->get('_token'))) {
            $entityManager->remove($post);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_post_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/share/post/{id}', name: 'app_post_share', methods: ['GET'])]
    #[IsGranted('PUBLIC_ACCESS')] // Force l'accès public pour le partage
    public function share(Post $post): Response
    {
        return $this->render('post/share.html.twig', [
            'post' => $post,
        ]);
    }
}