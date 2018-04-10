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
use Translation\Bundle\Exception\MessageValidationException;
use Translation\Bundle\Service\StorageService;
use Translation\Common\Model\Message;
use Translation\Common\Model\MessageInterface;

/**
 * @author Damien Alexandre <dalexandre@jolicode.com>
 */
class EditInPlaceController extends Controller
{
    /**
     * @param Request $request
     * @param string  $configName
     * @param string  $locale
     *
     * @return Response
     */
    public function editAction(Request $request, $configName, $locale)
    {
        try {
            $messages = $this->getMessages($request, $locale, ['Edit']);
        } catch (MessageValidationException $e) {
            return new Response($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        /** @var StorageService $storage */
        $storage = $this->get('php_translation.storage_manager')->getStorage($configName);
        foreach ($messages as $message) {
            $storage->update($message);
        }

        $cacheClearer = $this->get('php_translation.cache_clearer');
        $cacheClearer->clearAndWarmUp($locale);

        return new Response();
    }

    /**
     * Get and validate messages from the request.
     *
     * @param Request $request
     * @param string  $locale
     * @param array   $validationGroups
     *
     * @return MessageInterface[]
     *
     * @throws MessageValidationException
     */
    private function getMessages(Request $request, $locale, array $validationGroups = [])
    {
        $json = $request->getContent();
        $data = json_decode($json, true);
        $messages = [];
        $validator = $this->get('validator');

        foreach ($data as $key => $value) {
            list($domain, $translationKey) = explode('|', $key);

            $message = new Message($translationKey, $domain, $locale, $value);

            $errors = $validator->validate($message, null, $validationGroups);
            if (count($errors) > 0) {
                throw MessageValidationException::create();
            }

            $messages[] = $message;
        }

        return $messages;
    }
}
