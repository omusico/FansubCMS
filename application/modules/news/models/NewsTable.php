<?php

/**
 * News_Model_NewsTable
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class News_Model_NewsTable extends Doctrine_Table
{
    /**
     * Returns an instance of this class.
     *
     * @return object News_Model_NewsTable
     */
    public static function getInstance()
    {
        return Doctrine_Core::getTable('News_Model_News');
    }
    
    /**
     * returns a Zend_Paginator_Object
     * 
     * @see Zend_Paginator
     * @param mixed $type true public false non-public everything else all
     * @param integer $hydrate The hydration mode to use
     * @return Zend_Paginator
     */
    public function getPaginator ($type, $hydrate = Doctrine_Core::HYDRATE_RECORD)
    {
        $q = $this->createQuery('n');
        $q->leftJoin('n.User_Model_User u')
          ->leftJoin('n.News_Model_Comment c')
          ->orderBy('publish_date DESC');
        if ($type === true) {
            $q->where('public = ?', 'yes')
                ->andWhere('publish_date <= NOW()');
        } else 
            if ($type === false) {
                $q->where('public = ?', 'no');
            }
        $adapter = new FansubCMS_Paginator_Adapter_Doctrine($q, $hydrate);
        return new Zend_Paginator($adapter);
    }
}