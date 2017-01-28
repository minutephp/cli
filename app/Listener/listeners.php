<?php

/** @var Binding $binding */
use Minute\Component\CmsComponent;
use Minute\Event\AdminEvent;
use Minute\Event\Binding;
use Minute\Event\CmsEvent;
use Minute\Event\MemberEvent;
use Minute\Event\RouterEvent;
use Minute\Event\UserPaymentEvent;
use Minute\Event\UserSignupEvent;
use Minute\Menu\CmsMenu;
use Minute\Router\CliRouter;
use Minute\Router\CmsRouter;
use Minute\Theme\CmsTheme;
use Minute\Track\PageTracker;

$binding->addMultiple([
    //router
    ['event' => RouterEvent::ROUTER_GET_FALLBACK_RESOURCE, 'handler' => [CliRouter::class, 'handle'], 'priority' => 100],
]);