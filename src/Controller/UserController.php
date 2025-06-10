<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UserController extends AbstractController
{
    #[Route('/signup', name: 'user_signup')]
    public function signup(Request $request, EntityManagerInterface $em): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $em->persist($user);
            $em->flush();
            return $this->redirectToRoute('user_login');
        }

        return $this->render('user/signup.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/login', name:'user_login')]
    public function login(Request $request, EntityManagerInterface $em): Response
    {
        if($request->isMethod('POST')){
            $username = $request->request->get('username');
            $password = $request->request->get('password');

            $user = $em->getRepository(User::class)->findOneBy([
                'username' => $username,
                'password' => $password,
            ]);

            if($user){
                $this->addFlash('success', 'Logged in successfully!');
                return $this->redirectToRoute('task_index', ['userId' => $user->getId()]);
            } else {
                $this->addFlash('error', 'Invalid credentials!');
            }
        }

        return $this->render('user/login.html.twig');
    }

    #[Route('/logout', name: 'app_logout')]
public function logout(Request $request): Response
{
    // Destroy the session to log the user out
    $request->getSession()->invalidate();
    
    // Redirect the user to the login page after logout
    return $this->redirectToRoute('user_login');
}
}
