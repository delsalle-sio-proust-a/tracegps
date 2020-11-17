<?php
// Projet TraceGPS - services web
// fichier :  api/services/DemanderMdp.php
// Dernière mise à jour : 3/7/2019 par Jim

// Les paramètres doivent être passés par la méthode GET :
//     http://<hébergeur>/tracegps/api/ChangerDeMdppseudo=europa&mdp=13e3668bbee30b004380052b086457b014504b3e&nouveauMdp=123&confirmationMdp=123&lang=xml

// connexion du serveur web à la base MySQL
$dao = new DAO();

// Récupération des données transmises
$pseudo = ( empty($this->request['pseudo'])) ? "" : $this->request['pseudo'];
$mdpSha1 = ( empty($this->request['mdp'])) ? "" : $this->request['mdp'];
$lang = ( empty($this->request['lang'])) ? "" : $this->request['lang'];

// "xml" par défaut si le paramètre lang est absent ou incorrect
if ($lang != "json") $lang = "xml";

// La méthode HTTP utilisée doit être GET
if ($this->getMethodeRequete() != "GET")
{ $msg = "Erreur : méthode HTTP incorrecte.";
$code_reponse = 406;
}
else {
    if ( $pseudo == ""|| $mdpSha1 == "") {
        $msg = "Erreur : données incomplètes.";
        $code_reponse = 400;
    }
    else{
        // contrôle d'existence de pseudo
        if ( $dao->getNiveauConnexion($pseudo, $mdpSha1) == 0 ) {
            $msg = "Erreur : authentification incorrecte.";
            $code_reponse = 401;
            }
        else {
            // création d'un mot de passe aléatoire de 8 caractères
            $password = Outils::creerMdp();
            // enregistrement du mdp dans la BDD
            $ok = $dao->modifierMdpUtilisateur($pseudo, $password);
            if ( ! $ok ) {
                $msg = "Erreur : problème lors de l'enregistrement.";
                $code_reponse = 500;
            }
            else {
                // envoi d'un mail de confirmation de l'enregistrement
                $sujet = "Demande mot de passe";
                $contenuMail = "Vous venez de demander un nouveau mot de passe.\n\n";
                $contenuMail .= "Les données enregistrées sont :\n\n";
                $contenuMail .= "Votre pseudo : " . $pseudo . "\n";
                $contenuMail .= "Votre mot de passe : " . $password . " (changement lors de la prochaine connexion)\n";
                
                // cette variable globale est définie dans le fichier modele/parametres.php
                global $ADR_MAIL_EMETTEUR;
                $adrMail = $dao->getUnUtilisateur($pseudo)->getAdrMail();
                $ok = Outils::envoyerMail($adrMail, $sujet, $contenuMail, $ADR_MAIL_EMETTEUR);
                if (  $ok ) {
                    // tout a bien fonctionné
                    $msg = "Trace créée";
                    $code_reponse = 201;
                }
            }
        }
    }
}

// ferme la connexion à MySQL :
unset($dao);

// création du flux en sortie
if ($lang == "xml") {
    $content_type = "application/xml; charset=utf-8";      // indique le format XML pour la réponse
    $donnees = creerFluxXML ($msg);
}
else {
    $content_type = "application/json; charset=utf-8";      // indique le format Json pour la réponse
    $donnees = creerFluxJSON ($msg);
}

// envoi de la réponse HTTP
$this->envoyerReponse($code_reponse, $content_type, $donnees);

// fin du programme (pour ne pas enchainer sur les 2 fonctions qui suivent)
exit;

// ================================================================================================

