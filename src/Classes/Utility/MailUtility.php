<?php

namespace Helio\Invest\Utility;

use Helio\Invest\Helper\LogHelper;
use Helio\Invest\Model\User;

class MailUtility
{


    /**
     * @var string
     */
    protected static $confirmationMailContent = <<<EOM
    Hi %s 
    Welcome to helio investors platform. Please click this link to log in:
    %s
EOM;


    /**
     * @param User $user
     * @param string $linkLifetime
     *
     * @return bool
     * @throws \Exception
     */
    public static function sendConfirmationMail(User $user, string $linkLifetime = '+1 year'): bool
    {
        $content = vsprintf(self::$confirmationMailContent, [
            $user->getName(),
            ServerUtility::getBaseUrl() . 'app?token=' .
            JwtUtility::generateToken($user->getId(), $linkLifetime)['token']
        ]);

        $return = ServerUtility::get('SITE_ENV', 'PROD') !== 'TEST' ? @mail($user->getEmail(), 'Welcome to Helio', $content, 'From: hello@idling.host', '-f hello@idling.host') : true;
        if ($return) {
            LogHelper::info('Sent Confirmation Mail to ' . $user->getEmail());
        } else {
            LogHelper::warn('Failed to sent Mail to ' . $user->getEmail() . '. Reason: ' . $return);
        }

        // write mail to PHPStorm Console
        if (PHP_SAPI === 'cli-server' && ServerUtility::get('SITE_ENV') !== 'PROD') {
            LogHelper::logToConsole($content);
        }

        return $return;
    }


    /**
     * @param string $content
     * @return bool
     */
    public static function sendMailToAdmin(string $content = ''): bool
    {

        // write mail to PHPStorm Console
        if (PHP_SAPI === 'cli-server' && ServerUtility::get('SITE_ENV') !== 'PROD') {
            LogHelper::logToConsole('Admin Mail: ' . $content);
        }

        return @mail('team@helio.exchange', 'Admin Notification from Invest Platform', $content, 'From: no-reply@helio.exchange', '-f no-reply@helio.exchange');
    }
}