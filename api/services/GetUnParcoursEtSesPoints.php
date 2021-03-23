<?php
// Projet TraceGPS - services web
// fichier : api/services/GetTouslesTracesUtilisateur.php
// Dernière mise à jour : 3/7/2019 par Jim

// Rôle : ce service permet à un utilisateur authentifié d'obtenir la liste de tous les utilisateurs (de niveau 1)
// Le service web doit recevoir 3 paramètres :
//     pseudo : le pseudo de l'utilisateur
//     mdp : le mot de passe de l'utilisateur hashé en sha1
//     lang : le langage du flux de données retourné ("xml" ou "json") ; "xml" par défaut si le paramètre est absent ou incorrect
// Le service retourne un flux de données XML ou JSON contenant un compte-rendu d'exécution

// Les paramètres doivent être passés par la méthode GET :
//     http://<hébergeur>/tracegps/api/GetTouslesTracesUtilisateur?pseudo=callisto&mdp=13e3668bbee30b004380052b086457b014504b3e&lang=xml

// connexion du serveur web à la base MySQL
$dao = new DAO();

// Récupération des données transmises
$pseudo = ( empty($this->request['pseudo'])) ? "" : $this->request['pseudo'];
$mdpSha1 = ( empty($this->request['mdp'])) ? "" : $this->request['mdp'];
$idTrace = ( empty($this->request['idTrace'])) ? "" : $this->request['idTrace'];
$lang = ( empty($this->request['lang'])) ? "" : $this->request['lang'];

// "xml" par défaut si le paramètre lang est absent ou incorrect
if ($lang != "json") $lang = "xml";

// initialisation du nombre de réponses

$uneTrace = null;


