<?php

namespace Catrobat\AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * TagRepository.
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class TagRepository extends EntityRepository
{
    public function getConstantTags($language)
    {
        $qb = $this->createQueryBuilder('e');

        return $qb
            ->select('e.'.$language)
            ->getQuery()
            ->getResult();
    }

    public function getTagsWithProgramIdAndLanguage($program_id, $language)
    {
        $qb = $this->createQueryBuilder('e');

        return $qb
            ->select('e.'.$language)
            ->leftJoin('e.programs', 'p'  )
            ->andWhere($qb->expr()->eq('p.id', ':id'))
            ->setParameter('id', $program_id)
            ->getQuery()
            ->getResult();
    }
}
