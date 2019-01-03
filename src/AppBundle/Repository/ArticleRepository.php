<?php

namespace AppBundle\Repository;

/**
 * ArticleRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ArticleRepository extends \Doctrine\ORM\EntityRepository
{
    public function findRecentArticles(){
        return $this->getEntityManager()
            ->createQuery('SELECT a.id, a.title, a.content, a.dateAdded FROM AppBundle:Article a ORDER BY a.dateAdded DESC')
            ->setMaxResults(5)
            ->getResult();
    }
}
