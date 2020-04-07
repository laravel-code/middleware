<?php

namespace TestApp\Listeners;

use LaravelCode\Middleware\Listeners\CrudListener;
use TestApp\Events\BlogDestroyEvent;
use TestApp\Events\BlogRestoreEvent;

class BlogRestoreListener extends CrudListener
{
    /**
     * @var BlogRestoreEvent
     */
    protected $event;

    /**
     * @param BlogRestoreEvent $event
     */
    public function handle(BlogRestoreEvent $event)
    {
        $this->setRestore(true);
        $this->process($event);
    }
}
