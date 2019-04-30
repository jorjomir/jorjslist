<?php

namespace AppBundle\Repository;

/**
 * AdRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class AdRepository extends \Doctrine\ORM\EntityRepository
{
    public function findAdsByCategory($id){
        return $this->getEntityManager()
            ->createQuery('SELECT a.id, a.title, a.summary, a.dateAdded, a.author, a.town, a.phoneNumber, a.price, a.images, a.views FROM AppBundle:Ad a JOIN AppBundle:Category c
            WITH a.categoryId=c.id WHERE a.categoryId= :id')
            ->setParameter('id', $id)
            ->getResult();
    }
    public function findAdsByUser($username){
        return $this->getEntityManager()
            ->createQuery('SELECT a.id, a.title, a.summary, a.dateAdded, a.author, a.town, 
            a.phoneNumber, a.price, a.images, a.views FROM AppBundle:Ad a JOIN AppBundle:User u
            WITH a.author=u.username WHERE a.author= :username')
            ->setParameter('username', $username)
            ->getResult();
    }
    public function search($query){
        return $this->getEntityManager()
            ->createQuery("SELECT a.id, a.description, a.title, a.summary, a.dateAdded, a.author, a.town, 
            a.phoneNumber, a.price, a.images, a.views FROM AppBundle:Ad WHERE a.description LIKE '% :query %'")
            ->setParameter('query', $query)
            ->getResult();
    }
    public function findRecentAds(){
        return $this->getEntityManager()
            ->createQuery('SELECT a.id, a.title, a.summary, a.dateAdded, a.author, a.town, a.phoneNumber, a.price, a.images, a.views FROM AppBundle:Ad a ORDER BY a.dateAdded DESC')
            ->setMaxResults(3)
            ->getResult();
    }
    public function adminRecentAds(){
        return $this->getEntityManager()
            ->createQuery('SELECT a.id, a.title, a.summary, a.dateAdded, a.author, a.town, a.phoneNumber, a.price, a.images, a.views FROM AppBundle:Ad a ORDER BY a.dateAdded DESC')
            ->getResult();
    }
}
