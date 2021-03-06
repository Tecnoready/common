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
 * Adaptador de yii2 para el generador de secuencias
 *
 * @author Carlos Mendoza <inhack20@gmail.com>
 */
class Yii2ActiveRecordAdapter implements SequenceGeneratorAdapterInterface {

    /**
     * @var \yii\db\Query
     */
    private $qb;

    public function andWhere() {
        $args = func_get_args();
        return call_user_func_array([$this->qb,"andWhere"], $args);
    }

    public function getOneOrNullResult() {
        return $this->qb->scalar();
    }

    public function getRootAlias() {
        return null;
    }

    public function like($x, $y) {
        return $this->qb->andWhere($x . " ILIKE " . $y . "");
    }

    public function notLike($x, $y) {
        return $this->qb->andWhere($x . " NOT ILIKE " . $y . "");
    }

    public function select($select = null) {
        $expression = new \yii\db\Expression($select);
        return $this->qb->select($expression);
    }

    public function setQb(\yii\db\Query $qb) {
        $this->qb = $qb;
        return $this;
    }

    public function createAdapter($className) {
        $query = $className::find();
        $adapter = new self();
        $adapter->setQb($query);
        return $adapter;
    }
}
