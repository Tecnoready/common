<?php

namespace Tecnoready\Common\Model\Traits\ORM;

/**
 * Trait para referencia de la implementacion de:
 * Tecnoready\Common\Service\SequenceGenerator\ItemReferenceInterface
 * @author Carlos Mendoza <inhack20@gmail.com>
 */
trait ItemUniqueReferenceTrait
{
    /**
     * Referencia del item
     * @var string 
     * @ORM\Column(type="string",length=30,nullable=false,unique=true)
     */
    protected $ref;
    
    public function getRef()
    {
        return $this->ref;
    }

    public function setRef($ref)
    {
        $this->ref = $ref;
        return $this;
    }
}
