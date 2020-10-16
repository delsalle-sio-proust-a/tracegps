<?php
// Projet TraceGPS
// fichier : modele/Trace.class.php
// Rôle : la classe Trace représente une trace ou un parcours
// Dernière mise à jour : 9/9/2019 par JM CARTRON

include_once ('Point.class.php');
include_once ('PointDeTrace.class.php');

class Trace
{
    // ------------------------------------------------------------------------------------------------------
    // ---------------------------------- Attributs privés de la classe -------------------------------------
    // ------------------------------------------------------------------------------------------------------
    
    private $id;				// identifiant de la trace
    private $dateHeureDebut;		// date et heure de début
    private $dateHeureFin;		// date et heure de fin
    private $terminee;			// true si la trace est terminée, false sinon
    private $idUtilisateur;		// identifiant de l'utilisateur ayant créé la trace
    private $lesPointsDeTrace;		// la collection (array) des objets PointDeTrace formant la trace
    // ------------------------------------------------------------------------------------------------------
    // ----------------------------------------- Constructeur -----------------------------------------------
    // ------------------------------------------------------------------------------------------------------
    
    public function __construct($unId, $uneDateHeureDebut, $uneDateHeureFin, $terminee, $unIdUtilisateur) {
        $this->lesPointsDeTrace = array();
        $this -> id = $unId;
        $this -> dateHeureDebut = $uneDateHeureDebut;
        $this -> dateHeureFin = $uneDateHeureFin;
        $this -> terminee = $terminee;
        $this -> idUtilisateur = $unIdUtilisateur;
    }
    
    
    // ------------------------------------------------------------------------------------------------------
    // ---------------------------------------- Getters et Setters ------------------------------------------
    // ------------------------------------------------------------------------------------------------------
    
    public function getId() {return $this->id;}
    public function setId($unId) {$this->id = $unId;}
    
    public function getDateHeureDebut() {return $this->dateHeureDebut;}
    public function setDateHeureDebut($uneDateHeureDebut) {$this->dateHeureDebut = $uneDateHeureDebut;}
    
    public function getDateHeureFin() {return $this->dateHeureFin;}
    public function setDateHeureFin($uneDateHeureFin) {$this->dateHeureFin= $uneDateHeureFin;}
    
    public function getTerminee() {return $this->terminee;}
    public function setTerminee($terminee) {$this->terminee = $terminee;}
    
    public function getIdUtilisateur() {return $this->idUtilisateur;}
    public function setIdUtilisateur($unIdUtilisateur) {$this->idUtilisateur = $unIdUtilisateur;}
    
    public function getLesPointsDeTrace() {return $this->lesPointsDeTrace;}
    public function setLesPointsDeTrace($lesPointsDeTrace) {$this->lesPointsDeTrace = $lesPointsDeTrace;}
    
    // Fournit une chaine contenant toutes les données de l'objet
    public function toString() {
        $msg = "Id : " . $this->getId() . "<br>";
        $msg .= "Utilisateur : " . $this->getIdUtilisateur() . "<br>";
        if ($this->getDateHeureDebut() != null) {
            $msg .= "Heure de début : " . $this->getDateHeureDebut() . "<br>";
        }
        if ($this->getTerminee()) {
            $msg .= "Terminée : Oui  <br>";
        }
        else {
            $msg .= "Terminée : Non  <br>";
        }
        $msg .= "Nombre de points : " . $this->getNombrePoints() . "<br>";
        if ($this->getNombrePoints() > 0) {
            if ($this->getDateHeureFin() != null) {
                $msg .= "Heure de fin : " . $this->getDateHeureFin() . "<br>";
            }
            $msg .= "Durée en secondes : " . $this->getDureeEnSecondes() . "<br>";
            $msg .= "Durée totale : " . $this->getDureeTotale() . "<br>";
            $msg .= "Distance totale en Km : " . $this->getDistanceTotale() . "<br>";
            $msg .= "Dénivelé en m : " . $this->getDenivele() . "<br>";
            $msg .= "Dénivelé positif en m : " . $this->getDenivelePositif() . "<br>";
            $msg .= "Dénivelé négatif en m : " . $this->getDeniveleNegatif() . "<br>";
            $msg .= "Vitesse moyenne en Km/h : " . $this->getVitesseMoyenne() . "<br>";
            $msg .= "Centre du parcours : " . "<br>";
            $msg .= "   - Latitude : " . $this->getCentre()->getLatitude() . "<br>";
            $msg .= "   - Longitude : "  . $this->getCentre()->getLongitude() . "<br>";
            $msg .= "   - Altitude : " . $this->getCentre()->getAltitude() . "<br>";
        }
        return $msg;
    }
    
    public function getNombrePoints(){
        $nbrePoints = sizeof($this->lesPointsDeTrace);
        return $nbrePoints;
    }
    
