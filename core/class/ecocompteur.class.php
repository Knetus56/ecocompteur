<?php

require_once __DIR__ . '/../../../../core/php/core.inc.php';

/////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////
class ecocompteur extends eqLogic
{

  public static function cron()
  {
    foreach (self::byType('ecocompteur') as $compteur) {
      if ($compteur->getIsEnable() == 1) {
        $compteur->getInformations();
      }
    }
  }
  /////////////////////////////////////////////////////////////////////////////////////////////////////
  /////////////////////////////////////////////////////////////////////////////////////////////////////
  public function preUpdate()
  {
    if ($this->getConfiguration('ipcompteur') == '') {
      throw new Exception(__('Veuillez entrer une IP', __FILE__));
    }
  }
  /////////////////////////////////////////////////////////////////////////////////////////////////////
  /////////////////////////////////////////////////////////////////////////////////////////////////////
  public function postUpdate()
  {
    $this->getInformations();
  }
  /////////////////////////////////////////////////////////////////////////////////////////////////////
  /////////////////////////////////////////////////////////////////////////////////////////////////////
  public function checkCmdOk($_name)
  {
    $ecocompteurCmd = ecocompteurCmd::byEqLogicIdAndLogicalId($this->getId(), $_name);
    if (!is_object($ecocompteurCmd)) {
      log::add('ecocompteur', 'debug', 'Création de la commande ' . $_name);
      $ecocompteurCmd = new ecocompteurCmd();
      $ecocompteurCmd->setName(__($_name, __FILE__));
      $ecocompteurCmd->setEqLogic_id($this->getId());
      $ecocompteurCmd->setEqType('ecocompteur');
      $ecocompteurCmd->setLogicalId($_name);
      $ecocompteurCmd->setType('info');
      $ecocompteurCmd->setSubType('numeric');
      $ecocompteurCmd->setUnite('w');
      $ecocompteurCmd->setIsVisible('1');
      $ecocompteurCmd->setIsHistorized(0);
      $ecocompteurCmd->setTemplate("mobile", 'line');
      $ecocompteurCmd->setTemplate("dashboard", 'line');
      $ecocompteurCmd->save();
    }
  }
  /////////////////////////////////////////////////////////////////////////////////////////////////////
  /////////////////////////////////////////////////////////////////////////////////////////////////////
  public function getInformations()
  {
    $devAddr = 'http://' . $this->getConfiguration('ipcompteur', '') . '/compt.json';
    $request_http = new com_http($devAddr);
    $devResult = $request_http->exec(30);
    log::add('ecocompteur', 'debug', 'getInformations ' . $devAddr);
    if ($devResult === false) {
      log::add('ecocompteur', 'info', 'problème de connexion ' . $devAddr);
    } else {
      $devResbis = utf8_encode($devResult);
      $devList = json_decode($devResbis, true);
      log::add('ecocompteur', 'debug', print_r($devList, true));
      foreach ($devList as $name => $value) {
        $this->checkCmdOk($name);
        $this->checkAndUpdateCmd($name, (int)$value);
      }
    }
    $this->refreshWidget();
  }
}
/////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////
class ecocompteurCmd extends cmd
{
  public function execute($_options = array())
  {
  }
}
