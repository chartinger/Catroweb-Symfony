<?php

namespace Catrobat\CoreBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

/**
 * ProgramRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ProgramRepository extends EntityRepository
{
  public function findByOrderedByDownloads($limit = null, $offset = null)
  {
    //return $this->findBy(array(),array('downloads' => 'desc'), $limit, $offset);
  	return $this->createQueryBuilder('e')
  	->select('e')
  	->orderBy('e.downloads', 'DESC')
  	->setFirstResult($offset)
  	->setMaxResults($limit)
  	->getQuery()
  	->getResult();
  }

  public function findByOrderedByViews($limit = null, $offset = null)
  {
    //return $this->findBy(array(),array('views' => 'desc'), $limit, $offset);
    return $this->createQueryBuilder('e')
    ->select('e')
    ->orderBy('e.views', 'DESC')
    ->setFirstResult($offset)
    ->setMaxResults($limit)
    ->getQuery()
    ->getResult();
  }
  
  public function findByOrderedByDate($limit = null, $offset = null)
  {
    //return $this->findBy(array(),array('uploaded_at' => 'desc'), $limit, $offset);
    return $this->createQueryBuilder('e')
    ->select('e')
    ->orderBy('e.uploaded_at', 'DESC')
    ->setFirstResult($offset)
    ->setMaxResults($limit)
    ->getQuery()
    ->getResult();
  }

  public function search($query, $limit=10, $offset=0)
  {
    return $this->createQueryBuilder('e')->select('e')->where($this->createQueryBuilder('e')->expr()->like('e.name','?1'))->setParameter(1,'%'.$query.'%')->orderBy('e.uploaded_at', 'DESC')->setFirstResult($offset)->setMaxResults($limit)->getQuery()->getResult();




    //    $qb = $this->entity_manager->createQuery('SELECT u FROM Catrobat\CoreBundle\Entity\User u where u.id = 1');
//    return $qb->getResult();
//    echo "asdfasdfasdfasdf \n";
//    $em = $this->program_repository->getEM();
//    echo "fffffffffffffffffffffffffffffffffffffffffff \n";
//    $qb = $em->getRepository('CatrobatCoreBundle:User');
//    //$möp = $this->entity_manager->getRepository('CatrobatCoreBundle:User');
//    // $qb = $this->program_repository->createQueryBuilder();
//    echo "wfuufasdfusoh \n";
//    return $qb->createQueryBuilder('e')->where('e.id = ?1')->setParameter(1,1)->getQuery()->getResult();

//    $sadf = array();
//    $sadf["mooo"] = "safd";
//    return $sadf["mooo"];


    //$qb = $this->program_repository->createQueryBuilder('e');
    //return $qb->select('e')->where($qb->expr()->like('e.name','?1'))->setParameter(1,'%'.$query.'%')->orderBy('e.uploaded_at', 'DESC')->setFirstResult($offset)->setMaxResults($limit)->getQuery()->getResult();
    //return $this->program_repository->findBy(array('name' => $query),array('views' => 'desc'), $limit, $offset);

  }

  
}