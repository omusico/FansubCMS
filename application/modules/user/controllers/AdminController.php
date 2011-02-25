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

class User_AdminController extends FansubCMS_Controller_Action {
    public function indexAction() {
        $table = Doctrine_Core::getTable('User_Model_User');
        $this->view->users = $table->findAll()->toArray();
        if($this->acl->isAllowed($this->defaultUseRole, 'user_admin', 'edit'))
            $this->session->tableActions['user_edit'] = array('module' => 'user', 'controller' => 'admin', 'action' => 'edit');
        if($this->acl->isAllowed($this->defaultUseRole, 'user_admin', 'delete'))
            $this->session->tableActions['user_delete'] = array('module' => 'user', 'controller' => 'admin', 'action' => 'delete');
    }

    public function addAction() {
        $this->view->form = new User_Form_EditUser(array(), true);
        $req = $this->getRequest();
        if($req->isPost()) { // there are profile updates
            if($this->view->form->isValid($_POST)) {
                $values = $this->view->form->getValues();
                $u = new User;
                $u->updateProfile($values);
                $this->session->message = $this->translate('user_admin_add_success');
                $this->_helper->redirector->gotoSimple('index','admin','user');
                $this->view->form = new News_Form_EditNews(null,true);
            } else {
                $this->view->message = $this->translate('user_admin_add_failed');
            }
        }
    }

    public function deleteAction() {
        $id = $this->request->getParam('id');
        $table = Doctrine_Core::getTable('User_Model_User');
        if($id) {
            $user = $table->find($id);
            $this->view->form = new FansubCMS_Form_Confirmation();
            $this->view->confirmation = sprintf($this->translate('user_admin_delete_confirmation'),$user->name);
            if($this->request->getParam('yes') && $user) {
                $user->delete();
                $this->session->message = $this->translate('user_admin_delete_success');
                $this->_helper->redirector->gotoSimple('index', 'admin', 'user');
            } else if($this->request->getParam('no')) {
                $this->_helper->redirector->gotoSimple('index','admin','user');
            }
        } else {
            $this->session->message = $this->translate('user_not_existent');
            $this->_helper->redirector->gotoSimple('index','admin','user');
        }
    }

    public function editAction() {
        $id = $this->getRequest()->getParam('id');
        $table = Doctrine_Core::getTable('User_Model_User');
        $u = $table->findOneBy('id', $id ? $id : 0);
        if(!$u) {
            $this->session->message = $this->translate('user_not_existent');
            $this->_helper->redirector->gotoSimple('index','admin','user');
        }
        $array = $u->toArray();
        $array['User_Model_Role'] = $u->getRoles();
        $array['User_Model_Task'] = $u->getTasks();
        $this->view->form = new User_Form_EditUser($array);
        $req = $this->getRequest();
        if($req->isPost()) { // there are profile updates
            if($this->view->form->isValid($_POST)) {
                $values = $this->view->form->getValues();
                $u->updateProfile($values);
                $this->session->message = $this->translate('user_admin_edit_success');
                $this->_helper->redirector->gotoSimple('index','admin','user');
            } else {
                $this->view->message = $this->translate('user_admin_edit_failed');
            }
        }
    }

    public function profileAction() {
        $this->view->form = new User_Form_Profile('#');
        $req = $this->getRequest();
        if($req->isPost()) { // there are profile updates
            if($this->view->form->isValid($_POST)) {
                $values = $this->view->form->getValues();
                $u = Zend_Auth::getInstance()->getIdentity();
                if(!empty($values['password1'])) {
                    $u->password = $values['password1'];
                }
                $u->email = $values['email'];
                $u->description = $values['description'];
                $u->save();
            }
        }
    }

    public function tasksAction() {
        $table = Doctrine_Core::getTable('User_Model_Task');
        $this->view->tasks = $table->findAll()->toArray();
        if($this->acl->isAllowed($this->defaultUseRole, 'user_admin', 'taskedit'))
            $this->session->tableActions['user_edit_task'] = array('module' => 'user', 'controller' => 'admin', 'action' => 'edittask');
        if($this->acl->isAllowed($this->defaultUseRole, 'user_admin', 'taskdelete'))
            $this->session->tableActions['user_delete_task'] = array('module' => 'user', 'controller' => 'admin', 'action' => 'deletetask');
    }

    public function addtaskAction() {
        $this->view->form = new User_Form_EditTask(array(),true);
        $req = $this->getRequest();
        if($req->isPost()) { // there are profile updates
            if($this->view->form->isValid($_POST)) {
                $values = $this->view->form->getValues();
                $t = new User_Model_Task;
                $t->name = $values['taskname'];
                $t->save();
                $this->session->message = $this->translate('user_admin_taskadd_success');
                $this->_helper->redirector->gotoSimple('tasks','admin','user');
            } else {
                $this->view->message = $this->translate('user_admin_taskadd_failed');
            }
        }
    }

    public function deletetaskAction() {
        $id = $this->request->getParam('id');
        $table = Doctrine_Core::getTable('User_Model_Task');
        if($id) {
            $t = $table->find($id);
            $this->view->form = new FansubCMS_Form_Confirmation();
            $this->view->confirmation = sprintf($this->translate('user_admin_taskdelete_confirmation'),$t->name);
            if($this->request->getParam('yes') && $t) {
                $t->delete();
                $this->session->message = $this->translate('user_admin_taskdelete_success');
                $this->_helper->redirector->gotoSimple('tasks', 'admin', 'user');
            } else if($this->request->getParam('no')) {
                $this->_helper->redirector->gotoSimple('tasks','admin','user');
            }
        } else {
            $this->session->message = $this->translate('user_admin_task_not_existent');
            $this->_helper->redirector->gotoSimple('tasks','admin','user');
        }
    }

    public function edittaskAction() {
        $id = $this->getRequest()->getParam('id');
        $table = Doctrine_Core::getTable('User_Model_Task');
        $t = $table->findOneBy('id', $id ? $id : 0);
        if(!$t) {
            $this->session->message = $this->translate('user_admin_task_not_existent');
            $this->_helper->redirector->gotoSimple('tasks','admin','user');
        }
        $this->view->form = new User_Form_EditTask($t->toArray());
        $req = $this->getRequest();
        if($req->isPost()) { // there are profile updates
            if($this->view->form->isValid($_POST)) {
                $values = $this->view->form->getValues();
                $t->name = $values['taskname'];
                $t->save();
                $this->session->message = $this->translate('user_admin_taskedit_success');
                $this->_helper->redirector->gotoSimple('tasks','admin','user');
            } else {
                $this->view->message = $this->translate('user_admin_taskedit_failed');
            }
        }
    }
}
