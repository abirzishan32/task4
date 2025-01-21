<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('/', name: 'app_dashboard')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $users = $entityManager->getRepository(User::class)->findBy([], ['lastLoginAt' => 'DESC']);

        return $this->render('dashboard/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/users/bulk-action', name: 'app_users_bulk_action', methods: ['POST'])]
    public function bulkAction(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            $action = $request->request->get('action');
            $userIds = $request->request->all('users');

            if (empty($userIds)) {
                return new JsonResponse(['error' => 'No users selected'], Response::HTTP_BAD_REQUEST);
            }

            $users = $entityManager->getRepository(User::class)->findBy(['id' => $userIds]);
            
            if (empty($users)) {
                return new JsonResponse(['error' => 'No valid users found'], Response::HTTP_BAD_REQUEST);
            }

            foreach ($users as $user) {
                switch ($action) {
                    case 'block':
                        $user->setIsBlocked(true);
                        break;
                    case 'unblock':
                        $user->setIsBlocked(false);
                        break;
                    case 'delete':
                        $entityManager->remove($user);
                        break;
                    default:
                        return new JsonResponse(['error' => 'Invalid action'], Response::HTTP_BAD_REQUEST);
                }
            }

            $entityManager->flush();

            $message = match ($action) {
                'block' => 'Users have been blocked successfully',
                'unblock' => 'Users have been unblocked successfully',
                'delete' => 'Users have been deleted successfully',
                default => 'Action completed successfully'
            };

            $this->addFlash('success', $message);
            return new JsonResponse(['message' => $message]);

        } catch (\Exception $e) {
            return new JsonResponse(
                ['error' => 'An error occurred while processing your request'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
} 