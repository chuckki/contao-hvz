<?php

namespace Chuckki\ContaoHvzBundle\EventListener;

use Chuckki\ContaoHvzBundle\PushMeMessage;
use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\Module;
use Contao\MemberModel;
use Terminal42\ServiceAnnotationBundle\ServiceAnnotationInterface;

class ActivateAccountListener implements ServiceAnnotationInterface
{
    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * ActivateAccountListener constructor.
     */
    public function __construct(\Swift_Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * @Hook("activateAccount")
     */
    public function onActivateAccount(MemberModel $member, Module $module): void
    {

        $plainText = "Neue Registrierung von %s %s %s\n\nFirma:%s\n\nAdresse:\n%s\n%s %s\n\nMail:%s";
        $body      = sprintf(
            $plainText,
            $member->gender,
            $member->firstname,
            $member->lastname,
            $member->company,
            $member->street,
            $member->postal,
            $member->city,
            $member->email
        );
        $message   = (new \Swift_Message(
            'Neue Registrierung auf halteverbot-beantragen.de'
        ))->setFrom(
            'info@halteverbot-beantragen.de',
            'Halteverbot beantragen'
        )->setTo(
            'info@halteverbot-beantragen.de',
            'Halteverbot beantragen'
        )->setBody(
            $body
        );
        if (0 === $this->mailer->send($message)) {
            PushMeMessage::pushMe('Registeriungs-Info-Mail not Send:'.$member->email, 'iFrame');
        }

    }

}
