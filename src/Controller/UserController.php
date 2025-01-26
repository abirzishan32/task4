<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class UserController extends AbstractController
{
    private $tokenStorage;
    private $entityManager;

    public function __construct(TokenStorageInterface $tokenStorage, EntityManagerInterface $entityManager)
    {
        $this->tokenStorage = $tokenStorage;
        $this->entityManager = $entityManager;
    }

    #[Route('/user/block/{id}', name: 'app_user_block')]
    public function blockUser(User $user): Response
    {
        $currentUser = $this->getUser();
    

        $user->setBlocked(true);
        $this->entityManager->flush();

        // If user blocked themselves, log them out
        if ($currentUser === $user) {
            $this->tokenStorage->setToken(null);
            return $this->redirectToRoute('app_logout');
        }

        return $this->redirectToRoute('app_dashboard');
    }

    #[Route('/user/delete/{id}', name: 'app_user_delete')]
    public function deleteUser(User $user): Response
    {
        $currentUser = $this->getUser();

        $this->entityManager->remove($user);
        $this->entityManager->flush();

        // If user deleted themselves, log them out
        if ($currentUser === $user) {
            $this->tokenStorage->setToken(null);
            return $this->redirectToRoute('app_logout');
        }

        return $this->redirectToRoute('app_dashboard');
    }

    #[Route('/users/bulk-action', name: 'app_users_bulk_action', methods: ['POST'])]
    public function bulkAction(Request $request): JsonResponse
    {
        $action = $request->request->get('action');
        $userIds = $request->request->all('users');
        $currentUser = $this->getUser();
        $isCurrentUserAffected = false;

        if (empty($userIds)) {
            return new JsonResponse(['error' => 'No users selected'], 400);
        }

        $users = $this->entityManager->getRepository(User::class)->findBy(['id' => $userIds]);

        foreach ($users as $user) {
            if ($action === 'block') {
                $user->setBlocked(true);
                if ($currentUser === $user) {
                    $isCurrentUserAffected = true;
                }
            } elseif ($action === 'unblock') {
                $user->setBlocked(false);
            } elseif ($action === 'delete') {
                $this->entityManager->remove($user);
                if ($currentUser === $user) {
                    $isCurrentUserAffected = true;
                }
            }
        }

        $this->entityManager->flush();

        if ($isCurrentUserAffected) {
            $this->tokenStorage->setToken(null);
            return new JsonResponse([
                'message' => 'Operation successful. You will be logged out.',
                'logout' => true
            ]);
        }

        return new JsonResponse(['message' => 'Operation successful']);
    }
} 