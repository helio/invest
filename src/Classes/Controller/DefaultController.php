<?php

namespace Helio\Invest\Controller;

use Helio\Invest\Controller\Traits\ParametrizedController;
use Helio\Invest\Controller\Traits\TypeBrowserController;
use Helio\Invest\Model\User;
use Helio\Invest\Utility\CookieUtility;
use Helio\Invest\Utility\JwtUtility;
use Helio\Invest\Utility\MailUtility;
use Helio\Invest\Utility\ServerUtility;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\StatusCode;


/**
 * Class Frontend
 *
 * @package    Helio\Panel\Controller
 * @author    Christoph Buchli <team@opencomputing.cloud>
 *
 * @RoutePrefix('/')
 */
class DefaultController extends AbstractController
{
    use ParametrizedController;
    use TypeBrowserController;


    protected function getMode(): ?string
    {
        return 'default';
    }

    /**
     *
     * @return ResponseInterface
     * @Route("", methods={"GET"})
     */
    public function LoginAction(): ResponseInterface
    {
        $token = $this->request->getCookieParam('token', null);
        if ($token) {
            return $this->response->withRedirect('/app', StatusCode::HTTP_FOUND);
        }
        return $this->render(['title' => 'Welcome!']);
    }

    /**
     *
     * @return ResponseInterface
     * @Route("loggedout", methods={"GET"})
     */
    public function LoggedoutAction(): ResponseInterface
    {
        return CookieUtility::deleteCookie($this->render(['title' => 'Good Bye', 'loggedOut' => true]), 'token');
    }


    /**
     *
     * @return ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Exception
     *
     * @Route("user/login", methods={"POST"}, name="user.submit")
     */
    public function SubmitUserAction(): ResponseInterface
    {

        // normal user process
        $this->requiredParameterCheck(['email' => FILTER_SANITIZE_EMAIL]);

        /** @var User $user */
        $user = $this->dbHelper->getRepository(User::class)->findOneByEmail($this->params['email']);
        if (!$user) {
            $content = 'New user requested access: ' . $this->params['email'] . "\nClick to activate (make sure you're logged in  as admin first):\n" .
                ServerUtility::getBaseUrl() . 'app/admin/user/activate/' . $this->params['email'];
            if (!MailUtility::sendMailToAdmin($content)) {
                throw new \RuntimeException('Mail Error during User Creation', 1555743209);
            }

            return $this->render(
                [
                    'user' => $user,
                    'title' => 'Access requested'
                ]
            );
        }

        if (!MailUtility::sendConfirmationMail($user)) {
            throw new \RuntimeException('Mail Error during User Creation', 1545655919);
        }

        return $this->render(
            [
                'user' => $user,
                'title' => 'Login link sent'
            ]
        );
    }
}