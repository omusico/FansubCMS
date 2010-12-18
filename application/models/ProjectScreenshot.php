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

/**
 * ProjectScreenshot
 *
 * This class has been auto-generated by the Doctrine ORM Framework
 *
 * @package    FansubCMS
 * @subpackage Models
 * @author     FansubCMS Developer <##EMAIL##>
 * @version    SVN: $Id: Builder.php 7200 2010-02-21 09:37:37Z beberlei $
 */
class ProjectScreenshot extends BaseModelProjectScreenshot {
    public function updateScreenshot($values) {
        if(!empty($values['project']))
            $this->project_id = $values['project'];
        if($values['file'] instanceof Zend_Form_Element_File) {
            $file = $values['file']->receive();
            if(!$file) {
                throw new Zend_Exception('There was an error with the file upload.');
            }

            if(!empty($this->screenshot)) {
                $file = explode('/',$this->screenshot);
                $file = $file[count($file)-1];
                @unlink(realpath(UPLOAD_PATH.'/screenshots/').DIRECTORY_SEPARATOR.$file);
            }
            $view = Zend_Layout::getMvcInstance()->getView();
            $this->screenshot = $view->baseUrl().'/upload/screenshots/'.$values['screen'];
        }
        $this->save();
    }

    public function delete(Doctrine_Connection $conn = null) {
        $file = explode('/',$this->screenshot);
        $file = $file[count($file)-1];
        $file = realpath(UPLOAD_PATH.'/screenshots/').DIRECTORY_SEPARATOR.$file;
        if(is_file($file)) {
            unlink($file);
        } else {
            throw new Zend_Exception('File to delete does not exist.');
        }
        parent::delete($conn);
    }
}