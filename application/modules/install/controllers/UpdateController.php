<?php
/*
 *  This file is part of FansubCMS.
 *
 *  FansubCMS is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  FansubCMS is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with FansubCMS.  If not, see <http://www.gnu.org/licenses/>
 */

class Install_UpdateController extends FansubCMS_Controller_Action {
    public function init() {
        $envConf = Zend_Registry::get('environmentSettings');
        if(!$envConf->install && APPLICATION_ENV != 'development') die('locked!');
        $this->_helper->layout()->disableLayout(); // no layout in the installer
    }

    public function indexAction() {
        $this->view->form = new FansubCMS_Form_Confirmation;
        if($this->request->isPost()) {
            $submit = $this->request->getParam('yes');
            if(!empty($submit)) {
                if(Install_Model_Api_Migration::getInstance()->migrateDryRun()) {
                   $this->_helper->redirector->gotoSimple('migrate','update','install');
                } else {
                    $this->view->error = $this->translate('install_migrate_error_in_dry_run');
                }
                
            } else {
                $this->_helper->redirector->gotoSimple('index','index','install');
            }
        }
    }

    public function migrateAction() {
        $this->view->form = new FansubCMS_Form_Confirmation;
        if($this->request->isPost()) {
            $submit = $this->request->getParam('yes');
            if(!empty($submit)) {
                if(Install_Model_Api_Migration::getInstance()->migrate()) {
                   $this->_helper->redirector->gotoSimple('index','index','install');
                } else {
                    $this->view->error = $this->translate('install_migrate_error_in_migrate_run');
                }

            } else {
                $this->_helper->redirector->gotoSimple('index','index','install');
            }
        }
    }
}