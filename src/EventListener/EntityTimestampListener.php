<?php

namespace App\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Bundle\SecurityBundle\Security;
use App\Entity\Traits\Timestampable;
use App\Entity\Traits\Blameable;

class EntityTimestampListener
{
    public function __construct(private Security $security) {}

    public function prePersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof Timestampable) {
            $entity->setCreatedAt(new \DateTimeImmutable());
        }

        if ($entity instanceof Blameable) {
            $entity->setCreatedBy($this->security->getUser());
        }
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof Timestampable) {
            $entity->setUpdatedAt(new \DateTimeImmutable());
        }

        if ($entity instanceof Blameable) {
            $entity->setUpdatedBy($this->security->getUser());
        }
    }
}
