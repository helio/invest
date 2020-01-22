<?php

namespace Helio\Invest\Controller;

use Grpc\Server;
use Helio\Invest\Controller\Traits\ParametrizedController;
use Helio\Invest\Controller\Traits\TypeBrowserController;
use Helio\Invest\Helper\LogHelper;
use Helio\Invest\Helper\ZapierHelper;
use Helio\Invest\Model\User;
use Helio\Invest\Utility\CookieUtility;
use Helio\Invest\Utility\InvestUtility;
use Helio\Invest\Utility\JwtUtility;
use Helio\Invest\Utility\MailUtility;
use Helio\Invest\Utility\ServerUtility;
use http\Exception\InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UploadedFileInterface;
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
            if (!MailUtility::sendMailToAdmin('New user requested access: ' . $this->params['email'])) {
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

    /**
     * Function called by Zapier hook
     *
     * @return ResponseInterface
     *
     * @Route("user/add", methods={"POST","GET"}, name="user.autocreate")
     * @throws \Exception
     */
    public function AutoCreateUserAction(): ResponseInterface
    {
        $this->requiredParameterCheck(['auth' => FILTER_SANITIZE_STRING]);

        [$salt, $token] = explode(':', $this->params['auth']);

        if (ServerUtility::getHashOfString($salt . ':' . ServerUtility::get('ZAPIER_SECRET', 'ERROR')) !== $token) {
            throw new \InvalidArgumentException('Invalid token supplied');
        }

        $data = [];
        mb_parse_str($this->request->getParsedBody()['data'], $data);
        LogHelper::debug('Data received: ' . print_r($data, true));

        $email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
        $name = filter_var($data['name'] . ' ' . $data['surname'], FILTER_SANITIZE_STRING);

        /** @var User $user */
        $user = $this->dbHelper->getRepository(User::class)->findOneByEmail($email);
        if (!$user) {
            $user = new User();
        }

        $user->setEmail($email)->setActive(true)->setCreated()->setName($name);
        $this->dbHelper->persist($user);
        $this->dbHelper->flush($user);

        if (!InvestUtility::createUserDir($user->getId())) {
            throw new \RuntimeException('Error during creating user dir', 1556012784);
        }

        /** @var UploadedFileInterface $uploadedFile */
        $uploadedFile = $this->request->getUploadedFiles()['file'];
        if ($uploadedFile && $uploadedFile->getError() === UPLOAD_ERR_OK) {
            $round_suffix = array_key_exists('round', $data) ? filter_var($data['round'], FILTER_CALLBACK, function ($value) {
                $matches = [];
                $result = preg_match_all('/[a-zA-Z 0-9]/', trim($value), $matches);
                LogHelper::debug('preg_match_result: ' . __LINE__ . '. Result was ' . $result . ' and matches were ' . print_r($matches, true));
                if ($result > 0) {
                    return '_' . str_replace('\s', '_', implode($matches[0]));
                }
                return '';
            }) : '';
            $filename = 'Helio_Convertible' . $round_suffix . '.pdf';

            $uploadedFile->moveTo(ServerUtility::getApplicationRootPath(['assets', $user->getId()]) . DIRECTORY_SEPARATOR . $filename);
        }

        return $this->json(['title' => 'success', 'success' => true, 'userId' => $user->getId(), 'link' => ServerUtility::getBaseUrl() . 'app?token=' . JwtUtility::generateToken($user->getId())['token']]);

    }
}