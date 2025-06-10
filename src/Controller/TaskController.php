<?php

namespace App\Controller;

use App\Entity\Task;
use App\Entity\User;
use App\Form\TaskType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TaskController extends AbstractController
{
    #[Route('/{userId}/tasks', name: 'task_index')]
    public function index(int $userId, EntityManagerInterface $em): Response
    {
        $user = $em->getRepository(User::class)->find($userId);

        if (!$user) {
            throw $this->createNotFoundException('User not found.');
        }

        $tasks = $em->getRepository(Task::class)->findBy(['user' => $user]);

        return $this->render('task/index.html.twig', [
            'tasks' => $tasks,
            'userId' => $userId, // Pass userId to the template
        ]);
    }

    #[Route('/{userId}/task/new', name: 'task_new')]
public function new(int $userId, Request $request, EntityManagerInterface $em): Response
{
    $user = $em->getRepository(User::class)->find($userId);

    if (!$user) {
        throw $this->createNotFoundException('User not found.');
    }

    $task = new Task();
    $task->setUser($user);

    $form = $this->createForm(TaskType::class, $task);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $em->persist($task);
        $em->flush();
        return $this->redirectToRoute('task_index', ['userId' => $userId]);
    }

    // Make sure to pass the userId here to the template
    return $this->render('task/new.html.twig', [
        'form' => $form->createView(),
        'userId' => $userId, // Passing userId to the template
    ]);
}


    #[Route('/{userId}/task/{id}/toggle', name: 'task_toggle')]
    public function toggleTask(int $userId, Task $task, EntityManagerInterface $em): Response
    {
        $user = $em->getRepository(User::class)->find($userId);

        if (!$user) {
            throw $this->createNotFoundException('User not found.');
        }

        if ($task->getUser() !== $user) {
            throw $this->createAccessDeniedException('This task does not belong to the specified user.');
        }

        $task->setIsDone(!$task->getIsDone());
        $em->flush();

        return $this->redirectToRoute('task_index', ['userId' => $userId]);
    }

    #[Route('/{userId}/task/{id}', name: 'task_show')]
public function show(int $userId, Task $task, EntityManagerInterface $em): Response
{
    $user = $em->getRepository(User::class)->find($userId);

    if (!$user) {
        throw $this->createNotFoundException('User not found.');
    }

    if ($task->getUser() !== $user) {
        throw $this->createAccessDeniedException('This task does not belong to the specified user.');
    }

    return $this->render('task/show.html.twig', [
        'task' => $task,
        'userId' => $userId,
    ]);
}


#[Route('/{userId}/task/{id}/edit', name: 'task_edit')]
public function edit(int $userId, Task $task, Request $request, EntityManagerInterface $em): Response
{
    $user = $em->getRepository(User::class)->find($userId);

    if (!$user) {
        throw $this->createNotFoundException('User not found.');
    }

    if ($task->getUser() !== $user) {
        throw $this->createAccessDeniedException('This task does not belong to the specified user.');
    }

    $form = $this->createForm(TaskType::class, $task);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $em->flush();
        return $this->redirectToRoute('task_index', ['userId' => $userId]);
    }

    return $this->render('task/edit.html.twig', [
        'form' => $form->createView(),
        'task' => $task,
        'userId' => $userId,
    ]);
}


#[Route('/{userId}/task/{id}/delete', name: 'task_delete', methods: ['POST'])]
public function delete(int $userId, Task $task, Request $request, EntityManagerInterface $em): Response
{
    $user = $em->getRepository(User::class)->find($userId);

    if (!$user) {
        throw $this->createNotFoundException('User not found.');
    }

    if ($task->getUser() !== $user) {
        throw $this->createAccessDeniedException('This task does not belong to the specified user.');
    }

    // Security check to ensure the form was submitted correctly
    if ($this->isCsrfTokenValid('delete'.$task->getId(), $request->request->get('_token'))) {
        $em->remove($task);
        $em->flush();
    }

    return $this->redirectToRoute('task_index', ['userId' => $userId]);
}

}
