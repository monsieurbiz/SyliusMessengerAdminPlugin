<?php

/*
 * This file is part of Monsieur Biz's Sylius Messenger Admin Plugin for Sylius.
 * (c) Monsieur Biz <sylius@monsieurbiz.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MonsieurBiz\SyliusMessengerAdminPlugin\Controller;

use Exception;
use Sylius\Component\Grid\Parameters;
use Sylius\Component\Grid\Provider\GridProviderInterface;
use Sylius\Component\Grid\View\GridViewFactoryInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MessageController extends AbstractController
{
    public function index(
        Request $request,
        GridProviderInterface $gridProvider,
        GridViewFactoryInterface $gridViewFactory
    ): Response {
        $gridView = $gridViewFactory->create(
            $gridProvider->get('monsieurbiz_messenger_admin_messages'),
            new Parameters($request->query->all())
        );

        return $this->render('@MonsieurBizSyliusMessengerAdminPlugin/Admin/Messages/index.html.twig', [
            'resources' => $gridView,
        ]);
    }

    public function show(
        Request $request,
        KernelInterface $kernel
    ): Response {
        $queueName = $request->get('queueName');
        $id = $request->get('id');

        if (empty($queueName) || empty($id)) {
            throw new NotFoundHttpException();
        }

        $application = new Application($kernel);
        $application->setAutoExit(false);
        $application->setCatchExceptions(false);

        $input = new ArrayInput([
            'command' => 'messenger:failed:show',
            'id' => $id,
            '--transport' => $queueName,
            '-vv',
        ]);

        $output = new BufferedOutput();

        try {
            $application->run($input, $output);
        } catch (Exception $exception) {
            $this->addFlash('error', $exception->getMessage());

            return new RedirectResponse((string) $request->headers->get('referer'));
        }

        $content = $output->fetch();

        return $this->render('@MonsieurBizSyliusMessengerAdminPlugin/Admin/Messages/show.html.twig', [
            'content' => $content,
            'resourceId' => $id,
            'queueName' => $queueName,
        ]);
    }

    public function retry(
        Request $request,
        KernelInterface $kernel,
        UrlGeneratorInterface $urlGenerator
    ): Response {
        $queueName = $request->get('queueName');
        $id = $request->get('id');

        if (empty($queueName) || empty($id)) {
            throw new NotFoundHttpException();
        }

        $application = new Application($kernel);
        $application->setAutoExit(false);
        $application->setCatchExceptions(false);

        $input = new ArrayInput([
            'command' => 'messenger:failed:retry',
            'id' => [$id],
            '--transport' => $queueName,
            '--force' => true,
        ]);

        $output = new BufferedOutput();

        try {
            $application->run($input, $output);
        } catch (Exception $exception) {
            $this->addFlash('error', $exception->getMessage());

            return new RedirectResponse((string) $request->headers->get('referer'));
        }

        $content = $output->fetch();

        $this->addFlash('success', '<pre>' . $content . '</pre>');

        return new RedirectResponse($urlGenerator->generate('monsieurbiz_messenger_admin_messages_index'));
    }

    public function remove(
        Request $request,
        KernelInterface $kernel,
        UrlGeneratorInterface $urlGenerator
    ): Response {
        $queueName = $request->get('queueName');
        $id = $request->get('id');

        if (empty($queueName) || empty($id)) {
            throw new NotFoundHttpException();
        }

        $application = new Application($kernel);
        $application->setAutoExit(false);
        $application->setCatchExceptions(false);

        $input = new ArrayInput([
            'command' => 'messenger:failed:remove',
            'id' => $id,
            '--transport' => $queueName,
            '--force' => true,
        ]);

        $output = new BufferedOutput();

        try {
            $application->run($input, $output);
        } catch (Exception $exception) {
            $this->addFlash('error', $exception->getMessage());

            return new RedirectResponse((string) $request->headers->get('referer'));
        }

        $content = $output->fetch();

        $this->addFlash('success', '<pre>' . $content . '</pre>');

        return new RedirectResponse($urlGenerator->generate('monsieurbiz_messenger_admin_messages_index'));
    }
}
