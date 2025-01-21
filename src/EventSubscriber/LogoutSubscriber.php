<?php

namespace App\EventSubscriber;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LogoutEvent;

class LogoutSubscriber implements EventSubscriberInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LogoutEvent::class => ['onLogout', 0],
        ];
    }

    public function onLogout(LogoutEvent $event): void
    {
        $token = $event->getToken();
        if (!$token) {
            return;
        }

        $user = $token->getUser();
        if ($user instanceof User) {
            $user->setLastSeenAt(new \DateTimeImmutable());
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }
    }
} 