<?php

namespace AppBundle\Repository;

/**
 * UserRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class UserRepository extends \Doctrine\ORM\EntityRepository
{
    public function findExistingUsernames(){
        return $this->getEntityManager()
            ->createQuery('SELECT a.username FROM AppBundle:User a')
            ->getResult();
    }
    public function findExistingEmails(){
        return $this->getEntityManager()
            ->createQuery('SELECT a.email FROM AppBundle:User a')
            ->getResult();
    }
    public function getIdWithEmail($email){
        return $this->getEntityManager()
            ->createQuery('SELECT u.id, u.email, u.username, u.password, u.role FROM AppBundle:User u 
            WHERE u.email= :email')
            ->setParameter('email', $email)
            ->getResult();
    }
}
