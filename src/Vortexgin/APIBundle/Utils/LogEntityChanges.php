<?php

namespace Vortexgin\APIBundle\Utils;

use Doctrine\ORM\EntityManager;
use Vortexgin\APIBundle\Entity\TableModify;

/**
 * Log Entity Changes
 *
 * @category Utils
 * @package  Vortexgin\APIBundle\Utils
 * @author   vortexgin <vortexgin@gmail.com>
 * @license  Apache 2.0
 * @link     https://github.com/vortexgin/corebundle
 */
class LogEntityChanges
{

    /**
     * Entity manager
     * 
     * @var \Doctrine\ORM\EntityManager
     */
    private $_em;

    /**
     * Construct
     * 
     * @param \Doctrine\ORM\EntityManager $entityManager Entity Manager
     * 
     * @return void
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->_em = $entityManager;
    }

    /**
     * Function to log entity changes
     * 
     * @param object $entity Entity to update
     * @param string $who    User to update
     * @param string $key    Entity ID
     * 
     * @return mixed
     */
    public function log($entity, $who, $key = null)
    {
        try {
            $modify = new TableModify();
            $uow = $this->_em->getUnitOfWork();
            $uow->computeChangeSets();
            $changeset = $uow->getEntityChangeSet($entity);
            $tableName = $this->_em->getClassMetadata(get_class($entity))->getTableName();

            $id = $key ? $key : $entity->getId();
            unset($changeset['updatedAt']);
            unset($changeset['updatedBy']);

            // Only save if there is any changes
            if (!empty($changeset)) {
                foreach ($changeset as $key => $val) {
                    if ($val[0] instanceof \DateTime) {
                        $changeset[$key][0] = $val[0]->format('Y-m-d H:i:s');
                        $changeset[$key][1] = $val[1]->format('Y-m-d H:i:s');
                    } elseif (is_object($val[0])) {
                        $changeset[$key][0] = $val[0]->getId();
                        $changeset[$key][1] = $val[1]->getId();
                    }
                }

                $modify->setContainer($tableName)
                    ->setContainerId($id)
                    ->setCreatedBy($who)
                    ->setUpdatedValue($changeset);

                $this->_em->persist($modify);
                $this->_em->flush();
            }

            return true;
        } catch (\Exception $e) {
            throw new \InvalidArgumentException('Logging Error. '.$e->getMessage(), 500);
        }
    }    
}