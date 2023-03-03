<?php
/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

/* * ***************************Includes********************************* */
require_once __DIR__ . '/../../../../core/php/core.inc.php';

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


  public function preUpdate()
  {
    if ($this->getConfiguration('ipcompteur') == '') {
      throw new Exception(__('Veuillez entrer une IP', __FILE__));
    }
  }
  public function postUpdate() {
    $this->getInformations();
  }
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
      $ecocompteurCmd->setUnite('W');
      $ecocompteurCmd->setIsVisible('1');
      $ecocompteurCmd->setIsHistorized(0);
      $ecocompteurCmd->setTemplate("mobile", 'line');
      $ecocompteurCmd->setTemplate("dashboard", 'line');
      $ecocompteurCmd->save();
    }
  }

  public function getInformations()
  {
    $devAddr = 'http://' . $this->getConfiguration('ipcompteur', '') . '/inst.json';
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
        if ($name == 'data1' || $name == 'data2' || $name == 'data3' || $name == 'data4' || $name == 'data5') {
          $this->checkCmdOk($name);
          $this->checkAndUpdateCmd($name, $value);
        }
      }
    }
    $this->refreshWidget();
  }
}

class ecocompteurCmd extends cmd
{
  /*     * *************************Attributs****************************** */

  /*
  public static $_widgetPossibility = array();
  */

  /*     * ***********************Methode static*************************** */


  /*     * *********************Methode d'instance************************* */

  /*
  * Permet d'empêcher la suppression des commandes même si elles ne sont pas dans la nouvelle configuration de l'équipement envoyé en JS
  public function dontRemoveCmd() {
  return true;
  }
  */

  // Exécution d'une commande
  public function execute($_options = array())
  {
  }

/*     * **********************Getteur Setteur*************************** */

}