// La méthode HTTP utilisée doit être GET
if ($this->getMethodeRequete() != "GET")
{	$msg = "Erreur : méthode HTTP incorrecte.";
    $code_reponse = 406;
}
else {
    // Les paramètres doivent être présents
    if ( $pseudo == "" || $mdpSha1 == "" || $idTrace == "")
    {	$msg = "Erreur : données incomplètes.";
        $code_reponse = 400;
    }
    else
    {	if ( $dao->getNiveauConnexion($pseudo, $mdpSha1) == 0 )
        {
            $msg = "Erreur : authentification incorrecte.";
            $code_reponse = 401;
        }
        else
        {	// contrôle d'existence de pseudoConsulte
            $uneTrace = $dao->getUneTrace($idTrace);
            if ($uneTrace == null)
            {   $msg = "Erreur : parcours inexistant.";
                $code_reponse = 400;
            }
            else
            {   // verification autorisation consultation traces
                $idAutorise = $dao->getUnUtilisateur($pseudo)->getId();
                $idAutorisant = $uneTrace->getIdUtilisateur();
                if ($dao->autoriseAConsulter($idAutorisant, $idAutorise) == false && $idAutorise != $idAutorise)
                {
                    $msg = "Erreur : vous n'êtes pas autorisé par le propriétaire de ce parcours.";
                    $code_reponse = 400;
                }
                else
                {	  // récupération de la liste des utilisateurs à l'aide de la méthode getTouslesTracesUtilisateur de la classe DAO
                    //$unUtilisateur = $dao->getUnUtilisateur($pseudo);
                    $msg = "Données de la trace demandée";
                    $code_reponse = 200;
                }
            }
        }
    }
}
//     else
    //     {	// récupération de la liste des utilisateurs à l'aide de la méthode getTouslesTracesUtilisateur de la classe DAO
    //         $lesTracesUtilisateur = $dao->getTouslesTracesUtilisateur();

    //         // mémorisation du nombre d'utilisateurs
    //         $nbReponses = sizeof($lesTracesUtilisateur);
    
    //         if ($nbReponses == 0) {
    //             $msg = "Aucun utilisateur.";
    //             $code_reponse = 200;
    //         }
    //         else {
    //             $msg = $nbReponses . " utilisateur(s).";
    //             $code_reponse = 200;
    //         }
    //     }
    
    // ferme la connexion à MySQL :
    unset($dao);
    
    // création du flux en sortie
    if ($lang == "xml") {
        $content_type = "application/xml; charset=utf-8";      // indique le format XML pour la réponse
        $donnees = creerFluxXML($msg, $uneTrace);
    }
    else {
        $content_type = "application/json; charset=utf-8";      // indique le format Json pour la réponse
        $donnees = creerFluxJSON($msg, $uneTrace);
    }
    
    // envoi de la réponse HTTP
    $this->envoyerReponse($code_reponse, $content_type, $donnees);
    
    // fin du programme (pour ne pas enchainer sur les 2 fonctions qui suivent)
    exit;
    
    // ================================================================================================
    
    // création du flux XML en sortie
    function creerFluxXML($msg, $uneTrace)
    {
        /* Exemple de code XML
                <?xml version="1.0" encoding="UTF-8"?>
                <!--Service web GetUnParcoursEtSesPoints - BTS SIO - Lycée De La Salle - Rennes-->
                <data>
                  <reponse>Données de la trace demandée.</reponse>
                  <donnees>
                    <trace>
                      <id>2</id>
                      <dateHeureDebut>2018-01-19 13:08:48</dateHeureDebut>
                      <terminee>1</terminee>
                      <dateHeureFin>2018-01-19 13:11:48</dateHeureFin>
                      <idUtilisateur>2</idUtilisateur>
                    </trace>
                    <lesPoints>
                      <point>
                        <id>1</id>
                        <latitude>48.2109</latitude>
                        <longitude>-1.5535</longitude>
                        <altitude>60</altitude>
                        <dateHeure>2018-01-19 13:08:48</dateHeure>
                        <rythmeCardio>81</rythmeCardio>
                      </point>
                       .....................................................................................................
                      <point>
                        <id>10</id>
                        <latitude>48.2199</latitude>
                        <longitude>-1.5445</longitude>
                        <altitude>150</altitude>
                        <dateHeure>2018-01-19 13:11:48</dateHeure>
                        <rythmeCardio>90</rythmeCardio>
                      </point>
                    </lesPoints>
                  </donnees>
                </data>
         */
        
        // crée une instance de DOMdocument (DOM : Document Object Model)
        $doc = new DOMDocument();
        
        // specifie la version et le type d'encodage
        $doc->version = '1.0';
        $doc->encoding = 'UTF-8';
        
        // crée un commentaire et l'encode en UTF-8
        $elt_commentaire = $doc->createComment('Service web GetTouslesTracesUtilisateur - BTS SIO - Lycée De La Salle - Rennes');
        // place ce commentaire à la racine du document XML
        $doc->appendChild($elt_commentaire);
        
        // crée l'élément 'data' à la racine du document XML
        $elt_data = $doc->createElement('data');
        $doc->appendChild($elt_data);
        
        // place l'élément 'reponse' dans l'élément 'data'
        $elt_reponse = $doc->createElement('reponse', $msg);
        $elt_data->appendChild($elt_reponse);
        
        // traitement des utilisateurs
        //if (sizeof($lesTracesUtilisateur) > 0) {
            // place l'élément 'donnees' dans l'élément 'data'
            $elt_donnees = $doc->createElement('donnees');
            $elt_data->appendChild($elt_donnees);
            
            // place l'élément 'lesTracesUtilisateur' dans l'élément 'donnees'
            $elt_uneTrace = $doc->createElement('trace');
            $elt_donnees->appendChild($elt_uneTrace);
            
            
            //foreach ($DonnesTrace as $uneTrace)
            //{
                // crée un élément vide 'utilisateur'
                //$elt_trace = $doc->createElement('trace');
                // place l'élément 'utilisateur' dans l'élément 'lesTracesUtilisateur'
                
                // crée les éléments enfants de l'élément 'uneTrace'
            $elt_id         = $doc->createElement('id', $uneTrace->getId());
            $elt_uneTrace->appendChild($elt_id);
            
            $elt_dateHeureDebut     = $doc->createElement('dateHeureDebut', $uneTrace->getDateHeureDebut());
            $elt_uneTrace->appendChild($elt_dateHeureDebut);
            
            $elt_terminee    = $doc->createElement('terminee', $uneTrace->getTerminee());
            $elt_uneTrace->appendChild($elt_terminee);
            
            $elt_dateHeureFin     = $doc->createElement('dateHeureFin', $uneTrace->getDateHeureFin());
            $elt_uneTrace->appendChild($elt_dateHeureFin);
            
            $elt_distance     = $doc->createElement('distance', $uneTrace->getDistanceTotale());
            $elt_uneTrace->appendChild($elt_distance);
            
            $elt_idUtilisateur = $doc->createElement('idUtilisateur', $uneTrace->getIdUtilisateur());
            $elt_uneTrace->appendChild($elt_idUtilisateur);
                
                //if ($lesTracesUtilisateur->getNbTraces() > 0)
                //{   $elt_dateDerniereTrace = $doc->createElement('dateDerniereTrace', $uneTrace->getDateDerniereTrace());
                //$elt_trace->appendChild($elt_dateDerniereTrace);
                //}
            $lesPointsDeTrace = array();
                
            $lesPointsDeTrace = $uneTrace->getLesPointsDeTrace();
            
            
            $elt_lesPointsDeTrace = $doc->createElement('lesPoints');
            $elt_donnees->appendChild($elt_lesPointsDeTrace);
           
            foreach ($lesPointsDeTrace as $unPointDeTrace)
            {
            
                $elt_unPointDeTrace = $doc->createElement('point');
                $elt_lesPointsDeTrace->appendChild($elt_unPointDeTrace);
            
                $elt_id         = $doc->createElement('id', $unPointDeTrace->getId());
                $elt_unPointDeTrace->appendChild($elt_id);
            
                $elt_Latitude     = $doc->createElement('latitude', $unPointDeTrace->getLatitude());
                $elt_unPointDeTrace->appendChild($elt_Latitude);
            
                $elt_Longitude    = $doc->createElement('longitude', $unPointDeTrace->getLongitude());
                $elt_unPointDeTrace->appendChild($elt_Longitude);
            
                $elt_Altitude     = $doc->createElement('altitude', $unPointDeTrace->getAltitude());
                $elt_unPointDeTrace->appendChild($elt_Altitude);
            
                $elt_DateHeure     = $doc->createElement('dateHeure', $unPointDeTrace->getDateHeure());
                $elt_unPointDeTrace->appendChild($elt_DateHeure);
            
                $elt_RythmeCardio = $doc->createElement('rythmeCardio', $unPointDeTrace->getRythmeCardio());
                $elt_unPointDeTrace->appendChild($elt_RythmeCardio);
            }
            
    //}
    // Mise en forme finale
    $doc->formatOutput = true;
    
    // renvoie le contenu XML
    return $doc->saveXML();
}

