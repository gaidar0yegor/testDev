<?php

namespace App\Controller\FO\Admin;

use App\Slack\SlackConnectHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SlackController extends AbstractController
{
    /**
     * @Route("/notifications/slack", name="app_slack")
     */
    public function slack(Request $request, SlackConnectHandler $slackConnectHandler): Response
    {
        $slackConnectHandler->handleRequest($request);

        return $this->redirectToRoute('app_fo_admin_notification');
    }
}
