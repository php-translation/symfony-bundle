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
use Symfony\Component\Translation\DataCollector\TranslationDataCollector;
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
    private $storage;
    private $profiler;
    private $isToolbarAllowEdit;

    public function __construct(StorageService $storage, Profiler $profiler, bool $isToolbarAllowEdit)
    {
        $this->storage = $storage;
        $this->profiler = $profiler;
        $this->isToolbarAllowEdit = $isToolbarAllowEdit;
    }

    public function editAction(Request $request, string $token): Response
    {
        if (!$this->isToolbarAllowEdit) {
            return new Response('You are not allowed to edit the translations.');
        }

        if (!$request->isXmlHttpRequest()) {
            return $this->redirectToRoute('_profiler', ['token' => $token]);
        }

        $message = $this->getMessage($request, $token);

        if ($request->isMethod('GET')) {
            $translation = $this->storage->syncAndFetchMessage($message->getLocale(), $message->getDomain(), $message->getKey());

            return $this->render('@Translation/SymfonyProfiler/edit.html.twig', [
                'message' => $translation,
                'key' => $request->query->get('message_id'),
            ]);
        }

        //Assert: This is a POST request
        $message->setTranslation($request->request->get('translation'));
        $this->storage->update($message->convertToMessage());

        return new Response($message->getTranslation());
    }

    public function syncAction(Request $request, string $token): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->redirectToRoute('_profiler', ['token' => $token]);
        }

        $sfMessage = $this->getMessage($request, $token);
        $message = $this->storage->syncAndFetchMessage($sfMessage->getLocale(), $sfMessage->getDomain(), $sfMessage->getKey());

        if (null !== $message) {
            return new Response($message->getTranslation());
        }

        return new Response('Asset not found', 404);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function syncAllAction(Request $request, string $token): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->redirectToRoute('_profiler', ['token' => $token]);
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
            return $this->redirectToRoute('_profiler', ['token' => $token]);
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
        $this->profiler->disable();

        $messageId = $request->request->get('message_id', $request->query->get('message_id'));

        $collectorMessages = $this->getMessages($token);

        if (!isset($collectorMessages[$messageId])) {
            throw $this->createNotFoundException(\sprintf('No message with key "%s" was found.', $messageId));
        }
        $message = SfProfilerMessage::create($collectorMessages[$messageId]);

        if (DataCollectorTranslator::MESSAGE_EQUALS_FALLBACK === $message->getState()) {
            /** @var \Symfony\Component\HttpKernel\DataCollector\RequestDataCollector */
            $requestCollector = $this->profiler->loadProfile($token)->getCollector('request');

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
        $this->profiler->disable();

        $selected = $request->request->get('selected');
        if (!$selected || 0 == \count($selected)) {
            return [];
        }

        $toSave = \array_intersect_key($this->getMessages($token), \array_flip($selected));

        $messages = [];
        foreach ($toSave as $data) {
            $messages[] = SfProfilerMessage::create($data)->convertToMessage();
        }

        return $messages;
    }

    private function getMessages(string $token, string $profileName = 'translation'): array
    {
        $profile = $this->profiler->loadProfile($token);

        if (null === $dataCollector = $profile->getCollector($profileName)) {
            throw $this->createNotFoundException("No collector with name \"$profileName\" was found.");
        }
        if (!$dataCollector instanceof TranslationDataCollector) {
            throw $this->createNotFoundException("Collector with name \"$profileName\" is not an instance of TranslationDataCollector.");
        }

        $messages = $dataCollector->getMessages();

        if (\class_exists(Data::class) && $messages instanceof Data) {
            return $messages->getValue(true);
        }

        return $messages;
    }
}
