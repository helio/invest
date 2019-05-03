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
}