<?php

namespace Translation\Bundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\Translator;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class WebUIController extends Controller
{
    /**
     * @param $domain
     *
     * @return Response
     */
    public function indexAction($domain, $locale)
    {
        /** @var Translator $translator */
        $translator = $this->get('translator');
        $catalogue = $translator->getCatalogue($locale);
        $domains = $catalogue->getDomains();
        $messages = $catalogue->all($domain);

        return $this->renderView('Translation:WebUI:index.html.twig', [
            'domains'=>$domains,
            'messages'=>$messages,
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
        return new Response();
    }

    /**
     * @param Request $request
     * @param string  $domain
     *
     * @return Response
     */
    public function editAction(Request $request, $domain)
    {
        return new Response();
    }
}
