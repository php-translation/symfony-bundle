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
use Translation\Common\Model\Message;

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
            $messages = $this->getMessages($request, ['Edit'], $locale);
        } catch (MessageValidationException $e) {
            return new Response($e->getMessage(), 400);
        }

        foreach ($messages as $message) {
            $this->get('php_translation.storage.'.$configName)->update($message);
        }

        return new Response();
    }

    /**
     * Get and validate messages from the request
     *
     * @param Request $request
     * @param array $validationGroups
     * @param $locale
     * @return array
     */
    private function getMessages(Request $request, array $validationGroups = [], $locale)
    {
        $json = $request->getContent();
        $data = json_decode($json, true);
        $messages = [];
        $validator = $this->get('validator');

        foreach ($data as $key => $value) {
            list($domain, $translationKey) = explode('|', $key);
            $message = new Message();
            $message->setKey($translationKey);
            $message->setTranslation($value);
            $message->setDomain($domain);
            $message->setLocale($locale);

            // @todo validation
            //$errors = $validator->validate($message, null, $validationGroups);
            //if (count($errors) > 0) {
            //    throw  MessageValidationException::create();
            //}

            $messages[] = $message;
        }

        return $messages;
    }
}
