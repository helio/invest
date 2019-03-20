<?php

namespace Helio\Invest\Middleware;

use Helio\Invest\App;
use Helio\Invest\Helper\DbHelper;
use Helio\Invest\Model\User;
use Helio\Invest\Utility\JwtUtility;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Tuupola\Middleware\DoublePassTrait;

/**
 * Class that allows access to
 *
 * @package    Helio\Panel\Middleware
 * @author    Christoph Buchli <team@opencomputing.cloud>
 */
class CustomApiTokenForUser implements MiddlewareInterface
{


    /**
     * use process method instead of __invoke
     */
    use DoublePassTrait;


    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     *
     * @return ResponseInterface
     * @throws \Exception
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {

        $cookies = $request->getCookieParams();
        if (\array_key_exists('token', $cookies) && strpos($cookies['token'], ':') === 8) {
            $container = App::getApp()->getContainer();
            /**
             * @var DbHelper $dbHelper
             * @var User $user
             */
            $dbHelper = $container['dbHelper'];
            $user = $dbHelper->getRepository(User::class)->findOneByToken($request->getCookieParams()['token']);
            if ($user && JwtUtility::verifyUserIdentificationToken($user, $request->getCookieParams()['token']) && strpos($request->getUri()->getPath(), '/api') === 0) {
                $cookies['token'] = JwtUtility::generateToken($user->getId())['token'];
                $cookies['block_reauth'] = 'true';
            }
        }
        return $handler->handle($request->withCookieParams($cookies));
    }
}