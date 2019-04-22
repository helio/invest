<?php

namespace Helio\Invest\Controller;

use Helio\Invest\Controller\Traits\AuthenticatedController;
use Helio\Invest\Controller\Traits\TypeBrowserController;
use Helio\Invest\Utility\InvestUtility;
use Helio\Invest\Utility\ServerUtility;
use Psr\Http\Message\ResponseInterface;

/**
 *
 *
 * @package    Helio\Invest\Controller
 * @author    Christoph Buchli <team@opencomputing.cloud>
 *
 * @RoutePrefix('/app')
 */
class InvestController extends AbstractController
{

    use TypeBrowserController;
    use AuthenticatedController;

    /**
     * The mode is used to determine where to look for templates
     *
     * @return string
     */
    protected function getMode(): ?string
    {
        return 'invest';
    }

    /**
     * @return ResponseInterface
     * @Route("", methods={"GET"})
     */
    public function indexAction(): ResponseInterface
    {
        return $this->render(['user' => $this->user, 'files' => InvestUtility::getSharedFiles()]);
    }

    /**
     * @param string $file
     * @return ResponseInterface
     * @Route("/get/file/{file:[\w\d\.\/]+}", methods={"GET"})
     */
    public function getFileAction(string $file): ResponseInterface
    {
        if (strpos($file, 'personal') === 0) {
            $file = $this->user->getId() . substr($file, 8);
        }

        return ServerUtility::provideFileForDownload($file, $this->response);

    }
}