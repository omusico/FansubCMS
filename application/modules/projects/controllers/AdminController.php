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

class Projects_AdminController extends FansubCMS_Controller_Action 
{
    public function indexAction() 
    {
        $this->view->pageTitle = $this->translate('project_list_headline');
        $table = Doctrine_Core::getTable('Projects_Model_Project');

        $this->view->query = $table->createQuery();
    }

    public function addAction() 
    {
        $this->view->pageTitle = $this->translate('project_add_headline');
        $this->view->form = new Projects_Form_EditProject(array(), true);
        $req = $this->getRequest();
        if($req->isPost()) { // there are profile updates
            if($this->view->form->isValid($_POST)) {
                $values = $this->view->form->getValues();
                $p = new Projects_Model_Project;
                $p->updateProject($values);
                $this->session->message = $this->translate('project_admin_add_success');
                $this->_helper->redirector->gotoSimple('index','admin','projects');
            } else {
                $this->view->message = $this->translate('project_admin_add_failed');
            }
        }
    }

    public function deleteAction() 
    {
        $this->view->pageTitle = $this->translate('project_delete_headline');
        $id = $this->request->getParam('id');
        $table = Doctrine_Core::getTable('Projects_Model_Project');
        if($id) {
            $p = $table->find($id);
            $this->view->form = new FansubCMS_Form_Confirmation();
            $this->view->confirmation = sprintf($this->translate('project_admin_delete_confirmation'),$p->name);
            if($this->request->getParam('yes') && $p) {
                $p->delete();
                $this->session->message = $this->translate('project_admin_delete_success');
                $this->_helper->redirector->gotoSimple('index', 'admin', 'projects');
            } else if($this->request->getParam('no')) {
                $this->_helper->redirector->gotoSimple('index','admin','projects');
            }
        } else {
            $this->session->message = $this->translate('project_not_existent');
            $this->_helper->redirector->gotoSimple('index','admin','projects');
        }
    }

    public function editAction() 
    {
        $this->view->pageTitle = $this->translate('project_edit_headline');
        $id = $this->getRequest()->getParam('id');
        $table = Doctrine_Core::getTable('Projects_Model_Project');
        $p = $table->findOneBy('id', $id ? $id : 0);
        if(!$p) {
            $this->session->message = $this->translate('project_not_existent');
            $this->_helper->redirector->gotoSimple('index','admin','projects');
        }
        $pArr = $p->toArray();
        $pArr['Projects_Model_Leader'] = $p->getLeaders();
        
        $this->view->form = new Projects_Form_EditProject($pArr);
        $req = $this->getRequest();
        if($req->isPost()) { // there are profile updates
            if($this->view->form->isValid($_POST)) {
                $values = $this->view->form->getValues();
                $p->updateProject($values);
                $this->session->message = $this->translate('project_admin_edit_success');
                $this->session->message_type = 'message';
                $this->_helper->redirector->gotoSimple('index','admin','projects');
            } else {
                $this->view->message = $this->translate('project_admin_edit_failed');
                $this->session->message_type = 'type';
            }
        }
    }

    public function episodesAction() 
    {
        $id = $this->request->getParam('id');

        if(!empty($id)) {
            $project = Doctrine_Query::create()->from('Projects_Model_Project p')->where('p.id = ?', $id)->fetchOne();
            
            $this->view->pageTitle = sprintf($this->translate('project_admin_episodes_headline'),$project->name);
            $this->view->all = false;
        } else {
            $this->view->pageTitle = sprintf($this->translate('project_admin_episodes_headline_2'));
            $this->view->all = true;
        }
        
        $table = Doctrine_Core::getTable('Projects_Model_Episode');
        
        $query = $table->createQuery();
        $query->select('*, title as name, updated_by as updater, p.name as project')
        ->leftJoin('Projects_Model_Episode.Projects_Model_Project p')
        ->orderBy('p.name ASC, Projects_Model_Episode.number ASC, Projects_Model_Episode.container ASC');
        if (! empty($id)) {
            $query->where('Projects_Model_Episode.project_id = ?', $id);
        }
        
        $this->view->query = $query;
    }

