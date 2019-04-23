<?php

namespace Helio\Invest\Controller;

use Helio\Invest\Controller\Traits\AuthenticatedController;
use Helio\Invest\Controller\Traits\TypeBrowserController;
use Helio\Invest\Model\User;
use Helio\Invest\Utility\InvestUtility;
use Helio\Invest\Utility\JwtUtility;
use Helio\Invest\Utility\MailUtility;
use Helio\Invest\Utility\ServerUtility;
use Psr\Http\Message\ResponseInterface;


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
        /** @var User $user */
        $user = $this->dbHelper->getRepository(User::class)->findOneByEmail($email);
        if ($user) {
            return $this->render(['title' => 'Warning! User already in database.', 'userId' => $user->getId()]);
        }

        $user = new User();
        $user->setEmail($email)->setActive(true)->setCreated()->setName(substr($email, 0, strpos($email, '@')));
        $this->dbHelper->persist($user);
        $this->dbHelper->flush($user);

        if (!InvestUtility::createUserDir($user->getId())) {
            throw new \RuntimeException('Error during creating user dir', 1556012784);
        }

        return $this->render(['title' => 'done!', 'userId' => $user->getId(), 'link' => ServerUtility::getBaseUrl() . 'app?token=' . JwtUtility::generateToken($user->getId())['token']]);
    }

}