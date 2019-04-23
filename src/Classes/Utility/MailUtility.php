<?php

namespace Helio\Invest\Utility;

use Helio\Invest\Helper\LogHelper;
use Helio\Invest\Model\User;

class MailUtility
{


    /**
     * @var string
     */
    protected static $confirmation = <<<EOM
    Hi %s 
    Welcome to the Helio investment platform. Please click this link to log in:
    %s
EOM;

    /**
     * TODO: This is currently not really used since the emails should be personalised a little.
     * @var string
     */
    protected static $activation = <<<EOM
    Hi %s 
    You have been granted access to the Helio investment platform.
    You can log in to the platform at any times by clicking the link below.
    Please DON'T share this link with anyone! 
    %s
EOM;


    /**
     * @param User $user
     * @param string $contentVar
     *
     * @return bool
     * @throws \Exception
     */
    public static function sendConfirmationMail(User $user, string $contentVar = 'confirmation'): bool
    {
        $content = vsprintf(self::$$contentVar, [
            $user->getName(),
            ServerUtility::getBaseUrl() . 'app?token=' .
            JwtUtility::generateToken($user->getId())['token']
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