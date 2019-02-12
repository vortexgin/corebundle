<?php

namespace Vortexgin\LibraryBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Vortexgin\LibraryBundle\Utils\Doctrine\ORM\FilterGenerator;

/**
 * BaseRepository
 * 
 * @category Repository
 * @package  Vortexgin\LibraryBundle\Repository
 * @author   Tommy <vortexgin@gmail.com>
 * @license  Apache 2.0 (https://opensource.org/licenses/Apache-2.0)
 * @link     https://bitbucket.org/dailysocial/zeus-core
 * 
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class Base extends ServiceEntityRepository
{
    
    /**
     * Construct
     * 
     * @param \Symfony\Bridge\Doctrine\RegistryInterface $registry Doctrine registry
     * @param Object                                     $class    Object entity
     */
    public function __construct(RegistryInterface $registry, $class)
    {
        parent::__construct($registry, $class);
    }

    /**
     * Find custom
     * 
     * @return array
     */
    public function findCustom(array $param = array(), array $order = array('er.id' => 'DESC'), $limit = 10, $offset = 0, array $joins = array())
    {
        $queryBuilder = $this->createQueryBuilder('er')
            ->where('er.isActive = :active')
            ->setParameter('active', true);
        
        if (!empty($joins)) {
            foreach ($joins as $join=>$alias) {
                $queryBuilder->join($join, $alias);
            }
        }
        
        $queryBuilder = FilterGenerator::generate($queryBuilder, $param);
        foreach ($order as $key=>$value) {
            $queryBuilder->orderBy($key, $value);
        }

        $queryBuilder->setFirstResult($offset);
        $queryBuilder->setMaxResults($limit);
        $sql = $queryBuilder->getQuery();
        
        return $sql->getResult();
    }

    /**
     * Count custom
     * 
     * @return int
     */
    public function countCustom(array $param = array(), array $joins = array())
    {
        $queryBuilder = $this->createQueryBuilder('er')
            ->select('count(er.id)')
            ->andWhere('er.isActive = :active')
            ->setParameter('active', true);

        if (!empty($joins)) {
            foreach ($joins as $join=>$alias) {
                $queryBuilder->join($join, $alias);
            }
        }
            
        $queryBuilder = FilterGenerator::generate($queryBuilder, $param);
        $sql = $queryBuilder->getQuery();

        return $sql->getSingleScalarResult();
    }
}