    public function addepisodeAction() 
    {
        $this->view->pageTitle = $this->translate('project_addepisode_headline');
        $this->view->form = new Projects_Form_EditProjectEpisode(array(), true);
        $req = $this->getRequest();
        if($req->isPost()) { // there are profile updates
            if($this->view->form->isValid($_POST)) {
                $values = $this->view->form->getValues();
                $p = new Projects_Model_Episode();
                $p->updateEpisode($values);
                $this->session->message = $this->translate('project_admin_addepisode_success');
                $this->_helper->redirector->gotoSimple('episodes','admin','projects');
            } else {
                $this->view->message = $this->translate('project_admin_addepisode_failed');
            }
        }
    }

    public function deleteepisodeAction() 
    {
        $this->view->pageTitle = $this->translate('project_deleteepisode_headline');
        $id = $this->request->getParam('id');
        $table = Doctrine_Core::getTable('Projects_Model_Episode');
        if($id) {
            $p = $table->find($id);
            $this->view->form = new FansubCMS_Form_Confirmation();
            $this->view->confirmation = sprintf($this->translate('project_admin_deleteepisode_confirmation'),$p->title);
            if($this->request->getParam('yes') && $p) {
                $p->delete();
                $this->session->message = $this->translate('project_admin_deleteepisode_success');
                $this->_helper->redirector->gotoSimple('episodes', 'admin', 'projects');
            } else if($this->request->getParam('no')) {
                $this->_helper->redirector->gotoSimple('episodes','admin','projects');
            }
        } else {
            $this->session->message = $this->translate('project_episode_not_existent');
            $this->_helper->redirector->gotoSimple('episodes','admin','projects');
        }
    }

    public function editepisodeAction() 
    {
        $this->view->pageTitle = $this->translate('project_editepisode_headline');
        $id = $this->getRequest()->getParam('id');
        $table = Doctrine_Core::getTable('Projects_Model_Episode');
        $p = $table->findOneBy('id', $id ? $id : 0);
        if(!$p) {
            $this->session->message = $this->translate('project_episode_not_existent');
            $this->_helper->redirector->gotoSimple('episodes','admin','projects');
        }
        $this->view->form = new Projects_Form_EditProjectEpisode($p->toArray());
        $req = $this->getRequest();
        if($req->isPost()) { // there are profile updates
            if($this->view->form->isValid($_POST)) {
                $values = $this->view->form->getValues();
                $p->updateEpisode($values);
                $this->session->message = $this->translate('project_admin_editepisode_success');
                $this->_helper->redirector->gotoSimple('episodes','admin','projects');
            } else {
                $this->view->message = $this->translate('project_admin_editepisode_failed');
            }
        }
    }

    public function screenshotsAction() 
    {
        $this->view->pageTitle = $this->translate('project_screenshots_headline');
        $table = Doctrine_Core::getTable('Projects_Model_Screenshot');
        $id = $this->request->getParam('id', false);
        
        $query = $table->createQuery()
                ->select('id, description, screenshot as screen, p.name as project')
                ->leftJoin('Projects_Model_Screenshot.Projects_Model_Project p')
                ->orderBy('p.name ASC, id ASC');
                
        if($id) {
            $query->where('p.id = ?', $id);
        }

        $this->view->query = $query;
    }

