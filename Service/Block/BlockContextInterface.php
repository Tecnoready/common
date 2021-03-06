<?php

namespace Tecnoready\Common\Service\Block;

use Tecnoready\Common\Model\Block\BlockInterface;

/**
 *
 * @author Carlos Mendoza <inhack20@gmail.com>
 */
interface BlockContextInterface
{
    /**
     * @return BlockInterface
     */
    public function getBlock();

    /**
     * @return array
     */
    public function getSettings();

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function getSetting($name);

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @return BlockContextInterface
     */
    public function setSetting($name, $value);

    /**
     * @return string
     */
    public function getTemplate();
}
