<?php
/** @noinspection PhpUnusedAliasInspection */

namespace Helio\Invest\Model;

use Doctrine\{
    Common\Collections\Collection,
    ORM\Mapping\Entity,
    ORM\Mapping\Table,
    ORM\Mapping\Id,
    ORM\Mapping\Column,
    ORM\Mapping\GeneratedValue,
    ORM\Mapping\ManyToOne,
    ORM\Mapping\OneToMany
};

use Doctrine\Common\Collections\ArrayCollection;
use Helio\Invest\App;
use Helio\Invest\Helper\LogHelper;
use Helio\Invest\Utility\ArrayUtility;
use Helio\Invest\Utility\InvestUtility;
use Helio\Invest\Utility\JwtUtility;
use Helio\Invest\Utility\ServerUtility;

/**
 * @Entity @Table(name="user")
 **/
class User extends AbstractModel
{


    /**
     * @var string
     *
     * @Column
     */
    protected $email = '';


    /**
     * @var string
     *
     * @Column
     */
    protected $token = '';


    /**
     * @var string
     *
     * @Column
     */
    protected $role = '';


    /**
     * @var boolean
     *
     * @Column(type="boolean")
     */
    protected $active = false;


    /**
     * @var boolean
     *
     * @Column(type="boolean")
     */
    protected $admin = false;


    /**
     * @var \DateTime $loggedOut
     *
     * @Column(type="datetimetz", nullable=true)
     */
    protected $loggedOut;

    /**
     * @var int
     *
     * @Column
     */
    protected $guestClickCount = 0;


    /**
     * User constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }


    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }


    /**
     * @param string $email
     * @return User
     */
    public function setEmail(string $email): User
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @param string $token
     * @return User
     */
    public function setToken(string $token): User
    {
        $this->token = $token;
        return $this;
    }


    /**
     * @return string
     */
    public function getRole(): string
    {
        return $this->role;
    }


    /**
     * @param string $role
     * @return User
     */
    public function setRole(string $role): User
    {
        $this->role = $role;

        return $this;
    }


    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }


    /**
     * @param bool $active
     * @return User
     */
    public function setActive(bool $active): User
    {
        $this->active = $active;

        return $this;
    }


    /**
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->admin;
    }


    /**
     * @param bool $admin
     * @return User
     */
    public function setAdmin(bool $admin): User
    {
        $this->admin = $admin;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getLoggedOut(): ?\DateTime
    {
        return $this->loggedOut;
    }

    /**
     * @param \DateTime $loggedOut
     */
    public function setLoggedOut(\DateTime $loggedOut = null): void
    {
        if (!$loggedOut) {
            $loggedOut = new \DateTime('now', ServerUtility::getTimezoneObject());
        }
        // Fix Timezone because Doctrine assumes persistent DateTime Objects are always UTC
        $loggedOut->setTimezone(new \DateTimeZone('UTC'));

        $this->loggedOut = $loggedOut;
    }

    /**
     * @param int $status
     * @return User
     */
    public function setStatus(int $status): User
    {

        $this->status = $status;
        return $this;
    }

    /**
     * @return int
     */
    public function getGuestClickCount(): int
    {
        return $this->guestClickCount;
    }

    /**
     * @param int $guestClickCount
     * @return User
     */
    public function setGuestClickCount(int $guestClickCount = null): User
    {
        if ($guestClickCount === null) {
            $guestClickCount = ($this->guestClickCount ?? 0) + 1;
        }
        $this->guestClickCount = $guestClickCount;
        return $this;
    }

    /**
     * @return array
     */
    public function getFiles(): array
    {
        try {
        if (App::getApp()->getContainer()['jwt']['guest'] ?? false === true) {
            return [];
        }
        } catch (\Exception $e) {
            LogHelper::warn('Error during getContainer: ' . $e->getMessage());
            return [];
        }
        return InvestUtility::getUserFiles($this->getId());
    }
}