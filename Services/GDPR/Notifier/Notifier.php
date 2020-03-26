<?php

namespace CanalTP\SamCoreBundle\Services\GDPR\Notifier;

use Psr\Log\LogLevel;
use Psr\Log\LoggerInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Translation\TranslatorInterface;
use CanalTP\SamEcoreUserManagerBundle\Entity\User;
use CanalTP\SamCoreBundle\Entity\Customer;

abstract class Notifier
{
    /**
     * @var ObjectManager
     */
    protected $om;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var TwigEngine
     */
    protected $templating;

    /**
     * @var \Swift_Mailer
     */
    protected $mailer;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    public function __construct(
        ObjectManager $om,
        LoggerInterface $logger,
        TwigEngine $templating,
        \Swift_Mailer $mailer,
        TranslatorInterface $translator
    ) {
        $this->om = $om;
        $this->logger = $logger;
        $this->templating = $templating;
        $this->mailer = $mailer;
        $this->translator = $translator;
    }

    protected function logActionOnUser(User $user, $message, $level)
    {
        $customerName = ($user->getCustomer() instanceof Customer) ? $user->getCustomer()->getName() : 'not found';
        $msg = sprintf(
            'Client %s User ID %s %s ',
            str_pad($customerName . ':', 20, ' '),
            str_pad($user->getId() . ':', 3, ' '),
            $message
        );

        $this->logger->log($level, $msg);
    }

    protected function sendEmailToUser(User $user, $subject, $body)
    {
        $to = $user->getEmailCanonical();
        $this->logger->debug('Sending email to ' . $to);

        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom('support@kisio.com')
            ->setTo($to)
            ->setReplyTo('support@kisio.com')
            ->setContentType('text/html')
            ->setBody($body);

        $result = $this->mailer->send($message);

        if ($result === 0) {
            throw new \RuntimeException('Unable to send email to ' . $to);
        }

        $this->logActionOnUser($user, 'email has been sent', LogLevel::INFO);
    }
}