    public function getCentre(){
        if (sizeof($this->lesPointsDeTrace) == 0) return null;
        
        $lePremierPoint = $this->lesPointsDeTrace[0];
        $latMax = $lePremierPoint->getLatitude();
        $longMax = $lePremierPoint->getLongitude();
        $latMin = $lePremierPoint->getLatitude();
        $longMin = $lePremierPoint->getLongitude();
        
        for($i=0; $i<sizeof($this->lesPointsDeTrace); $i++){
            $lePoint = $this->lesPointsDeTrace[$i];
            
            if($latMax < $lePoint->getLatitude()){
                $latMax = $lePoint->getLatitude();
            }
            if($longMax < $lePoint->getLongitude()){
                $longMax = $lePoint->getLongitude();
            }
            if($latMin > $lePoint->getLatitude()){
                $latMin = $lePoint->getLatitude();
            }
            if($longMin > $lePoint->getLongitude()){
                $longMin = $lePoint->getLongitude();
            }
        
            $leCentre = new Point ((($latMin + $latMax)/2), (($longMin+$longMax)/2), 0);
        
        }
        return $leCentre;
    }
    
    public function getDenivele() {
        
        if (sizeof($this->lesPointsDeTrace) == 0) return 0;
        
        $lePremierPoint = $this->lesPointsDeTrace[0];
        
        $altMax = $lePremierPoint->getAltitude();
        $altMin = $lePremierPoint->getAltitude();
        
        for($i=0; $i<sizeof($this->lesPointsDeTrace); $i++){
            $lePoint = $this->lesPointsDeTrace[$i];
            
            if($altMax < $lePoint->getAltitude()){
                $altMax = $lePoint->getAltitude();
            }
            if($altMin > $lePoint->getAltitude()){
                $altMin = $lePoint->getAltitude();
            }
            
    }
    $ecart = $altMax - $altMin;
    return $ecart;
}

    
    public function getDureeEnSecondes(){
        if (sizeof($this->lesPointsDeTrace) == 0) return 0;
        
        $duree = strtotime($this->dateHeureFin)-strtotime($this->dateHeureDebut);
        return (int)$duree;
}
    
    public function getDureeTotale(){
        $tps = $this->getDureeEnSecondes();
        $heures = $tps/3600;
        $reste = $tps % 3600;
        $minutes = $reste / 60;
        $secondes = $reste % 60;
        
        return sprintf("%02d",$heures).":".sprintf("%02d",$minutes).":".sprintf("%02d",$secondes);
        
    }
    public function getDistanceTotale(){
        if (sizeof($this->lesPointsDeTrace) == 0) return 0;
        
        $leDernierPoint = $this->lesPointsDeTrace[sizeof($this->lesPointsDeTrace) -1];
        
        $dist = $leDernierPoint->getDistanceCumulee();
        return $dist;
    }
    
    public function getDenivelePositif(){
        if (sizeof($this->lesPointsDeTrace) == 0) return 0;
        
        $total = 0;
        
        for($i=1; $i<sizeof($this->lesPointsDeTrace); $i++){
            $lePoint = $this->lesPointsDeTrace[$i];
            $lePointAvant = $this->lesPointsDeTrace[$i-1];
            
            if($lePoint->getAltitude() > $lePointAvant->getAltitude()){
                $total += $lePoint->getAltitude() - $lePointAvant->getAltitude();
            }
        }
        return $total;
    }
    
    public function getDeniveleNegatif(){
        if (sizeof($this->lesPointsDeTrace) == 0) return 0;
        
        $total = 0;
        
        for($i=1; $i<sizeof($this->lesPointsDeTrace); $i++){
            $lePoint = $this->lesPointsDeTrace[$i];
            $lePointAvant = $this->lesPointsDeTrace[$i-1];
            
            if($lePoint->getAltitude() < $lePointAvant->getAltitude()){
                $total += $lePointAvant->getAltitude() - $lePoint->getAltitude();
            }
        }
        return $total;
    }
    
    public function getVitesseMoyenne(){
        if (sizeof($this->lesPointsDeTrace) == 0) return 0;
        
        $dist = $this->getDistanceTotale();
        $tps = $this->getDureeEnSecondes() / 3600;
        
        $vit = $dist / $tps;
        return $vit;
    }
    
    public function ajouterPoint($unPoint){
        if (sizeof($this->lesPointsDeTrace) == 0)
        {
            $unPoint->setDistanceCumulee(0);
            $unPoint->setTempsCumule(0);
            $unPoint->setVitesse(0);
        }
        else{

            $leDernierPoint = $this->lesPointsDeTrace[sizeof($this->lesPointsDeTrace) -1];
            $distance = Point::getDistance($leDernierPoint, $unPoint);
            $duree = strtotime($unPoint->getDateHeure())-strtotime($leDernierPoint->getDateHeure());
        
            $vitesse = $distance / $duree;
            
            $unPoint->setDistanceCumulee($leDernierPoint->getDistanceCumulee() + $distance);
            $unPoint->setTempsCumule($leDernierPoint->getTempsCumule() + $duree);
            $unPoint->setVitesse($vitesse);
            
            
        }
        $this->lesPointsDeTrace[] = $unPoint;
        $this->dateHeureFin = $unPoint->getDateHeure();
    }
    
    public function viderListePoint(){
        $this->lesPointsDeTrace->clear();
    }
}
    