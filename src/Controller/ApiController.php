<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\PostsRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    // public function __construct(private PostsRepository $postsRepository)
    // {        
    // }
    
    /**
     * @Route("/api/posts/{page}", name = "api_get_all_posts", methods={"get"})
     */
    public function getPostsToPage($page=1, $limit= 10, PostsRepository $postsRepository): Response
    {
        return $this->json($postsRepository->getPostsToPage($page, $limit));
    }

    /**
     * @Route("/api/posts/{page}/{id}", name = "api_get_post", methods={"get"})
     */
    public function getPost($id, PostsRepository $postsRepository): Response
    {
        return $this->json($postsRepository->find($id));
    }

    /**
     * @param int $numOfNodes
     * @param int $maxDepth
     * 
     * @Route("/api/createTree", name = "createTree", methods={"POST", "GET"})
     */
    public function createTree($numOfNodes): Response
    {
        dd($numOfNodes);
        return new Response();
    }
}