    public function addscreenshotAction() 
    {
        $this->view->pageTitle = $this->translate('project_addscreenshot_headline');
        $this->view->form = new Projects_Form_EditProjectScreenshot(array(), true);
        $req = $this->getRequest();
        if($req->isPost()) { // there are profile updates
            if($this->view->form->isValid($req->getPost())) {
                $values = $this->view->form->getValues();
                $values['file'] = $this->view->form->getElement('screen');
                $p = new Projects_Model_Screenshot();
                $p->updateScreenshot($values);
                $this->session->message = $this->translate('project_admin_addscreenshot_success');
                $this->session->message_type = 'message';
                $this->_helper->redirector->gotoSimple('screenshots','admin','projects');
            } else {
                $this->view->message = $this->translate('project_admin_addscreenshot_failed');
                $this->view->message_type = 'error';
            }
        }
    }

    public function deletescreenshotAction() 
    {
        $this->view->pageTitle = $this->translate('project_deletescreenshot_headline');
        $id = $this->request->getParam('id');
        $table = Doctrine_Core::getTable('Projects_Model_Screenshot');
        if($id) {
            $p = $table->find($id);
            $this->view->form = new FansubCMS_Form_Confirmation();
            $this->view->confirmation = sprintf($this->translate('project_admin_deletescreenshot_confirmation'), $p->Projects_Model_Project->name, $p->screenshot);
            if($this->request->getParam('yes') && $p) {
                $p->delete();
                $this->session->message = $this->translate('project_admin_deletescreenshot_success');
                $this->_helper->redirector->gotoSimple('screenshots', 'admin', 'projects');
            } else if($this->request->getParam('no')) {
                $this->_helper->redirector->gotoSimple('screenshots','admin','projects');
            }
        } else {
            $this->session->message = $this->translate('project_screenshot_not_existent');
            $this->_helper->redirector->gotoSimple('screenshots','admin','projects');
        }
    }

    public function editscreenshotAction() 
    {
        $this->view->pageTitle = $this->translate('project_editscreenshot_headline');
        $id = $this->getRequest()->getParam('id');
        $table = Doctrine_Core::getTable('Projects_Model_Screenshot');
        $p = $table->findOneBy('id', $id ? $id : 0);
        if(!$p) {
            $this->session->message = $this->translate('project_screenshot_not_existent');
            $this->_helper->redirector->gotoSimple('screenshots','admin','projects');
        }
        $this->view->form = new Projects_Form_EditProjectScreenshot($p->toArray());
        $req = $this->getRequest();
        if($req->isPost()) { // there are profile updates
            if($this->view->form->isValid($_POST)) {
                $values = $this->view->form->getValues();
                $values['file'] = $this->view->form->getElement('screen');
                $p->updateScreenshot($values);
                $this->session->message = $this->translate('project_admin_editscreenshot_success');
                $this->_helper->redirector->gotoSimple('screenshots','admin','projects');
            } else {
                $this->view->message = $this->translate('project_admin_editscreenshot_failed');
            }
        }
    }
    
    public function teamAction()
    {
        $id = $this->getRequest()->getParam('id');
        $table = Doctrine_Core::getTable('Projects_Model_Project');
        $p = $table->findOneBy('id', $id ? $id : 0);
        if(!$p) {
            $this->session->message = $this->translate('project_not_existent');
            $this->_helper->redirector->gotoSimple('index','admin','projects');
        }
        
        $this->checkAllowed($p, 'team');
        
        $this->view->pageTitle = sprintf($this->translate('project_team_headline'), $p->name);
        
        $leaders = array();
        foreach($p->Projects_Model_Leader as $leader) {
            $leaders[] = $leader->User_Model_User;
        }
        
        $this->view->leaders = $leaders;
        $this->view->team = $p->Projects_Model_User;
        
        $this->view->project = $p;
    }
    
