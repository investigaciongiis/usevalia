<?php

/*
 * Copyright (C) 2018 luis
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 */
namespace Drupal\usevalia\EventSubscriber;

use Drupal;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event subscriber subscribing to KernelEvents::REQUEST.
 */
class RedirectAnonymousSubscriber implements EventSubscriberInterface
{

    public function __construct()
    {
        $this->account = Drupal::currentUser();
    }

    public function checkAuthStatus(GetResponseEvent $event)
    {
        if ($this->account->isAnonymous() && Drupal::routeMatch()->getRouteName() != 'user.login' && Drupal::routeMatch()->getRouteName() != 'user.register' && Drupal::routeMatch()->getRouteName() != 'user.reset' && Drupal::routeMatch()->getRouteName() != 'user.reset.form' && Drupal::routeMatch()->getRouteName() != 'user.reset.login' && Drupal::routeMatch()->getRouteName() != 'user.pass' && Drupal::routeMatch()->getRouteName() != 'entity.node.canonical' && Drupal::routeMatch()->getRouteName() != 'usevalia') {
            // add logic to check other routes you want available to anonymous users,
            // otherwise, redirect to login page.
            $route_name = Drupal::routeMatch()->getRouteName();
            if (strpos($route_name, 'view') === 0 && strpos($route_name, 'rest_') !== FALSE) {
                return;
            }
            $response = new RedirectResponse(Url::fromRoute('usevalia')->toString(), 301);
            $event->setResponse($response);
            $event->stopPropagation();
        }
    }

    public static function getSubscribedEvents()
    {
        $events[KernelEvents::REQUEST][] = array(
            'checkAuthStatus'
        );
        return $events;
    }
}
