<?php
// Projet TraceGPS - services web
// fichier :  api/services/DemanderUneAutorisation.php
// Dernière mise à jour : 17/11/2020 par alexandre

// Rôle : ce service web permet à un utilisateur destinataire d'accepter ou de rejeter une demande d'autorisation provenant d'un utilisateur demandeur
// il envoie un mail au demandeur avec la décision de l'utilisateur destinataire

// Le service web doit être appelé avec 4 paramètres obligatoires dont les noms sont volontairement non significatifs :
//    a : le mot de passe (hashé) de l'utilisateur destinataire de la demande ($mdpSha1)
//    b : le pseudo de l'utilisateur destinataire de la demande ($pseudoAutorisant)
//    c : le pseudo de l'utilisateur source de la demande ($pseudoAutorise)
//    d : la decision 1=oui, 0=non ($decision)

// Les paramètres doivent être passés par la méthode GET :
//     http://<hébergeur>/tracegps/api/ValiderDemandeAutorisation?a=13e3668bbee30b004380052b086457b014504b3e&b=oxygen&c=europa&d=1

// ces variables globales sont définies dans le fichier modele/parametres.php
global $ADR_MAIL_EMETTEUR, $ADR_SERVICE_WEB;

// connexion du serveur web à la base MySQL
$dao = new DAO();

// Récupération des données transmises
$pseudo = ( empty($this->request['pseudo'])) ? "" : $this->request['pseudo'];
$mdpSha1 = ( empty($this->request['mdp'])) ? "" : $this->request['mdp'];
$pseudoARetirer = ( empty($this->request['pseudoARetirer'])) ? "" : $this->request['pseudoARetirer'];
$txtMessage = ( empty($this->request['texteMessage'])) ? "" : $this->request['texteMessage'];
$nomPrenom = ( empty($this->request['nomPrenom'])) ? "" : $this->request['nomPrenom'];
$lang = ( empty($this->request['lang'])) ? "" : $this->request['lang'];

// La méthode HTTP utilisée doit être GET
if ($this->getMethodeRequete() != "GET")
{	$msg = "Erreur : méthode HTTP incorrecte.";
    $code_reponse = 406;
}
else
{
    // Les paramètres doivent être présents et corrects
    if ( $mdpSha1 == "" || $pseudoARetirer == "" || $pseudo == "")
    {	$message = "Erreur : données incomplètes.";
        $code_reponse = 400;
    }
    else
    {	// test de l'authentification de l'utilisateur
        // la méthode getNiveauConnexion de la classe DAO retourne les valeurs 0 (non identifié) ou 1 (utilisateur) ou 2 (administrateur)
        $niveauConnexion = $dao->getNiveauConnexion($pseudo, $mdpSha1);
        
        if ( $niveauConnexion == 0 )
        {   $message = "Erreur : authentification incorrecte.";
            $code_reponse = 401;
        }
        else
        {
            if ($dao->existePseudoUtilisateur($pseudoARetirer) == false)
            {
                $message = "Erreur : pseudo utilisateur inexistant";
                $code_reponse = 401;
            }
            else
            {
                $utilisateurDemandeur = $dao->getUnUtilisateur($pseudo);
                $utilisateurDestinataire = $dao->getUnUtilisateur($pseudoARetirer);
                $idDest = $utilisateurDestinataire->getId();
                $idDem = $utilisateurDemandeur->getId();
                $adrMailDestinataire = $utilisateurDestinataire->getAdrMail();
                
                $ok = $dao->supprimerUneAutorisation($idDem, $idDest);
                $msg = "Autorisation supprimée.";
                if ( ! $ok ) {
                    $msg = "Erreur : l'autorisation n'était pas accordée.";
                    $code_reponse = 500; 
                }
                else
                {    // envoi d'un mail de suppression
                    $sujetMail = "Demande de suppression de la part d'un utilisateur du système TraceGPS";
                    $contenuMail = "Cher ou chère " . $pseudoARetirer . "\n\n";
                    $contenuMail .= "Un utilisateur du système traceGPS vous retire l'autorisation de suivre ses parcours.\n";
                    
                    $contenuMail .= "Son message :".$txtMessage."\n\n";
                    
                    $contenuMail .= "Cordialement."."\n\n";
                    $contenuMail .= "L'administrateur du système TraceGPS";
                    
                    $ok2 = Outils::envoyerMail($adrMailDestinataire, $sujetMail, $contenuMail, $ADR_MAIL_EMETTEUR);
                    if ( ! $ok2 ) {
                        $message = "Erreur : l'envoi du courriel de suppression d'autorisation a rencontré un problème.";
                        $code_reponse = 500;
                    }
                    else {
                        $message = $pseudoARetirer." va recevoir un courriel avec votre demande.";
                        $code_reponse = 200;
                    }
                    
                    
                }
            }
        }
    }
}

unset($dao);   // ferme la connexion à MySQL
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Validation TraceGPS</title>
	<style type="text/css">body {font-family: Arial, Helvetica, sans-serif; font-size: small;}</style>
</head>
<body>
	<p><?php echo $message; ?></p>
	<p><a href="Javascript:window.close();">Fermer</a></p>
</body>
</html>