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
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Translation\Bundle\Exception\MessageValidationException;
use Translation\Bundle\Service\StorageService;
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
            $messages = $this->getMessages($request, $locale, ['Edit']);
        } catch (MessageValidationException $e) {
            return new Response($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        /** @var StorageService $storage */
        $storage = $this->get('php_translation.storage.'.$configName);
        foreach ($messages as $message) {
            $storage->update($message);
        }

        $this->rebuildTranslations($locale);

        return new Response();
    }

    /**
     * Remove the Symfony translation cache and warm it up again.
     *
     * @param $locale
     */
    private function rebuildTranslations($locale)
    {
        $cacheDir = $this->getParameter('kernel.cache_dir');
        $translationDir = sprintf('%s/translations', $cacheDir);

        $filesystem = $this->get('filesystem');
        $finder = new Finder();

        if (!is_writable($translationDir)) {
            throw new \RuntimeException(sprintf('Unable to write in the "%s" directory', $translationDir));
        }

        // Remove the translations for this locale
        $files = $finder->files()->name('*.'.$locale.'.*')->in($translationDir);
        foreach ($files as $file) {
            $filesystem->remove($file);
        }

        // Build them again
        $this->get('translator')->warmUp($translationDir);
    }

    /**
     * Get and validate messages from the request.
     *
     * @param Request $request
     * @param string  $locale
     * @param array   $validationGroups
     *
     * @return Message[]
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

            $message = new Message();
            $message->setKey($translationKey);
            $message->setTranslation($value);
            $message->setDomain($domain);
            $message->setLocale($locale);

            $errors = $validator->validate($message, null, $validationGroups);
            if (count($errors) > 0) {
                throw MessageValidationException::create();
            }

            $messages[] = $message;
        }

        return $messages;
    }
}
