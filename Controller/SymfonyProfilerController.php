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
use Translation\Bundle\Model\SfProfilerMessage;
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
        $storage = $this->get('php_translation.storage');

        if ($request->isMethod('GET')) {
            $translation = $storage->get($message->getLocale(), $message->getDomain(), $message->getKey());

            return $this->render('HappyrTranslationBundle:Profiler:edit.html.twig', [
                'message' => $translation,
                'key' => $message->getKey(),
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
    public function flagAction(Request $request, $token)
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->redirectToRoute('_profiler', ['token' => $token]);
        }

        $message = $this->getMessage($request, $token);

        // TODO
        $saved = false;

        return new Response($saved ? 'OK' : 'ERROR');
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

        $sfMessage = $this->getMessage($request, $token);
        $message = $this->get('php_translation.storage')->syncAndFetchMessage($sfMessage->getLocale(), $sfMessage->getDomain(), $sfMessage->getKey());

        if ($message !== null) {
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

        $this->get('php_translation.storage')->sync();

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
        $trans = $this->get('php_translation.storage');
        foreach ($messages as $message) {
            if ($trans->update($message)) {
                $uploaded[] = $message;
            }
        }

        $saved = count($uploaded);
        if ($saved > 0) {
            $this->get('happyr.translation.filesystem')->updateMessageCatalog($uploaded);
        }

        return new Response(sprintf('%s new assets created!', $saved));
    }

    /**
     * @param Request $request
     * @param string  $token
     *
     * @return SfProfilerMessage
     */
    protected function getMessage(Request $request, $token)
    {
        // TODO Move disable code to somewhere else.
        $profiler = $this->get('profiler');
        $profiler->disable();

        $messageId = $request->request->get('message_id', $request->query->get('message_id'));

        $profile = $profiler->loadProfile($token);
        $messages = $profile->getCollector('translation')->getMessages();
        if (!isset($messages[$messageId])) {
            throw $this->createNotFoundException(sprintf('No message with key "%s" was found.', $messageId));
        }
        $message = SfProfilerMessage::create($messages[$messageId]);

        if ($message->getState() === DataCollectorTranslator::MESSAGE_EQUALS_FALLBACK) {
            $message->setLocale($profile->getCollector('request')->getLocale())
                ->setTranslation(sprintf('[%s]', $message->getTranslation()));
        }

        return $message;
    }

    /**
     * @param Request $request
     * @param string  $token
     *
     * @return array
     */
    protected function getSelectedMessages(Request $request, $token)
    {
        $profiler = $this->get('profiler');
        $profiler->disable();

        $selected = $request->request->get('selected');
        if (!$selected || count($selected) == 0) {
            return [];
        }

        $profile = $profiler->loadProfile($token);
        $dataCollector = $profile->getCollector('translation');
        $toSave = array_intersect_key($dataCollector->getMessages(), array_flip($selected));

        $messages = [];
        foreach ($toSave as $data) {
            //We do not want do add the placeholder to Loco. That messes up the stats.
            $data['translation'] = '';

            $messages[] = new Message($data);
        }

        return $messages;
    }
}
