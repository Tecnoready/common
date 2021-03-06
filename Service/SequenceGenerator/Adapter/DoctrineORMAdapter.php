<?php

/*
 * This file is part of the BtoB4Rewards package.
 * 
 * (c) www.btob4rewards.com
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tecnoready\Common\Service\SequenceGenerator\Adapter;

/**
 * Description of DoctrineORMAdapter
 *
 * @author Carlos Mendoza <inhack20@gmail.com>
 */
class DoctrineORMAdapter implements SequenceGeneratorAdapterInterface 
{
    /**
     * @var \Doctrine\ORM\QueryBuilder
     */
    private $qb;
    
    /**
     *
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;
    
    /**
     *
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    public function andWhere() {
        $args  = func_get_args();
        $this->qb->andWhere($args);
    }

    public function getOneOrNullResult() {
        return $this->qb->getQuery()->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
    }

    public function getRootAlias() {
        $aliases = $this->qb->getRootAliases();
        $alias = $aliases[0];
        return $alias;
    }

    public function like($x, $y) {
        return $this->qb->andWhere($this->qb->expr()->like($x,$y));
    }

    public function notLike($x, $y) {
        return $this->qb->andWhere($this->qb->expr()->notLike($x,$y));
    }

    public function select($select = null) {
        $this->qb->select($select);
    }
    
    /**
     * Shortcut to return the Doctrine Registry service.
     *
     * @return \Doctrine\Bundle\DoctrineBundle\Registry
     *
     * @throws \LogicException If DoctrineBundle is not available
     */
    protected function getDoctrine()
    {
        if (!$this->container->has('doctrine')) {
            throw new \LogicException('The DoctrineBundle is not registered in your application.');
        }

        return $this->container->get('doctrine');
    }
    
    public function setContainer(\Symfony\Component\DependencyInjection\ContainerInterface $container = null)
    {
         $this->container = $container;
    }

    public function createAdapter($className) {
        $alias = "p";
        $qb = $this->getDoctrine()->getManager()->createQueryBuilder($alias);
        $qb->from($className,$alias);
        $adapter = new self();
        $adapter->setQb($qb);
        return $adapter;
    }
}
