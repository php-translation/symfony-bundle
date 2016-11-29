<?php

namespace Translation\Bundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\Translator;
use Translation\Symfony\CatalogueManager;
use Translation\Symfony\Model\Message;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class WebUIController extends Controller
{
    public function indexAction()
    {
        $locales = $this->getParameter('translation.locales');
        /** @var Translator $translator */
        $translator = $this->get('translator');
        $catalogues = [];
        $catalogueSize = [];
        $maxDomainSize = [];
        $maxCatalogueSize = 1;
        foreach ($locales as $locale) {
            $domains = $translator->getCatalogue($locale)->all();
            $catalogueSize[$locale] = 0;
            foreach ($domains as $domain => $messages) {
                $count = count($messages);
                $catalogueSize[$locale]+=$count;
                $catalogues[$locale][$domain] = $count;
                if (!isset($maxDomainSize[$domain]) || $count>$maxDomainSize[$domain]) {
                    $maxDomainSize[$domain] = $count;
                }
            }

            if ($catalogueSize[$locale]>$maxCatalogueSize) {
                $maxCatalogueSize = $catalogueSize[$locale];
            }

        }

        return $this->render('TranslationBundle:WebUI:index.html.twig', [
            'catalogues'=>$catalogues,
            'catalogueSize'=>$catalogueSize,
            'maxDomainSize' => $maxDomainSize,
            'maxCatalogueSize' => $maxCatalogueSize,
            'locales' => $locales,
        ]);
    }

    /**
     * @param $locale
     * @param $domain
     *
     * @return Response
     */
    public function showAction($locale, $domain)
    {
        $locales = $this->getParameter('php_translation.locales');
        /** @var Translator $translator */
        $translator = $this->get('translator');
        $catalogues = [];
        foreach ($locales as $l) {
            $catalogues[] = $translator->getCatalogue($l);
        }
        $catalogueManager = $this->get('php_translation.catalogue_manager');
        $catalogueManager->load($catalogues);

        /** @var Message[] $messages */
        $messages = $catalogueManager->getMessages($locale, $domain);

        return $this->render('TranslationBundle:WebUI:show.html.twig', [
            'messages'=>$messages,
            'domains'=>$catalogueManager->getDomains(),
            'currentDomain' => $domain,
            'locales' => $locales,
            'currentLocale' => $locale,
        ]);
    }

    /**
     * @param Request $request
     * @param string  $domain
     *
     * @return Response
     */
    public function createAction(Request $request, $domain)
    {
        return new Response('Not yet implemented');
    }

    /**
     * @param Request $request
     * @param string  $domain
     *
     * @return Response
     */
    public function editAction(Request $request, $domain)
    {
        return new Response('Not yet implemented');
    }
}
