<?php

/*
 * This file is part of the PHP Translation package.
 *
 * (c) PHP Translation team <tobias.nyholm@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Translation\Bundle\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Profiler\Profiler;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\DataCollector\TranslationDataCollector;
use Symfony\Component\Translation\DataCollectorTranslator;
use Symfony\Component\VarDumper\Cloner\Data;
use Translation\Bundle\Model\SfProfilerMessage;
use Translation\Bundle\Service\StorageService;
use Translation\Common\Model\MessageInterface;
use Twig\Environment;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class SymfonyProfilerController
{
    /**
     * @var Profiler An optional dependency
     */
    private $profiler;
    private $storage;
    private $twig;
    private $router;
    private $isToolbarAllowEdit;

    public function __construct(StorageService $storage, Environment $twig, RouterInterface $router, bool $isToolbarAllowEdit)
    {
        $this->storage = $storage;
        $this->twig = $twig;
        $this->router = $router;
        $this->isToolbarAllowEdit = $isToolbarAllowEdit;
    }

    public function editAction(Request $request, string $token): Response
    {
        if (!$this->isToolbarAllowEdit) {
            return new Response('You are not allowed to edit the translations.');
        }

        if (!$request->isXmlHttpRequest()) {
            return $this->redirectToProfiler($token);
        }

        $message = $this->getMessage($request, $token);

        if ($request->isMethod('GET')) {
            $translation = $this->storage->syncAndFetchMessage($message->getLocale(), $message->getDomain(), $message->getKey());

            $content = $this->twig->render('@Translation/SymfonyProfiler/edit.html.twig', [
                'message' => $translation,
                'key' => $request->query->get('message_id'),
            ]);

            return new Response($content);
        }

        // Assert: This is a POST request
        $message->setTranslation((string) $request->request->get('translation'));
        $this->storage->update($message->convertToMessage());

        return new Response($message->getTranslation());
    }

    public function syncAction(Request $request, string $token): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->redirectToProfiler($token);
        }

        $sfMessage = $this->getMessage($request, $token);
        $message = $this->storage->syncAndFetchMessage($sfMessage->getLocale(), $sfMessage->getDomain(), $sfMessage->getKey());

        if (null !== $message) {
            return new Response($message->getTranslation());
        }

        return new Response('Asset not found', 404);
    }

    /**
     * @return RedirectResponse|Response
     */
    public function syncAllAction(Request $request, string $token): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->redirectToProfiler($token);
        }

        $this->storage->sync();

        return new Response('Started synchronization of all translations');
    }

    /**
     * Save the selected translation to resources.
     *
     * @author Damien Alexandre (damienalexandre)
     */
    public function createAssetsAction(Request $request, string $token): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->redirectToProfiler($token);
        }

        $messages = $this->getSelectedMessages($request, $token);
        if (empty($messages)) {
            return new Response('No translations selected.');
        }

        $uploaded = [];
        foreach ($messages as $message) {
            $this->storage->create($message);
            $uploaded[] = $message;
        }

        return new Response(\sprintf('%s new assets created!', \count($uploaded)));
    }

    private function getMessage(Request $request, string $token): SfProfilerMessage
    {
        $this->getProfiler()->disable();

        $messageId = (string) $request->request->get('message_id', $request->query->get('message_id'));

        $collectorMessages = $this->getMessages($token);

        if (!isset($collectorMessages[$messageId])) {
            throw new NotFoundHttpException(\sprintf('No message with key "%s" was found.', $messageId));
        }
        $message = SfProfilerMessage::create($collectorMessages[$messageId]);

        if (DataCollectorTranslator::MESSAGE_EQUALS_FALLBACK === $message->getState()) {
            /** @var \Symfony\Component\HttpKernel\DataCollector\RequestDataCollector */
            $requestCollector = $this->getProfiler()->loadProfile($token)->getCollector('request');

            $message
                ->setLocale($requestCollector->getLocale())
                ->setTranslation(\sprintf('[%s]', $message->getTranslation()))
            ;
        }

        return $message;
    }

    /**
     * @return MessageInterface[]
     */
    protected function getSelectedMessages(Request $request, string $token): array
    {
        $this->getProfiler()->disable();

        $parameters = $request->request->all();
        if (!isset($parameters['selected'])) {
            return [];
        }
        /** @var string[] $selected */
        $selected = (array) $parameters['selected'];
        if (0 === \count($selected)) {
            return [];
        }

        $toSave = array_intersect_key($this->getMessages($token), array_flip($selected));

        $messages = [];
        foreach ($toSave as $data) {
            $messages[] = SfProfilerMessage::create($data)->convertToMessage();
        }

        return $messages;
    }

    private function getMessages(string $token, string $profileName = 'translation'): array
    {
        $profile = $this->getProfiler()->loadProfile($token);

        if (null === $dataCollector = $profile->getCollector($profileName)) {
            throw new NotFoundHttpException("No collector with name \"$profileName\" was found.");
        }
        if (!$dataCollector instanceof TranslationDataCollector) {
            throw new NotFoundHttpException("Collector with name \"$profileName\" is not an instance of TranslationDataCollector.");
        }

        $messages = $dataCollector->getMessages();

        if (class_exists(Data::class) && $messages instanceof Data) {
            return $messages->getValue(true);
        }

        return $messages;
    }

    public function setProfiler(Profiler $profiler): void
    {
        $this->profiler = $profiler;
    }

    private function getProfiler(): Profiler
    {
        if (!$this->profiler) {
            throw new \Exception('The "profiler" service is missing. Please, run "composer require symfony/web-profiler-bundle" first to use this feature.');
        }

        return $this->profiler;
    }

    private function redirectToProfiler(string $token): RedirectResponse
    {
        try {
            $targetUrl = $this->router->generate('_profiler', ['token' => $token]);

            return new RedirectResponse($targetUrl);
        } catch (RouteNotFoundException $e) {
            throw new \Exception('Route to profiler page not found. Please, run "composer require symfony/web-profiler-bundle" first to use this feature.');
        }
    }
}
