<?php

namespace Helio\Invest\Controller;

use Helio\Invest\App;
use Helio\Invest\Controller\Traits\AuthenticatedController;
use Helio\Invest\Controller\Traits\TypeBrowserController;
use Helio\Invest\Helper\DbHelper;
use Helio\Invest\Model\User;
use Helio\Invest\Utility\InvestUtility;
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
 * @RoutePrefix('/app/admin')
 */
class AdminController extends AbstractController
{
    use AuthenticatedController;
    use TypeBrowserController;


    public function validateIsAdmin(): bool
    {
        return $this->user->isAdmin();
    }

    protected function getMode(): ?string
    {
        return 'admin';
    }

    /**
     * @param string $email
     * @return ResponseInterface
     * @Route("/user/activate/{email:.+}", methods={"GET"})
     * @throws \Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function activateUserAction(string $email): ResponseInterface
    {
        $user = new User();
        $user->setEmail($email)->setActive(true)->setCreated()->setLatestAction()->setName(substr($email, 0, strpos($email, '@')));
        $this->dbHelper->persist($user);
        $this->dbHelper->flush($user);

        if (!$this->zapierHelper->submitUserToZapier($user)) {
            throw new \RuntimeException('Zapier Error during User Creation', 1546940197);
        }

        // setup user
        if (!MailUtility::sendConfirmationMail($user, 'activation')) {
            throw new \RuntimeException('Could not send confirmation mail to user', 1556012770);
        }

        if (!InvestUtility::createUserDir($user->getId())) {
            throw new \RuntimeException('Error during creating user dir', 1556012784);
        }

        return $this->render(['title' => 'done!', 'userId' => $user->getId()]);
    }

}