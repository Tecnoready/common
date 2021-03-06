<?php

/*
 * This file is part of the TecnoCreaciones package.
 * 
 * (c) www.tecnocreaciones.com
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tecnoready\Common\Service\SequenceGenerator;
/**
 *
 * @author Carlos Mendoza <inhack20@gmail.com>
 */
interface SequenceGeneratorServiceInterface 
{
    /**
     * Retorna las clases que esta manejando el generador de secuencia
     */
    public function getClassMap();
}
