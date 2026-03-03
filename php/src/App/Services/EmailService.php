<?php

namespace App\Services;

use App\Models\Hackathon;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailService
{
    /**
     * @throws Exception
     */
    private function getMailer(): PHPMailer
    {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = $_ENV['SMTP_HOST'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['SMTP_USER'];
        $mail->Password   = $_ENV['SMTP_PASS'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = $_ENV['SMTP_PORT'];
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom($_ENV['SMTP_USER'], 'Find Your Hackathon');

        return $mail;
    }

    public function sendHackathonConfirmation(Hackathon $hackathon, bool $isUpdate = false): bool
    {
        try {
            $mail = $this->getMailer();
            $mail->addAddress($hackathon->getEmailOrganisateur());
            $mail->isHTML(true);

            $action = $isUpdate ? 'Mise à jour' : 'Création';
            $mail->Subject = "Confirmation de $action : " . $hackathon->getNom();

            // On s'assure de fournir une URL absolue
            $host = 'fyh.bastiengrnt.fr';
            $urlDetails = "https://" . $host . "/hackathon?id=" . $hackathon->getId();

            $mail->Body = "
                <h2>Votre Hackathon a bien été enregistré !</h2>
                <ul>
                    <li><strong>Nom :</strong> {$hackathon->getNom()}</li>
                    <li><strong>Date :</strong> {$hackathon->getDateEvent()}</li>
                    <li><strong>Ville :</strong> {$hackathon->getVille()}</li>
                    <li><strong>Prix :</strong> {$hackathon->getPrix()} €</li>
                </ul>
                <p>Retrouvez la page de votre événement ici : <a href='{$urlDetails}'>Voir mon événement</a></p>
            ";

            return $mail->send();
        } catch (Exception $e) {
            error_log("Erreur d'envoi d'email : " . $e->getMessage());
            return false;
        }
    }
}