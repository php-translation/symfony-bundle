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
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Translation\Bundle\Exception\MessageValidationException;
use Translation\Bundle\Service\CacheClearer;
use Translation\Bundle\Service\StorageManager;
use Translation\Bundle\Service\StorageService;
use Translation\Common\Model\Message;
use Translation\Common\Model\MessageInterface;

/**
 * @author Damien Alexandre <dalexandre@jolicode.com>
 */
class EditInPlaceController extends AbstractController
{
    private $storageManager;
    private $cacheClearer;
    private $validator;

    public function __construct(StorageManager $storageManager, CacheClearer $cacheClearer, ValidatorInterface $validator)
    {
        $this->storageManager = $storageManager;
        $this->cacheClearer = $cacheClearer;
        $this->validator = $validator;
    }

    public function editAction(Request $request, string $configName, string $locale): Response
    {
        try {
            $messages = $this->getMessages($request, $locale, ['Edit']);
        } catch (MessageValidationException $e) {
            return new Response($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        /** @var StorageService $storage */
        $storage = $this->storageManager->getStorage($configName);
        foreach ($messages as $message) {
            $storage->update($message);
        }

        $this->cacheClearer->clearAndWarmUp($locale);

        return new Response();
    }

    /**
     * Get and validate messages from the request.
     *
     * @return MessageInterface[]
     *
     * @throws MessageValidationException
     */
    private function getMessages(Request $request, string $locale, array $validationGroups = []): array
    {
        $json = $request->getContent();
        $data = \json_decode($json, true);
        $messages = [];

        foreach ($data as $key => $value) {
            [$domain, $translationKey] = \explode('|', $key);

            $message = new Message($translationKey, $domain, $locale, $value);

            $errors = $this->validator->validate($message, null, $validationGroups);
            if (\count($errors) > 0) {
                throw MessageValidationException::create();
            }

            $messages[] = $message;
        }

        return $messages;
    }
}
