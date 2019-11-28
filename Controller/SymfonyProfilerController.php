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

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Profiler\Profiler;
use Symfony\Component\Translation\DataCollectorTranslator;
use Symfony\Component\VarDumper\Cloner\Data;
use Translation\Bundle\Model\SfProfilerMessage;
use Translation\Bundle\Service\StorageService;
use Translation\Common\Model\MessageInterface;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class SymfonyProfilerController extends AbstractController
{
    /**
     * @var StorageService
     */
    private $storageService;

    /**
     * @var Profiler
     */
    private $profiler;

    public function __construct(StorageService $storageService, Profiler $profiler)
    {
        $this->storageService = $storageService;
        $this->profiler = $profiler;
    }

    public function editAction(Request $request, string $token): Response
    {
        if (!$this->getParameter('php_translation.toolbar.allow_edit')) {
            return new Response('You are not allowed to edit the translations.');
        }

        if (!$request->isXmlHttpRequest()) {
            return $this->redirectToRoute('_profiler', ['token' => $token]);
        }

        $message = $this->getMessage($request, $token);

        if ($request->isMethod('GET')) {
            $translation = $this->storageService->syncAndFetchMessage($message->getLocale(), $message->getDomain(), $message->getKey());

            return $this->render('@Translation/SymfonyProfiler/edit.html.twig', [
                'message' => $translation,
                'key' => $request->query->get('message_id'),
            ]);
        }

        //Assert: This is a POST request
        $message->setTranslation($request->request->get('translation'));
        $this->storageService->update($message->convertToMessage());

        return new Response($message->getTranslation());
    }

    public function syncAction(Request $request, string $token): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->redirectToRoute('_profiler', ['token' => $token]);
        }

        $sfMessage = $this->getMessage($request, $token);
        $message = $this->storageService->syncAndFetchMessage($sfMessage->getLocale(), $sfMessage->getDomain(), $sfMessage->getKey());

        if (null !== $message) {
            return new Response($message->getTranslation());
        }

        return new Response('Asset not found', 404);
    }

    public function syncAllAction(Request $request, string $token): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->redirectToRoute('_profiler', ['token' => $token]);
        }

        $this->storageService->sync();

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
            return $this->redirectToRoute('_profiler', ['token' => $token]);
        }

        $messages = $this->getSelectedMessages($request, $token);
        if (empty($messages)) {
            return new Response('No translations selected.');
        }

        $uploaded = [];

        foreach ($messages as $message) {
            $this->storageService->create($message);
            $uploaded[] = $message;
        }

        return new Response(\sprintf('%s new assets created!', \count($uploaded)));
    }

    private function getMessage(Request $request, string $token): SfProfilerMessage
    {
        $this->profiler->disable();

        $messageId = $request->request->get('message_id', $request->query->get('message_id'));

        $profile = $this->profiler->loadProfile($token);

        if (null === $dataCollector = $profile->getCollector('translation')) {
            throw $this->createNotFoundException('No collector with name "translation" was found.');
        }

        $collectorMessages = $dataCollector->getMessages();

        if ($collectorMessages instanceof Data) {
            $collectorMessages = $collectorMessages->getValue(true);
        }

        if (!isset($collectorMessages[$messageId])) {
            throw $this->createNotFoundException(\sprintf('No message with key "%s" was found.', $messageId));
        }

        $message = SfProfilerMessage::create($collectorMessages[$messageId]);

        if (DataCollectorTranslator::MESSAGE_EQUALS_FALLBACK === $message->getState()) {
            $message->setLocale($profile->getCollector('request')->getLocale())
                ->setTranslation(\sprintf('[%s]', $message->getTranslation()));
        }

        return $message;
    }

    /**
     * @return MessageInterface[]
     */
    protected function getSelectedMessages(Request $request, string $token): array
    {
        $this->profiler->disable();

        $selected = $request->request->get('selected');
        if (!$selected || 0 == \count($selected)) {
            return [];
        }

        $profile = $this->profiler->loadProfile($token);
        $dataCollector = $profile->getCollector('translation');
        $messages = $dataCollector->getMessages();

        if ($messages instanceof Data) {
            $messages = $messages->getValue(true);
        }

        $toSave = \array_intersect_key($messages, \array_flip($selected));

        $messages = [];
        foreach ($toSave as $data) {
            $messages[] = SfProfilerMessage::create($data)->convertToMessage();
        }

        return $messages;
    }
}
