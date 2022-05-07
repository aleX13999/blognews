<?php

namespace App\Controller;

use App\Controller\ApiController;
use App\Entity\Posts;
use App\Form\PostType;
use App\Repository\PostsRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Services\FileUploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="main")
     */
    public function main()
    {
        return $this->redirectToRoute('posts');
    }

    /**
     * @Route("/posts/{page}", name="posts")
     */
    public function showPosts($page=1, PostsRepository $postsRepository, ApiController $api): Response
    {
        $limit = 10;

        if($this->isGranted('IS_AUTHENTICATED_FULLY'))
            $posts = $postsRepository->getPostsToAdmin($page, $limit);        
        else
            $posts = json_decode($api->getPostsToPage($page, $limit, $postsRepository)->getContent()); 

        $maxPages = ceil($postsRepository->getCountPosts() / $limit);
        $thisPage = $page;

        if (!$posts) {
            throw $this->createNotFoundException(
                'Нет ни одной новости!'
            );
        }

        return $this->render('default/posts.html.twig', [
            'posts'=>$posts,
            'maxPages'=>$maxPages,
            'thisPage'=>$thisPage
        ]);        
    }

    /**
     * @Route("/posts/{page}/{id}", name = "post")
     */
    public function post($page=1, $id, PostsRepository $postsRepository, ApiController $api): Response
    {        
        $testApi = $api->getPost($id, $postsRepository);
        $post = json_decode($testApi->getContent());

        return $this->render('default/currentPost.html.twig', [
            'post' => $post,
            'thisPage' => $page,
        ]);
    }

    /**
     * @Route("/admin/add", name = "postAdd")
     */
    public function postAdd(Request $request, ManagerRegistry $doctrine, FileUploader $fileUploader): Response
    {
        $post = new Posts;

        $form = $this->createForm(PostType::class, $post);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $post = $form->getData();

            $imgFile = $form->get('imgFile')->getData();

            if ($imgFile)
                $post->setImg($fileUploader->upload($imgFile)); 

            $entityManager = $doctrine->getManager();
            // сообщите Doctrine, что вы хотите (в итоге) сохранить Продукт (пока без запросов)
            $entityManager->persist($post);
            // действительно выполните запросы (например, запрос INSERT)
            $entityManager->flush();

            return $this->redirectToRoute('posts');
        }

        return $this->renderForm('default/postAdd.html.twig', [
            'form' => $form,
        ]);
    }

    /**
     * @Route("/admin/update/{id}", name = "postUpdate")
     */
    public function postUpdate(Request $request, ManagerRegistry $doctrine, FileUploader $fileUploader, PostsRepository $postsRepository, $id): Response
    {
        $post = $postsRepository->find($id);
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $doctrine->getManager();
            $pp = $entityManager->getRepository(Posts::class)->find($id);

            $imgFile = $form->get('imgFile')->getData();

            if ($imgFile)
                $pp->setImg($fileUploader->upload($imgFile));              
            
            
            $pp->setHeader($form->get('header')->getData());
            $pp->setDate($form->get('date')->getData());
            $pp->setAnnotation($form->get('annotation')->getData());
            $pp->setAllText($form->get('allText')->getData());
            $entityManager->persist($pp);
            $entityManager->flush();
            return $this->redirectToRoute('posts');
        }

        return $this->renderForm('default/updatePost.html.twig', [
            'form'=>$form,
            'post'=>$post
        ]);
    }

    /**
     * @Route("/admin/delete/{id}", name = "postDelete")
     */
    public function postDelete($id, ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();
        $post = $entityManager->getRepository(Posts::class)->find($id);
        $entityManager->remove($post);
        $entityManager->flush();

        return $this->redirectToRoute('posts');
    }

    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