    public function addteammemberAction()
    {
        $id = $this->getRequest()->getParam('pid');
        $table = Doctrine_Core::getTable('Projects_Model_Project');
        $p = $table->findOneBy('id', $id ? $id : 0);
        if(!$p) {
            $this->session->message = $this->translate('project_not_existent');
            $this->_helper->redirector->gotoSimple('team','admin','projects');
        }
        
        $this->checkAllowed($p, 'addteammember');
        $this->view->pageTitle = sprintf($this->translate('project_addteammember_headline'), $p->name);
        
        $form = new Projects_Form_EditTeamMember($p, array(), true);
        
        if($this->request->isPost()) {
            if($form->isValid($_POST)) {
                $values = $form->getValues();
                
                $pu = new Projects_Model_User();
                $pu->project_id = $p->id;
                $pu->user_id = $values['user'];
                $pu->function = $values['function'];
                $pu->save();
                
                $this->session->message = $this->translate('project_admin_addteammember_success');
                $this->_helper->redirector->gotoSimple('team','admin','projects', array('id' => $id));
            } else {
                $this->view->message = $this->translate('project_admin_addteammember_failed');
            }
        }
        
        $this->view->form = $form;
    }
    
    public function editteammemberAction()
    {
        $pid = $this->getRequest()->getParam('pid', 0);
        $uid = $this->request->getParam('uid', 0);
        $table = Doctrine_Core::getTable('Projects_Model_User');
        $pu = $table->createQuery('pu')
                ->leftJoin('pu.Projects_Model_Project p')
                ->leftJoin('pu.User_Model_User u')
                ->where('pu.project_id = ?', $pid)
                ->andWhere('pu.user_id = ?', $uid)
                ->fetchOne();
        if(!$pu) {
            $this->session->message = $this->translate('project_not_existent');
            $this->_helper->redirector->gotoSimple('team','admin','projects');
        }
        $this->checkAllowed($pu->Projects_Model_Project, 'editteammember');
        $this->view->pageTitle = sprintf($this->translate('project_editteammember_headline'), $pu->Projects_Model_Project->name);
        
        $this->view->user = $pu->User_Model_User;
        
        $form = new Projects_Form_EditTeamMember($pu->Projects_Model_Project, $pu->toArray(), false);
        
        if($this->request->isPost()) {
            if($form->isValid($_POST)) {
                $values = $form->getValues();

                $pu->function = $values['function'];
                $pu->save();
                
                $this->session->message = $this->translate('project_admin_editteammember_success');
                $this->_helper->redirector->gotoSimple('team','admin','projects', array('id' => $pid));
            } else {
                $this->view->message = $this->translate('project_admin_editteammember_failed');
            }
        }
        
        $this->view->form = $form;
    }
    
    public function deleteteammemberAction()
    {
        
        $pid = $this->getRequest()->getParam('pid', 0);
        $uid = $this->request->getParam('uid', 0);
        $table = Doctrine_Core::getTable('Projects_Model_User');
        $pu = $table->createQuery('pu')
                ->leftJoin('pu.Projects_Model_Project p')
                ->leftJoin('pu.User_Model_User u')
                ->where('pu.project_id = ?', $pid)
                ->andWhere('pu.user_id = ?', $uid)
                ->fetchOne();
        if(!$pu) {
            $this->session->message = $this->translate('project_not_existent');
            $this->_helper->redirector->gotoSimple('index','admin','projects');
        }
        $this->checkAllowed($pu->Projects_Model_Project, 'deleteteammember');
        
        $this->view->pageTitle = sprintf($this->translate('project_deleteteammember_headline'), $pu->Projects_Model_Project->name);

        $this->view->form = new FansubCMS_Form_Confirmation();
        $this->view->confirmation = sprintf($this->translate('project_admin_deleteteammember_confirmation'), $pu->Projects_Model_Project->name, $pu->User_Model_User->name);
        if($this->request->getParam('yes') && $pu) {
            $pu->delete();
            $this->session->message = $this->translate('project_admin_deleteteammember_success');
            $this->_helper->redirector->gotoSimple('team', 'admin', 'projects', array('id' => $pid));
        } else if($this->request->getParam('no')) {
             $this->_helper->redirector->gotoSimple('team','admin','projects', array('id' => $pid));
        }
    }
}