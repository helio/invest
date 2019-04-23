<?php

namespace Helio\Invest\Controller;

use Grpc\Server;
use Helio\Invest\Controller\Traits\ParametrizedController;
use Helio\Invest\Controller\Traits\TypeBrowserController;
use Helio\Invest\Helper\ZapierHelper;
use Helio\Invest\Model\User;
use Helio\Invest\Utility\CookieUtility;
use Helio\Invest\Utility\InvestUtility;
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

        $email = $data['email'];
        $name = $data['name'] . ' ' . $data['surname'];

        /** @var User $user */
        $user = $this->dbHelper->getRepository(User::class)->findOneByEmail($email);
        if ($user) {
            return $this->render(['title' => 'Warning! User already in database.', 'userId' => $user->getId()], StatusCode::HTTP_NOT_ACCEPTABLE);
        }

        $user = new User();
        $user->setEmail($email)->setActive(true)->setCreated()->setLatestAction()->setName($name);
        $this->dbHelper->persist($user);
        $this->dbHelper->flush($user);

        if (!InvestUtility::createUserDir($user->getId())) {
            throw new \RuntimeException('Error during creating user dir', 1556012784);
        }

        // setup user
        if (!MailUtility::sendConfirmationMail($user, 'activation')) {
            throw new \RuntimeException('Could not send confirmation mail to user', 1556012770);
        }

        /** @var UploadedFileInterface $uploadedFile */
        $uploadedFile = $this->request->getUploadedFiles()['file'];
        if ($uploadedFile && $uploadedFile->getError() === UPLOAD_ERR_OK) {
            $uploadedFile->moveTo(ServerUtility::getApplicationRootPath(['assets', $user->getId()]) . $uploadedFile->getClientFilename());
            return $this->render();
        }

        return $this->render(['title' => 'success!', 'success' => true]);

    }
}