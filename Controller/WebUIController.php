<?php

namespace Translation\Bundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
    public function indexAction($domain)
    {
        return new Response();
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
