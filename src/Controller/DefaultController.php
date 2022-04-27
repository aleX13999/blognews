<?php

namespace App\Controller;

use App\Entity\Posts;
use App\Entity\User;
use App\Form\UserType;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Repository\PostsRepository;
use Doctrine\Persistence\ManagerRegistry;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\BrowserKit\Request;
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
        //dd($post);
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
    public function postAdd(): Response
    {
        return $this->render('default/postAdd.html.twig');
    }

    /**
     * @Route("/admin/post/add/q", name = "postAddShow")
     */
    public function postAddShow(ManagerRegistry $doctrine): Response
    {        
        $post = new Posts();

        $post->setHeader('Третья новость');
        $post->setImg('#');
        $post->setAnnotation('Аннотация третьей новости');
        $post->setDate(new DateTime('now'));
        $post->setFullText('Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem. Nulla consequat massa quis enim. Donec pede justo, fringilla vel, aliquet nec, vulputate eget, arcu. In enim justo, rhoncus ut, imperdiet a, venenatis vitae, justo. Nullam dictum felis eu pede mollis pretium. Integer tincidunt. Cras dapibus. Vivamus elementum semper nisi. Aenean vulputate eleifend tellus. Aenean leo ligula, porttitor eu, consequat vitae, eleifend ac, enim. Aliquam lorem ante, dapibus in, viverra quis, feugiat a, tellus.');

        $entityManager = $doctrine->getManager();

        // сообщите Doctrine, что вы хотите (в итоге) сохранить Продукт (пока без запросов)
        $entityManager->persist($post);

        // действительно выполните запросы (например, запрос INSERT)
        $entityManager->flush();

        return $this->render('default/index.html.twig', [
            'controller_name' => 'DefaultController',
        ]);
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
