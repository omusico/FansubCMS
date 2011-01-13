<?php
/*
 * This file is part of FansubCMS.
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
 * News
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    FansubCMS
 * @subpackage Models
 * @author     FansubCMS Developer <##EMAIL##>
 * @version    SVN: $Id: Builder.php 7200 2010-02-21 09:37:37Z beberlei $
 */
class News extends BaseModelNews {
	/**
	 * get News
	 * @param integer $limit
	 * @param integer $offset
	 * @param bool | null $public
	 * @return Doctrine_Collection
	 */
    static function getNews($limit,$offset,$public=true) {
        $q = Doctrine_Query::create();
        $q->from('News n')
        ->limit($limit) 
        ->offset($offset);
        if($public === false) {
        	$q->where('public = ?','no');
        } else if($public === true) {
        	$q->where('public = ?','yes');
        }
        return $q->execute();
    }

    /**
     * get one news by its id
     * @param integer $id
     * @return News
     */
    static function getNewsById($id) {
        $q = Doctrine_Query::create();
        $q->from('News n')
          ->where('id = ?',$id);
        return $q->fetchOne();
    }

    /**
     * get news for permalinks
     * @param integer $day
     * @param integer $month
     * @param integer $year
     * @param string $title
     * @return string
     */
    static function getNewsByDateAndTitle($day,$month,$year,$title) {
        $time = strtotime($year.'-'.$month.'-'.$day);
        $date = new Zend_Date($time);
        $q = Doctrine_Query::create();
        $q->from('News n')
          ->where('created_at BETWEEN ? AND ?',array(
              $date->toString('YYYY-MM-dd').' 00:00:00',
              $date->toString('YYYY-MM-dd').' 23:59:59'))
          ->andWhere('title = ?',$title);
        return $q->fetchOne();
    }

    /**
     * retrieve url parameters for permalinks or not
     * @return string
     */
    public function getUrlParams() {
        $conf = Zend_Registry::get('environmentSettings');
        if($conf->news->usePermaLink) {
            $date = new Zend_Date(strtotime($this->created_at));
            return 'archive/'.$date->toString('YYYY').'/'.$date->toString('MM').'/'.$date->toString('dd').'/'.urlencode($this->title);
        } else {
            return 'comment/index/id/'.$this->id;
        }
    }
    
    /**
     * gets comments that are not marked as spam or invisible
     * @return Doctrine_Collection
     */
    public function getComments() {

    	$q = Doctrine_Query::create();
    	$q->from('NewsComment nc')
    	  ->where('spam = ?','no')
    	  ->andWhere('visible = ?','yes')
          ->andWhere('news_id = ?',$this->id)
    	  ->orderBy('nc.created_at DESC');;
        return $q->execute();
    }
    
    /**
     * returns a Zend_Paginator_Object
     * @see Zend_Paginator
     * @param mixed $spam true spam false non-spam everything else all
     * @param mixed $visible true visible false invisible everything else all
     * @return Zend_Paginator
     */
    public function getCommentPaginator($spam=null,$visible=null) {
        $q = Doctrine_Query::create();
        $q->from('NewsComment nc')
          ->orderBy('nc.created_at DESC');
        
        if($spam === true) {
            $q->andWhere('nc.spam = ?','yes');
        } else if($spam === false) {
            $q->andWhere('nc.spam = ?','no');
        }
        if($visible === true) {
            $q->andWhere('nc.visible = ?','yes');
        } else if($visible === false) {
            $q->andWhere('nc.visible = ?','no');
        }
        $q->andWhere('nc.news_id = ?',$this->id);
        
        $adapter = new FansubCMS_Paginator_Adapter_Doctrine($q);
        return new Zend_Paginator($adapter);
    }
}