<?php

namespace Helio\Invest\Utility;

class InvestUtility
{

    /**
     * @return array
     */
    public static function getSharedFiles(): array
    {
        $filesInDir = [];
        foreach (scandir(ServerUtility::getApplicationRootPath(['assets']), 0) as $node) {
            if (
                strpos($node, '.') !== 0
                && is_file(ServerUtility::getApplicationRootPath(['assets']) . DIRECTORY_SEPARATOR . $node)
            ) {
                $filesInDir[] = $node;
            }
        }
        return $filesInDir;
    }

    /**
     * @param int $userId
     * @return array
     */
    public static function getUserFiles(int $userId): array
    {
        $filesInDir = [];
        foreach (scandir(ServerUtility::getApplicationRootPath(['assets', $userId]), 0) as $node) {
            if (
                strpos($node, '.') !== 0
                && is_file(ServerUtility::getApplicationRootPath(['assets', $userId]) . DIRECTORY_SEPARATOR . $node)
            ) {
                $filesInDir[] = $node;
            }
        }
        return $filesInDir;
    }

    /**
     * @param int $id
     * @return bool
     */
    public static function createUserDir(int $id): bool
    {
        return mkdir(ServerUtility::getApplicationRootPath(['assets', $id]), 0750, true);
    }
}