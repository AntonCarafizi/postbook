<?php

namespace App\Events;

use App\Entity\Post;
use Symfony\Contracts\EventDispatcher\Event;

class PostCreatedEvent extends Event
{
    const NAME = 'post.created';
    protected $post;

    public function __construct(Post $post)
    {
        $this->post = $post;
    }

    public function getPost(): Post
    {
        return $this->post;
    }
}