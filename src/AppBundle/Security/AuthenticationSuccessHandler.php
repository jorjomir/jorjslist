<?php
/**
 * Created by PhpStorm.
 * User: jorjomir
 * Date: 12/11/2018
 * Time: 12:00 AM
 */

namespace AppBundle\Security;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;
use Symfony\Component\Security\Http\HttpUtils;

class AuthenticationSuccessHandler extends DefaultAuthenticationSuccessHandler
{
    protected $httpUtils;
    protected $targetUrl;

    /**
     * AuthenticationSuccessHandler constructor.
     * @param $httpUtils
     */
    public function __construct(HttpUtils $httpUtils)
    {
        $this->httpUtils = $httpUtils;
        $this->targetUrl = '/index?login=success';
    }

    public function onLoginSuccess(Request $request)
    {
        $response= $this->httpUtils->createRedirectResponse($request, $this->targetUrl);
        return $response;
    }


}