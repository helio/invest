<?php

namespace Helio\Invest\Controller\Traits;

use Helio\Invest\App;
use Helio\Invest\Model\User;

trait AuthenticatedController
{

    /**
     * @var User
     */
    protected $user;


    /**
     * @return bool
     */
    public function setupUser(): bool
    {
        try {
            $this->user = App::getApp()->getContainer()['user'];
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }

    /**
     * Persist
     */
    protected function persistUser(): void {
        $this->user->setLatestAction();

        $this->dbHelper->persist($this->user);
        $this->dbHelper->flush($this->user);
    }
}