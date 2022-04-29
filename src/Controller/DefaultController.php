<?php

namespace App\Controller;

use App\Entity\Posts;
use App\Entity\News;
use App\Entity\Test;
use App\Entity\User;
use App\Form\UserType;
use App\Form\PostType;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Repository\PostsRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

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
     * @Route("/{page}/posts", name="posts")
     */
    public function showPosts($page=1, PostsRepository $postsRepository): Response
    {
        $limit = 3;
        $posts = $postsRepository->getAllPosts($page, $limit);
        $maxPages = ceil($posts->count() / $limit);
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
     * @Route("/posts/{id}", name = "post")
     */
    public function post(PostsRepository $postsRepository, $id): Response
    {
        $post = $postsRepository->find($id);
        return $this->render('default/currentPost.html.twig', [
            'post' => $post,
        ]);
    }

    /**
     * @Route("/admin/add", name = "postAdd")
     */
    public function postAdd(Request $request, ManagerRegistry $doctrine, SluggerInterface $slugger): Response
    {
        $post = new Posts;

        $form = $this->createForm(PostType::class, $post);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $post = $form->getData();

            $imgFile = $form->get('img')->getData();

            if ($imgFile) {
                $originalFilename = pathinfo($imgFile->getClientOriginalName(), PATHINFO_FILENAME);
                // это необходимо для безопасного включения имени файла в качестве части URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imgFile->guessExtension();

                try {
                    $imgFile->move(
                        $this->getParameter('img_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    
                }
                $post->setImg($newFilename);                
            }
           
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
    public function postUpdate(Request $request, ManagerRegistry $doctrine, SluggerInterface $slugger, PostsRepository $postsRepository, $id): Response
    {
        $post = $postsRepository->find($id);
        
        $p = new Posts;

        $form = $this->createForm(PostType::class, $p);

        $form->handleRequest($request);

        //dd($post);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $doctrine->getManager();
            $pp = $entityManager->getRepository(Posts::class)->find($id);
            
            $pp->setImg($form->get('img')->getData());
            $pp->setAnnotation($form->get('annotation')->getData());
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

    /**
     * @Route("/admin/news/add/q", name = "postAddShow")
     */
    public function postAddShow(ManagerRegistry $doctrine): Response
    {        
        $post = new Posts();
        $post->setHeader('Заголовок');
        $post->setAnnotation('Аннотация');
        $post->setDate(new DateTime('now'));
        $post->setImg('#');
        $post->setAllText('Полный текст');
        $entityManager = $doctrine->getManager();
        // сообщите Doctrine, что вы хотите (в итоге) сохранить Продукт (пока без запросов)
        $entityManager->persist($post);
        // действительно выполните запросы (например, запрос INSERT)
        $entityManager->flush();
        return $this->render('default/index.html.twig');
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
