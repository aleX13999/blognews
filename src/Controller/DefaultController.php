<?php

namespace App\Controller;

use App\Entity\Posts;
use App\Repository\PostsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name = "app_default")
     */
    public function index(): Response
    {
        return $this->render('default/index.html.twig', [
            'controller_name' => 'DefaultController',
        ]);
    }

    /**
     * @Route("/posts", name="posts")
     */
    public function showPosts(PostsRepository $postsRepository): Response
    {
        $posts = $postsRepository
        ->findAll();


        if (!$posts) {
            throw $this->createNotFoundException(
                'No posts'
            );
        }

        return $this->render('default/posts.html.twig', [
            'posts'=>$posts,
        ]);        
    }

    /**
     * @Route("/admin/post/add", name = "postAdd")
     */
    public function postAdd(): Response
    {
        $post = new Posts();

        $post->setHeader('Вторая новость');
        
        return $this->render('default/index.html.twig', [
            'controller_name' => 'DefaultController',
        ]);
    }
}
