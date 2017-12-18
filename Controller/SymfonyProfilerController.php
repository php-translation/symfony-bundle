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

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\DataCollectorTranslator;
use Symfony\Component\VarDumper\Cloner\Data;
use Translation\Bundle\Model\SfProfilerMessage;
use Translation\Bundle\Service\StorageService;
use Translation\Common\Model\Message;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class SymfonyProfilerController extends Controller
{
    /**
     * @param Request $request
     * @param string  $token
     *
     * @return Response
     */
    public function editAction(Request $request, $token)
    {
        if (!$this->getParameter('php_translation.toolbar.allow_edit')) {
            return new Response('You are not allowed to edit the translations.');
        }

        if (!$request->isXmlHttpRequest()) {
            return $this->redirectToRoute('_profiler', ['token' => $token]);
        }

        $message = $this->getMessage($request, $token);
        /** @var StorageService $storage */
        $storage = $this->get('php_translation.storage');

        if ($request->isMethod('GET')) {
            $translation = $storage->syncAndFetchMessage($message->getLocale(), $message->getDomain(), $message->getKey());

            return $this->render('@Translation/SymfonyProfiler/edit.html.twig', [
                'message' => $translation,
                'key' => $request->query->get('message_id'),
            ]);
        }

        //Assert: This is a POST request
        $message->setTranslation($request->request->get('translation'));
        $storage->update($message->convertToMessage());

        return new Response($message->getTranslation());
    }

    /**
     * @param Request $request
     * @param string  $token
     *
     * @return Response
     */
    public function syncAction(Request $request, $token)
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->redirectToRoute('_profiler', ['token' => $token]);
        }

        /** @var StorageService $storage */
        $storage = $this->get('php_translation.storage');
        $sfMessage = $this->getMessage($request, $token);
        $message = $storage->syncAndFetchMessage($sfMessage->getLocale(), $sfMessage->getDomain(), $sfMessage->getKey());

        if (null !== $message) {
            return new Response($message->getTranslation());
        }

        return new Response('Asset not found', 404);
    }

    /**
     * @param Request $request
     * @param         $token
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function syncAllAction(Request $request, $token)
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->redirectToRoute('_profiler', ['token' => $token]);
        }

        /** @var StorageService $storage */
        $storage = $this->get('php_translation.storage');
        $storage->sync();

        return new Response('Started synchronization of all translations');
    }

    /**
     * Save the selected translation to resources.
     *
     * @author Damien Alexandre (damienalexandre)
     *
     * @param Request $request
     * @param string  $token
     *
     * @return Response
     */
    public function createAssetsAction(Request $request, $token)
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->redirectToRoute('_profiler', ['token' => $token]);
        }

        $messages = $this->getSelectedMessages($request, $token);
        if (empty($messages)) {
            return new Response('No translations selected.');
        }

        $uploaded = [];
        /** @var StorageService $storage */
        $storage = $this->get('php_translation.storage');
        foreach ($messages as $message) {
            $storage->create($message);
            $uploaded[] = $message;
        }

        return new Response(sprintf('%s new assets created!', count($uploaded)));
    }

    /**
     * @param Request $request
     * @param string  $token
     *
     * @return SfProfilerMessage
     */
    private function getMessage(Request $request, $token)
    {
        $profiler = $this->get('profiler');
        $profiler->disable();

        $messageId = $request->request->get('message_id', $request->query->get('message_id'));

        $profile = $profiler->loadProfile($token);
        if (null === $dataCollector = $profile->getCollector('translation')) {
            throw $this->createNotFoundException('No collector with name "translation" was found.');
        }

        $messages = $dataCollector->getMessages();

        if ($messages instanceof Data) {
            $messages = $messages->getValue(true);
        }

        if (!isset($messages[$messageId])) {
            throw $this->createNotFoundException(sprintf('No message with key "%s" was found.', $messageId));
        }
        $message = SfProfilerMessage::create($messages[$messageId]);

        if (DataCollectorTranslator::MESSAGE_EQUALS_FALLBACK === $message->getState()) {
            $message->setLocale($profile->getCollector('request')->getLocale())
                ->setTranslation(sprintf('[%s]', $message->getTranslation()));
        }

        return $message;
    }

    /**
     * @param Request $request
     * @param string  $token
     *
     * @return Message[]
     */
    protected function getSelectedMessages(Request $request, $token)
    {
        $profiler = $this->get('profiler');
        $profiler->disable();

        $selected = $request->request->get('selected');
        if (!$selected || 0 == count($selected)) {
            return [];
        }

        $profile = $profiler->loadProfile($token);
        $dataCollector = $profile->getCollector('translation');
        $messages = $dataCollector->getMessages();

        if ($messages instanceof Data) {
            $messages = $messages->getValue(true);
        }

        $toSave = array_intersect_key($messages, array_flip($selected));

        $messages = [];
        foreach ($toSave as $data) {
            $messages[] = SfProfilerMessage::create($data)->convertToMessage();
        }

        return $messages;
    }
}