// ================================================================================================

// création du flux JSON en sortie
function creerFluxJSON($msg, $uneTrace)
{
    /* Exemple de code JSON
            {
                "data": {
                    "reponse": "Données de la trace demandée.",
                    "donnees": {
                        "trace": {
                            "id": "2",
                            "dateHeureDebut": "2018-01-19 13:08:48",
                            "terminee: "1",
                            "dateHeureFin: "2018-01-19 13:11:48",
                            "idUtilisateur: "2"
                        }
                        "lesPoints": [
                            {
                                "id": "1",
                                "latitude": "48.2109",
                                "longitude": "-1.5535",
                                "altitude": "60",
                                "dateHeure": "2018-01-19 13:08:48",
                                "rythmeCardio": "81"
                            },
                            ..................................
                            {
                                "id"10</id>,
                                "latitude": "48.2199",
                                "longitude": "-1.5445",
                                "altitude": "150",
                                "dateHeure": "2018-01-19 13:11:48",
                                "rythmeCardio": "90"
                            }
                        ]
                    }
                }
            }
     */
    
            $lesPointsDeTrace = array();
    
            $lesPointsDeTrace = $uneTrace->getLesPointsDeTrace();

        // construction d'un tableau contenant les utilisateurs
            $trace = array();
            $trace["id"] = $uneTrace->getId();
            $trace["dateHeureDebut"] = $uneTrace->getDateHeureDebut();
            $trace["terminee"] = $uneTrace->getTerminee();
            $trace["dateHeureFin"] = $uneTrace->getDateHeureFin();
            $trace["distance"] = $uneTrace->getDistanceTotale();
            $trace["idUtilisateur"] = $uneTrace->getIdUtilisateur();
            //if ($uneTrace->getNbTraces() > 0)
            //{   $unObjetUtilisateur["dateDerniereTrace"] = $uneTrace->getDateDerniereTrace();
            //}
            
            $lesPoints = array();
            foreach ($lesPointsDeTrace as $unPointDeTrace)
            {	// crée une ligne dans le tableau
                $unObjetPoint = array();
                $unObjetPoint["id"] = $unPointDeTrace->getId();
                $unObjetPoint["latitude"] = $unPointDeTrace->getLatitude();
                $unObjetPoint["longitude"] = $unPointDeTrace->getLongitude();
                $unObjetPoint["altitude"] = $unPointDeTrace->getAltitude();
                $unObjetPoint["dateHeure"] = $unPointDeTrace->getDateHeure();
                $unObjetPoint["rythmeCardiaque"] = $unPointDeTrace->getRythmeCardio();
                //if ($uneTrace->getNbTraces() > 0)
                //{   $unObjetUtilisateur["dateDerniereTrace"] = $uneTrace->getDateDerniereTrace();
                //}
                $lesPoints[] = $unObjetPoint;
            }
            
            $elt_trace = ["trace" => $trace];
            $elt_lesPoints = ["lesPoints" => $lesPoints];
        
        // construction de l'élément "data"
            $elt_data = ["reponse" => $msg, "donnees" => $elt_trace, "lesPoints" => $elt_lesPoints];
        
        
    
    // construction de la racine
    $elt_racine = ["data" => $elt_data];
    
    // retourne le contenu JSON (l'option JSON_PRETTY_PRINT gère les sauts de ligne et l'indentation)
    return json_encode($elt_racine, JSON_PRETTY_PRINT);
}

// ================================================================================================
?>

