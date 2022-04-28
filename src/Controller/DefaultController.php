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
     * @Route("/posts", name="posts")
     */
    public function showPosts(PostsRepository $postsRepository): Response
    {
        $posts = $postsRepository->findBy(array(), array('date'=>'DESC'));

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
     * @Route("/registration", name = "registration")
     */
    public function registration(): Response
    {
        return $this->render('default/registration.html.twig', [
            'controller_name' => 'DefaultController',
        ]);
    }

    /**
     * @Route("/registration", name = "registrationPost", methods={"POST"})
     */
    public function registrationAction(Request $request)//: Response
    {
        //dd($request);
        return $this->render('default/index.html.twig', [
            'controller_name' => 'DefaultController',
        ]);
    }

    /**
     * @Route("/admin/post/add", name = "postAdd")
     */
    public function postAdd(Request $request, ManagerRegistry $doctrine, SluggerInterface $slugger): Response
    {
        $post = new Posts;

        $form = $this->createForm(PostType::class, $post);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // $form->getData() holds the submitted values
            // но изначальная переменная `$task` также была обновлена
            //$post = $form->getData();

            $post->setHeader($form->get('header')->getData());
            $post->setImg($form->get('img')->getData());
            $post->setAnnotation($form->get('annotation')->getData());
            $post->setDate($form->get('date')->getData());
            $post->setFullText($form->get('fulltext')->getData());
           
            // $entityManager = $doctrine->getManager();

            // // сообщите Doctrine, что вы хотите (в итоге) сохранить Продукт (пока без запросов)
            // $entityManager->persist($post);
            // // действительно выполните запросы (например, запрос INSERT)
            // $entityManager->flush();
            // ... выполните какое-то действие, например сохраните задачу в базу данных
            // for example, if Task is a Doctrine entity, save it!
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($post);
            $entityManager->flush();

            $imgFile = $form->get('img')->getData();

            if ($imgFile) {
                $originalFilename = pathinfo($imgFile->getClientOriginalName(), PATHINFO_FILENAME);
                // это необходимо для безопасного включения имени файла в качестве части URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imgFile->guessExtension();

                // Переместите файлв каталог, где хранятся брошюры
                try {
                    $imgFile->move(
                        $this->getParameter('img_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... разберитесь с исключением, если что-то случится во время загрузки файла
                }

                // обновляет свойство 'brochureFilename' для сохранения имени PDF-файла,
                // а не его содержания
                $post->setImg($newFilename);
                
            }

            return $this->redirectToRoute('posts');
        }

        return $this->renderForm('default/postAdd.html.twig', [
            'form' => $form,
        ]);
    }

    /**
     * @Route("/admin/news/add/q", name = "postAddShow")
     */
    public function postAddShow(ManagerRegistry $doctrine): Response
    {        
        $news = new News();


        $entityManager = $doctrine->getManager();

        // сообщите Doctrine, что вы хотите (в итоге) сохранить Продукт (пока без запросов)
        $entityManager->persist($news);

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

    /**
     * @Route("/", name = "app_test", methods={"POST"})
     */
    public function test(Request $request)
    {
        dd($request);
    }





    /**
     * @Route("/test", name = "test")
     */
    public function testic(ManagerRegistry $doctrine): Response
    {
        $test = new Test;

        $test->setName('Firstname');
        $test->setImg('#');
        $test->setDate(new DateTime('now'));

        $entityManager = $doctrine->getManager();

        // сообщите Doctrine, что вы хотите (в итоге) сохранить Продукт (пока без запросов)
        $entityManager->persist($test);

        // действительно выполните запросы (например, запрос INSERT)
        $entityManager->flush();

        $test->setName('Firstname');

        return new Response('Saved new product with id '.$test->getId());
    }
}
