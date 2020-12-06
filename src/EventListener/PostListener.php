<?php
/**
 * Created by PhpStorm.
 * User: anton
 * Date: 06.12.20
 * Time: 13:36
 */

namespace App\EventListener;

use App\Events\PostCreatedEvent;


class PostListener
{
    public function onPostCreationAction(PostCreatedEvent $event) {
        $post = $event->getPost();
        echo $post->getTitle() . "\r\n";
        echo $post->getContent() . "\r\n";
    }
}