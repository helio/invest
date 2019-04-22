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
     * @param int $id
     * @return ResponseInterface
     * @Route("/user/activate/{id:[\d]+}", methods={"GET"})
     * @throws \Exception
     */
    public function activateUserAction(int $id): ResponseInterface
    {
        /** @var User $user */
        $user = $this->dbHelper->getRepository(User::class)->findOneById($id);
        $user->setActive(true);
        $this->dbHelper->persist($user);
        $this->dbHelper->flush($user);

        // setup user
        MailUtility::sendConfirmationMail($user, 'activation');
        InvestUtility::createUserDir($user->getId());
        return $this->render(['title' => 'done!']);
    }

}