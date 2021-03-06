<?php

namespace Helio\Invest\Middleware;

use Helio\Invest\App;
use Helio\Invest\Helper\DbHelper;
use Helio\Invest\Model\User;
use Helio\Invest\Utility\CookieUtility;
use Helio\Invest\Utility\ServerUtility;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Tuupola\Middleware\DoublePassTrait;

/**
 * Class ReAuthenticate
 *
 * @package    Helio\Panel\Middleware
 * @author    Christoph Buchli <team@opencomputing.cloud>
 */
class LoadUserFromJwt implements MiddlewareInterface
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
        $container = App::getApp()->getContainer();
        if (isset($container['jwt']['uid'])) {
            /**
             * @var DbHelper $dbHelper
             * @var User $user
             */
            $dbHelper = $container['dbHelper'];
            $user = $dbHelper->getRepository(User::class)->findOneById($container['jwt']['uid']);
            if ($user) {
                if ($user->getLoggedOut()) {
                    $tokenGenerationTime = new \DateTime('now', ServerUtility::getTimezoneObject());
                    $tokenGenerationTime->setTimestamp($container['jwt']['iat']);

                    $userLoggedOutTime = $user->getLoggedOut()->setTimezone(ServerUtility::getTimezoneObject());

                    if ($userLoggedOutTime > $tokenGenerationTime) {
                        return CookieUtility::deleteCookie($handler->handle($request)->withRedirect('/panel/logout'), 'token');
                    }
                }

                $user->setLatestAction();
                if (($container['jwt']['guest'] ?? false) === true) {
                    $user->setGuestClickCount();
                }
                $dbHelper->merge($user);
                $dbHelper->flush();

                if (\array_key_exists('impersonate', $request->getCookieParams()) && $user->isAdmin() && (string)(int)$request->getCookieParams()['impersonate'] === (string)$request->getCookieParams()['impersonate']) {
                    $user = $dbHelper->getRepository(User::class)->findOneById($request->getCookieParams()['impersonate']);
                }

                $container['user'] = $user;
            }
        }

        return $handler->handle($request);

    }
}