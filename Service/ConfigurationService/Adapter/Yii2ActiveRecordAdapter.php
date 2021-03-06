<?php

/*
 * This file is part of the BtoB4Rewards package.
 * 
 * (c) www.btob4rewards.com
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tecnoready\Common\Service\ConfigurationService\Adapter;

use Tecnoready\Common\Model\Configuration\BaseEntity\ConfigurationYii2AR;

/**
 * Adaptador de yii2
 *
 * @author Carlos Mendoza <inhack20@gmail.com>
 */
class Yii2ActiveRecordAdapter implements ConfigurationAdapterInterface 
{
    public function findAll() {
        return ConfigurationYii2AR::find()->all();
    }

    public function update($key, $value, $description,$wrapperName) {
        $entity = $this->find($key);
        if($entity === null){
            $entity = $this->createNew();
        }else{
            $entity->setUpdatedAt();
        }
        $entity->setKey($key);
        $entity->setValue($value);
        if($description != null){
            $entity->setDescription($description);
        }
        $entity->setNameWrapper($wrapperName);
        $success = $entity->save();
        return $success;
    }

    public function createNew() {
        return new ConfigurationYii2AR();
    }

    public function find($key) {
        return ConfigurationYii2AR::find()
                ->andWhere(["key" => $key])
                ->one()
                ;
    }

    public function flush() {
        
    }

}
