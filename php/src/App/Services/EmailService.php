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
        $mail->Host       = getenv('SMTP_HOST');
        $mail->SMTPAuth   = true;
        $mail->Username   = getenv('SMTP_USER');
        $mail->Password   = getenv('SMTP_PASS');
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = getenv('SMTP_PORT');
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom(getenv('SMTP_USER') ?: 'noreply@domaine.fr', 'Find Your Hackathon');

        return $mail;
    }

    public function sendHackathonConfirmation(Hackathon $hackathon, bool $isUpdate = false): bool
    {
        try {
            $mail = $this->getMailer();

            $mail->SMTPDebug = 2;

            $mail->addAddress($hackathon->getEmailOrganisateur());
            $mail->isHTML(true);

            $action = $isUpdate ? 'Mise à jour' : 'Création';
            $mail->Subject = "Confirmation de $action : " . $hackathon->getNom();

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
            echo "<pre style='background:#1e1e1e;color:#d4d4d4;padding:15px;border-radius:5px;'>";
            var_dump([
                'Erreur_PHP' => $e->getMessage(),
                'Erreur_PHPMailer' => $mail->ErrorInfo,
                'Configuration_Env' => [
                    'SMTP_HOST' => $_ENV['SMTP_HOST'] ?? 'Non défini',
                    'SMTP_PORT' => $_ENV['SMTP_PORT'] ?? 'Non défini',
                    'SMTP_USER' => $_ENV['SMTP_USER'] ?? 'Non défini',
                    'SMTP_PASS' => isset($_ENV['SMTP_PASS']) ? '(Défini, longueur: ' . strlen($_ENV['SMTP_PASS']) . ')' : 'Non défini'
                ]
            ]);
            echo "</pre>";
            die();
        }
    }
}