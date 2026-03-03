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

            $mail->addAddress($hackathon->getEmailOrganisateur());
            $mail->isHTML(true);

            $action = $isUpdate ? 'Mise à jour' : 'Création';
            $mail->Subject = "Confirmation de $action : " . $hackathon->getNom();

            $host = 'fyh.bastiengrnt.fr';
            $urlDetails = "https://" . $host . "/hackathon?id=" . $hackathon->getId();

            $dateEvent = date('d/m/Y à H:i', strtotime($hackathon->getDateEvent()));
            $prixFormat = $hackathon->getPrix() > 0 ? $hackathon->getPrix() . ' €' : 'Gratuit';
            $titreAction = $isUpdate ? 'mis à jour' : 'créé';

            $mail->Body = "
            <div style='font-family: Arial, Helvetica, sans-serif; background-color: #f4f7f6; padding: 30px 15px; color: #333333;'>
                <div style='max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05);'>
                    
                    <div style='background-color: #0d6efd; padding: 25px; text-align: center;'>
                        <h1 style='margin: 0; color: #ffffff; font-size: 24px; font-weight: bold;'>Find Your Hackathon</h1>
                    </div>
                    
                    <div style='padding: 30px 40px;'>
                        <h2 style='color: #2c3e50; margin-top: 0; font-size: 20px;'>Événement enregistré !</h2>
                        <p style='line-height: 1.6; color: #555555; font-size: 15px;'>
                            L'événement <strong>{$hackathon->getNom()}</strong> a été {$titreAction} avec succès dans notre base de données. Voici le récapitulatif :
                        </p>
                        
                        <table style='width: 100%; border-collapse: collapse; margin: 30px 0;'>
                            <tr>
                                <td style='padding: 12px 0; border-bottom: 1px solid #eeeeee; color: #777777;'><strong>Nom</strong></td>
                                <td style='padding: 12px 0; border-bottom: 1px solid #eeeeee; text-align: right; color: #333333;'>{$hackathon->getNom()}</td>
                            </tr>
                            <tr>
                                <td style='padding: 12px 0; border-bottom: 1px solid #eeeeee; color: #777777;'><strong>Date</strong></td>
                                <td style='padding: 12px 0; border-bottom: 1px solid #eeeeee; text-align: right; color: #333333;'>{$dateEvent}</td>
                            </tr>
                            <tr>
                                <td style='padding: 12px 0; border-bottom: 1px solid #eeeeee; color: #777777;'><strong>Ville</strong></td>
                                <td style='padding: 12px 0; border-bottom: 1px solid #eeeeee; text-align: right; color: #333333;'>{$hackathon->getVille()}</td>
                            </tr>
                            <tr>
                                <td style='padding: 12px 0; border-bottom: 1px solid #eeeeee; color: #777777;'><strong>Prix</strong></td>
                                <td style='padding: 12px 0; border-bottom: 1px solid #eeeeee; text-align: right; color: #333333;'>{$prixFormat}</td>
                            </tr>
                        </table>
                        
                        <div style='text-align: center; margin-top: 40px; margin-bottom: 20px;'>
                            <a href='{$urlDetails}' style='background-color: #0d6efd; color: #ffffff; text-decoration: none; padding: 14px 30px; border-radius: 6px; font-weight: bold; display: inline-block; font-size: 16px;'>
                                Voir mon événement
                            </a>
                        </div>
                    </div>
                    
                    <div style='background-color: #f8f9fa; padding: 20px; text-align: center; border-top: 1px solid #eeeeee;'>
                        <p style='margin: 0; font-size: 13px; color: #888888;'>
                            Cet e-mail a été envoyé automatiquement, merci de ne pas y répondre.
                        </p>
                    </div>
                    
                </div>
            </div>
            ";

            return $mail->send();
        } catch (Exception $e) {
            error_log("Erreur SMTP : " . $e->getMessage());
            return false;
        }
    }
}