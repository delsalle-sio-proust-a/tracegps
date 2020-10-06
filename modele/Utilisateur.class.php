<?php
include_once ('Outils.class.php');


class Utilisateur
{
    
    
        // ------------------------------------------------------------------------------------------------------
        // ---------------------------------- Attributs privés de la classe -------------------------------------
        // ------------------------------------------------------------------------------------------------------
        private $id; 
        private $pseudo; 
        private $mdpSha1; 
        private $adrMail; 
        private $numTel; 
        private $niveau; 
        private $dateCreation; 
        private $nbTraces; 
        private $dateDerniereTrace; 
    
        // ------------------------------------------------------------------------------------------------------
        // ----------------------------------------- Constructeur -----------------------------------------------
        // ------------------------------------------------------------------------------------------------------
        public function __construct($unId, $unPseudo, $unMdpSha1, $uneAdrMail, $unNumTel, $unNiveau,
            $uneDateCreation, $unNbTraces, $uneDateDerniereTrace) {
                
                $this-> id = $unId;
                $this-> pseudo = $unPseudo;
                $this-> mdpSha1 = $unMdpSha1;
                $this-> adrMail = $uneAdrMail;
                $this-> numTel = Outils::corrigerTelephone($unNumTel);
                $this-> niveau = $unNiveau;
                $this-> dateCreation = $uneDateCreation;
                $this-> nbTraces = $unNbTraces;
                $this-> dateDerniereTrace = $uneDateDerniereTrace;
                
        }
        
        // ------------------------------------------------------------------------------------------------------
        // ---------------------------------------- Getters et Setters ------------------------------------------
        // ------------------------------------------------------------------------------------------------------
        
        public function getId() {return $this->id;}
        public function setId($unId) {$this->id = $unId;}
        
        public function getPseudo() {return $this->pseudo;}
        public function setPseudo($unPseudo) {$this->pseudo = $unPseudo;}
        
        public function getMdpSha1() {return $this->mdpSha1;}
        public function setMdpSha1($unMdpSha1) {$this->mdpSha1 = $unMdpSha1;}
        
        public function getAdrMail() {return $this->adrMail;}
        public function setAdrMail($uneAdrMail) {$this->adrMail = $uneAdrMail;}
        
        public function getNumTel() {return $this->numTel;}
        public function setNumTel($unNumTel) {$this->numTel = Outils::corrigerTelephone($unNumTel);}
        
        public function getNiveau() {return $this->niveau;}
        public function setNiveau($unNiveau) {$this->niveau = $unNiveau;}
        
        public function getDateCreation() {return $this->dateCreation;}
        public function setDateCreation($uneDateCreation) {$this->dateCreation = $uneDateCreation;}
        
        public function getNbTraces() {return $this->nbTraces;}
        public function setNbTraces($unNbTraces) {$this->nbTraces = $unNbTraces;}
        
        public function getDateDerniereTrace() {return $this->dateDerniereTrace;}
        public function setDateDerniereTrace($uneDateDerniereTrace) {$this->dateDerniereTrace = $uneDateDerniereTrace;}
        
        // ------------------------------------------------------------------------------------------------------
        // -------------------------------------- Méthodes d&#39;instances ------------------------------------------
        // ------------------------------------------------------------------------------------------------------
        public function toString() {
            $msg = 'id : ' . $this->id . '<br>';
            $msg .= 'pseudo : ' . $this->pseudo . '<br>';
            $msg .= 'mdpSha1 : ' . $this->mdpSha1 . '<br>';
            $msg .= 'adrMail : ' . $this->adrMail . '<br>';
            $msg .= 'numTel : ' . $this->numTel . '<br>';
            $msg .= 'niveau : ' . $this->niveau . '<br>';
            $msg .= 'dateCreation : ' . $this->dateCreation . '<br>';
            $msg .= 'nbTraces : ' . $this->nbTraces . '<br>';
            $msg .= 'dateDerniereTrace : ' . $this->dateDerniereTrace . '<br>';
            return $msg;
        }
        
}