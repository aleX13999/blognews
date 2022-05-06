<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\PostsRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
     * @Route("/api/randomTree", name = "randomTree")
     */
    public function randomTree(Request $request): Response
    {
        $numOfNodes = $request->query->get('numOfNodes');
        $maxDepth = $request->query->get('maxDepth');
        return $this->json($this->buildTree($numOfNodes, $maxDepth));
    }

    public function buildTree(&$numOfNodes, $maxDepth)
    {     
        $tree['value'] = rand(0, 100);      //новый узел
        $tree['nodes'] = [];    //под потомков

        if($numOfNodes == 0 || $maxDepth == 0)    //если создано достаточно узлов или достигнута глубина
        {
            return $tree;
        }

        $maxDepth--;    //уменьшаю оставшуюся глубину

        if($numOfNodes <= 5)    //если осталось меньше 5 узлов
            $rnd_nodes_col = rand(0, $numOfNodes);  //правой границей является оставшееся число узлов
        else        
            $rnd_nodes_col = rand(0, 5);  //случайное число новых узлов от 0 до 5

        $numOfNodes -= $rnd_nodes_col;    //отнимаем сгенерированное кол-во новых узлов

        $nodes = [];    //новый массив для потомков
        
        for($i=0; $i<$rnd_nodes_col; $i++)
            $nodes[] = $this->buildTree($numOfNodes, $maxDepth);    //кол-во узлов передается по ссылке

        $tree['nodes'] = $nodes;    //добавляю в созданный узел массив потомков
        
        return $tree;
    }

    /**
     * @Route("/api/findMax", name = "api_find_max")
     */
    public function findMax(Request $request): Response
    {
        $tree = $request->query->get('tree');
        $arr_tree = json_decode($tree);
        $maxValue = $arr_tree->value;

        $this->findInTree($arr_tree, $maxValue);

        return new Response($maxValue);
    }

    public function findInTree($tree, &$maxValue)
    {
        if($tree->value > $maxValue)    //если значение в данном узле больше чем найденное ранее
            $maxValue = $tree->value;   //новое максимальное значение

        $nodes_col = count($tree->nodes);   //сколько потомков в данном узле

        for($i=0;$i<$nodes_col;$i++)    //цикл по всем потомкам
        {
            $this->findInTree($tree->nodes[$i], $maxValue); //для каждого потомка вызываем рекурсию
        }
    }
}