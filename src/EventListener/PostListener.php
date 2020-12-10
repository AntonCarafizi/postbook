<?php

namespace App\EventListener;

use App\Entity\Post;
use Doctrine\Persistence\Event\LifecycleEventArgs;


class PostListener
{
    public function postPersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        // if this listener only applies to certain entity types,
        // add some code to check the entity type as early as possible
        if (!$entity instanceof Post) {
            //return $entity;
        }

        $entityManager = $args->getObjectManager();
        //var_dump($entity->getTitle()); die();
        // ... do something with the Product entity
    }
}