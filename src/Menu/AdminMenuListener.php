<?php

/*
 * This file is part of Monsieur Biz's Sylius Messenger Admin Plugin for Sylius.
 * (c) Monsieur Biz <sylius@monsieurbiz.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MonsieurBiz\SyliusMessengerAdminPlugin\Menu;

use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: 'sylius.menu.admin.main')]
final class AdminMenuListener
{
    public function __invoke(MenuBuilderEvent $event): void
    {
        $menu = $event->getMenu();

        $configurationMenu = $menu
            ->getChild('configuration')
        ;

        if (null === $configurationMenu) {
            $configurationMenu = $menu
                ->addChild('configuration')
                ->setLabel('sylius.ui.configuration')
            ;
        }

        $configurationMenu->addChild('monsieurbiz_messenger_admin_messages', ['route' => 'monsieurbiz_messenger_admin_messages_index'])
            ->setLabel('monsieurbiz_messenger_admin.messenger.messenger_messages')
            ->setLabelAttribute('icon', 'stopwatch')
        ;
    }
}
