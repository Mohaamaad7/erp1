<?php
/*
*	MultiPDO v1.0 - Pili
*	Pattern: Singleton
*/

class MultiPDO {

    private $_PDO;

    /* Singleton */
    private static $_Instances = array();
    protected function __construct () {}

    public static function init ($params) {
        $access = 'host='.$params->host.';dbname='.$params->base.';user='.$params->user;
        $hash = md5($access);
        if(!isset(self::$_Instances[$hash])) {
            self::$_Instances[$hash] = new MultiPDO();
            self::$_Instances[$hash]->connect($params);
        }
        return self::$_Instances[$hash];
    }

    /* Connection PDO */
    private function connect ($params) {
        try {
            /* Create an instance PDO */
            $this->_PDO = new PDO('mysql:host='.$params->host.';dbname='.$params->base.';charset=utf8',
                $params->user, $params->pass);
            $this->_PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(Exception $e) { $this->exceptionSQL($e); }
    }

    /* Exception SQL */
    private static function exceptionSQL ($e) {
        echo "<p>[ERROR ".$e->getCode()."] - Une erreur SQL est survenu. Veuillez contacter l'administrateur.</p>";
        echo '<p>Erreur: '.$e->getMessage().'<br/>('.$e->getCode().')</p>';die();
    }

    /* Request */
    public function request ($sql, $queryMode=false, $callback=false) {
        try {
            if($queryMode) {
                $data = $this->_PDO->query($sql)->fetchAll(PDO::FETCH_ASSOC);
                return $data;
            } else {
                $req = $this->_PDO->exec($sql);
                return $req;
            }
        } catch(Exception $e) {
            if($callback) {
                return false;
            } else {
                $this->exceptionSQL($e);
            }
        }
    }

}