// création du flux XML en sortie
function creerFluxXML($msg, $lesUtilisateurs)
{
    /* Exemple de code XML
     <?xml version="1.0" encoding="UTF-8"?>
     <!--Service web GetTousLesUtilisateurs - BTS SIO - Lycée De La Salle - Rennes-->
     <data>
     <reponse>2 utilisateur(s).</reponse>
     <donnees>
     <lesUtilisateurs>
     <utilisateur>
     <id>2</id>
     <pseudo>callisto</pseudo>
     <adrMail>delasalle.sio.eleves@gmail.com</adrMail>
     <numTel>22.33.44.55.66</numTel>
     <niveau>1</niveau>
     <dateCreation>2018-08-12 19:45:23</dateCreation>
     <nbTraces>2</nbTraces>
     <dateDerniereTrace>2018-01-19 13:08:48</dateDerniereTrace>
     </utilisateur>
     <utilisateur>
     <id>3</id>
     <pseudo>europa</pseudo>
     <adrMail>delasalle.sio.eleves@gmail.com</adrMail>
     <numTel>22.33.44.55.66</numTel>
     <niveau>1</niveau>
     <dateCreation>2018-08-12 19:45:23</dateCreation>
     <nbTraces>0</nbTraces>
     </utilisateur>
     </lesUtilisateurs>
     </donnees>
     </data>
     */
    
    // crée une instance de DOMdocument (DOM : Document Object Model)
    $doc = new DOMDocument();
    
    // specifie la version et le type d'encodage
    $doc->version = '1.0';
    $doc->encoding = 'UTF-8';
    
    // crée un commentaire et l'encode en UTF-8
    $elt_commentaire = $doc->createComment('Service web DemarrerEnregistrementParcours - BTS SIO - Lycée De La Salle - Rennes');
    // place ce commentaire à la racine du document XML
    $doc->appendChild($elt_commentaire);
    
    // crée l'élément 'data' à la racine du document XML
    $elt_data = $doc->createElement('data');
    $doc->appendChild($elt_data);
    
    // place l'élément 'reponse' dans l'élément 'data'
    $elt_reponse = $doc->createElement('reponse', $msg);
    $elt_data->appendChild($elt_reponse);
    
    // traitement des utilisateurs
    if (sizeof($lesUtilisateurs) > 0) {
        // place l'élément 'donnees' dans l'élément 'data'
        $elt_donnees = $doc->createElement('donnees');
        $elt_data->appendChild($elt_donnees);
        
        // place l'élément 'lesUtilisateurs' dans l'élément 'donnees'
        $elt_lesUtilisateurs = $doc->createElement('lesUtilisateurs');
        $elt_donnees->appendChild($elt_lesUtilisateurs);
        
        foreach ($lesUtilisateurs as $unUtilisateur)
        {
            // crée un élément vide 'utilisateur'
            $elt_utilisateur = $doc->createElement('utilisateur');
            // place l'élément 'utilisateur' dans l'élément 'lesUtilisateurs'
            $elt_lesUtilisateurs->appendChild($elt_utilisateur);
            
            // crée les éléments enfants de l'élément 'utilisateur'
            $elt_id         = $doc->createElement('id', $unUtilisateur->getId());
            $elt_utilisateur->appendChild($elt_id);
            
            $elt_pseudo     = $doc->createElement('pseudo', $unUtilisateur->getPseudo());
            $elt_utilisateur->appendChild($elt_pseudo);
            
            $elt_adrMail    = $doc->createElement('adrMail', $unUtilisateur->getAdrMail());
            $elt_utilisateur->appendChild($elt_adrMail);
            
            $elt_numTel     = $doc->createElement('numTel', $unUtilisateur->getNumTel());
            $elt_utilisateur->appendChild($elt_numTel);
            
            $elt_niveau     = $doc->createElement('niveau', $unUtilisateur->getNiveau());
            $elt_utilisateur->appendChild($elt_niveau);
            
            $elt_dateCreation = $doc->createElement('dateCreation', $unUtilisateur->getDateCreation());
            $elt_utilisateur->appendChild($elt_dateCreation);
            
            $elt_nbTraces   = $doc->createElement('nbTraces', $unUtilisateur->getNbTraces());
            $elt_utilisateur->appendChild($elt_nbTraces);
            
            if ($unUtilisateur->getNbTraces() > 0)
            {   $elt_dateDerniereTrace = $doc->createElement('dateDerniereTrace', $unUtilisateur->getDateDerniereTrace());
            $elt_utilisateur->appendChild($elt_dateDerniereTrace);
            }
        }
    }
    // Mise en forme finale
    $doc->formatOutput = true;
    
    // renvoie le contenu XML
    return $doc->saveXML();
}
// ================================================================================================

// création du flux JSON en sortie
function creerFluxJSON($msg)
{
    /* Exemple de code JSON
     {
     "data": {
     "reponse": "Erreur : authentification incorrecte."
     }
     }
     */
    
    // construction de l'élément "data"
    $elt_data = ["reponse" => $msg];
    
    // construction de la racine
    $elt_racine = ["data" => $elt_data];
    
    // retourne le contenu JSON (l'option JSON_PRETTY_PRINT gère les sauts de ligne et l'indentation)
    return json_encode($elt_racine, JSON_PRETTY_PRINT);
}

// ================================================================================================
?>