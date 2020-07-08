<?php

/*
 * This file is part of the Witty Growth C.A. - J406095737 package.
 * 
 * (c) www.mpandco.com
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tecnoready\Common\Service\ObjectManager\StatisticManager;

use DateTime;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Tecnoready\Common\Service\ObjectManager\ConfigureInterface;
use Tecnoready\Common\Service\ObjectManager\StatisticManager\Adapter\StatisticsAdapterInterface;

/**
 * Manejador de estadisticas para el manejador de objetos (ObjectManager)
 *
 * @author Carlos Mendoza <inhack20@gmail.com>
 */
class StatisticsManager implements ConfigureInterface
{
    use \Tecnoready\Common\Service\ObjectManager\TraitConfigure;
    
    /**
     * @var PropertyAccessor 
     */
    private $propertyAccess;

    /**
     * @var StatisticsAdapterInterface
     */
    private $adapter;

    /**
     * Opciones del estadist manager
     * @var array
     */
    protected $options;

    /**
     * Adaptadores disponibles
     * @var Adapters
     */
    private $adapters;

    /**
     * Adaptador por defecto
     * @var StatisticsAdapterInterface
     */
    private $defaultAdapter;

    /**
     * Objetos validos
     * @var objectValids
     */
    private $objectValids;

    public function __construct(StatisticsAdapterInterface $adapter = null)
    {
        if (!class_exists("Symfony\Component\PropertyAccess\PropertyAccess")) {
            throw new \Exception(sprintf("The package '%s' is required, please install https://packagist.org/packages/symfony/property-access", '"symfony/property-access": "^3.1"'));
        }
        if (!class_exists("Symfony\Component\OptionsResolver\OptionsResolver")) {
            throw new \Exception(sprintf("The package '%s' is required, please install https://packagist.org/packages/symfony/options-resolver", '"symfony/options-resolver": "^3.1"'));
        }
        $builder = PropertyAccess::createPropertyAccessorBuilder();
        $builder->enableMagicCall();

        $this->propertyAccess = $builder->getPropertyAccessor();
        $this->defaultAdapter = $adapter;
    }

    /**
     * Registro de configuraciones
     * @author Máximo Sojo <maxsojo13@gmail.com>
     * @author Carlos Mendoza <inhack20@gmail.com>
     * @param  $objectId
     * @param  $objectType
     */
    public function configure($objectId, $objectType, array $options = [])
    {
        $this->adapter = $this->defaultAdapter;
        if (isset($this->adapters[$objectType])) {
            $this->adapter = $this->adapters[$objectType];
        }
        if ($this->adapter === null) {
            throw new RuntimeException(sprintf("No hay ningun adaptador configurado para '%s' en '%s' debe agregar por lo menos uno.", $objectType, StatisticsManager::class));
        }
        
        $this->objectId = $objectId;
        $this->objectType = $objectType;
        
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            "object" => null,
            "current_ip" => null,
        ]);
        $this->options = $resolver->resolve($options);

        $this->setObject($this->options["object"]);
        $this->adapter->configure($objectId, $objectType);
    }

    /**
     * Retorna las estadisticas de un mes especifico por año y dia
     * @param array $options [year,month,day]
     * @return StatisticsMonthValue
     */
    public function getStatisticsMonthValue(array $options = [])
    {
        $resolver = new OptionsResolver();
        $now = new DateTime();
        $defaults = [
            "year" => (int) $now->format("Y"),
            "month" => (int) $now->format("m"),
            "day" => (int) $now->format("d"),
        ];
        $resolver->setDefaults($defaults);
        foreach ($defaults as $option => $value) {
            $resolver->setAllowedTypes($option,"int");
        }
        $options = $resolver->resolve($options);
        
        $foundStatistics = $this->findStatisticsMonth([
            "year" => $options["year"],
            "month" => $options["month"],
        ]);
        return (int) $this->getValueDay($options["day"], $foundStatistics);
    }

    /**
     * Busca el total de un mes
     * @param array $options [year,month]
     * @return int
     */
    public function getStatisticsMonthTotal(array $options = [])
    {
        $resolver = new OptionsResolver();
        $now = new DateTime();
        $defaults = [
            "year" => (int) $now->format("Y"),
            "month" => (int) $now->format("m"),
        ];
        $resolver->setDefaults($defaults);
        foreach ($defaults as $option => $value) {
            $resolver->setAllowedTypes($option,"int");
        }
        $options = $resolver->resolve($options);
        
        $foundStatistics = $this->findStatisticsMonth($option["year"], $option["month"]);
        $total = 0;
        if ($foundStatistics !== null) {
            $total = $foundStatistics->getTotal();
        }

        return $total;
    }

    /**
     * Busca el total de un año
     * @param array $options [year]
     * @return int
     */
    public function getStatisticsYearValue(array $options = [])
    {
        $resolver = new OptionsResolver();
        $now = new DateTime();
        $defaults = [
            "year" => (int) $now->format("Y"),
        ];
        $resolver->setDefaults($defaults);
        foreach ($defaults as $option => $value) {
            $resolver->setAllowedTypes($option,"int");
        }
        $options = $resolver->resolve($options);
        
        $foundStatistics = $this->findStatisticsYear($options["year"]);
        $total = 0;
        if ($foundStatistics) {
            $total = $foundStatistics->getTotal();
        }

        return $total;
    }

    /**
     * Retorna las estadisticas de un mes por el año
     * @param array $options [year,month]
     * @return int
     */
    public function findStatisticsMonth(array $options = [])
    {
        $resolver = new OptionsResolver();
        $now = new DateTime();
        $defaults = [
            "year" => (int) $now->format("Y"),
            "month" => (int) $now->format("m"),
        ];
        $resolver->setDefaults($defaults);
        foreach ($defaults as $option => $value) {
            $resolver->setAllowedTypes($option,"int");
        }
        $options = $resolver->resolve($options);
        
        $foundStatisticsYear = $this->findStatisticsYear($options["year"]);
        $foundStatistics = null;
        if ($foundStatisticsYear !== null) {
            $foundStatistics = $foundStatisticsYear->getMonth($options["month"]);
        }

        return $foundStatistics;
    }

    /**
     * Retorna las estadisticas de un año
     * @param type $year
     * @param type $month
     * @return \Tecnoready\Common\Model\Statistics\StatisticsYearInterface
     */
    public function findStatisticsYear($year)
    {
        $year = (int) $year;
        $foundStatistics = $this->adapter->findStatisticsYear([
            "object" => $this->options["object"],
            "objectId" => $this->objectId,
            "objectType" => $this->objectType,
            "year" => $year
        ]);
        if (!$foundStatistics) {
            $foundStatistics = null;
        }

        return $foundStatistics;
    }

    /**
     * Cuenta uno a las estadisticas de un objeto por el año, mes y dia
     * @param array $options [year,month,day,value]
     * @return \Tecnoready\Common\Model\Statistics\StatisticsMonthInterface
     */
    public function countStatisticsMonth(array $options = [])
    {
        $resolver = new OptionsResolver();
        $now = new DateTime();
        $defaults = [
            "year" => (int) $now->format("Y"),
            "month" => (int) $now->format("m"),
            "day" => (int) $now->format("d"),
            "value" => null,
        ];
        $resolver->setDefaults($defaults);
        foreach ($defaults as $option => $v) {
            if(in_array($option,["$option"])){
                continue;
            }
            $resolver->setAllowedTypes($option,"int");
        }
        $options = $resolver->resolve($options);

        // Consulta de estadistica año
        $foundStatisticsYear = $this->findStatisticsYear($options["year"]);
        if ($foundStatisticsYear === null) {
            $foundStatisticsYear = $this->newYearStatistics($options["year"]);
            $this->adapter->persist($foundStatisticsYear);
        }
        $foundStatisticsMonth = $foundStatisticsYear->getMonth($options["month"]);

        $value = $options["value"];
        if ($value && is_string($value)) {
            $value = $this->getValueDay($options["day"], $foundStatisticsMonth) + intval($value);
        } elseif (!$value) {
            $value = $this->getValueDay($options["day"], $foundStatisticsMonth);
            $value++;
        }

        $this->setValueDay($foundStatisticsMonth, $options["day"], $value);
        $foundStatisticsMonth->totalize();

        //Guardo cambios en el mes (totales)
        $this->adapter->persist($foundStatisticsMonth);

        //Totalizo el valor del anio con los valores actualizados del mes.
        $foundStatisticsYear->totalize();
        $this->adapter->persist($foundStatisticsYear);
        $this->adapter->flush();

        return $foundStatisticsMonth;
    }
    
    /**
     * Retorna el resumen de las estadisticas del anio en un array
     * @param type $year
     * @return YearStatistics
     */
    public function getSummaryYear($year = null)
    {
        $summary = [];
        for ($month = 1; $month <= 12; $month++) {
            $summary[$month] = $this->getStatisticsMonthTotal($year, $month);
        }

        return $summary;
    }

    /**
     * Retorna el valor de un dia
     * @param type $day
     * @param type $foundStatistics
     * @return int
     */
    private function getValueDay($day, $foundStatistics = null)
    {
        if ($foundStatistics === null) {
            return 0;
        }
        $statisticsPropertyPath = "day" . $day;
        $value = $this->propertyAccess->getValue($foundStatistics, $statisticsPropertyPath);

        return $value;
    }

    /**
     * Establece el valor de un dia
     * @param type $foundStatistics
     * @param type $day
     * @param type $value
     */
    private function setValueDay($foundStatistics, $day, $value)
    {
        $statisticsPropertyPath = "day" . $day;
        $this->propertyAccess->setValue($foundStatistics, $statisticsPropertyPath, $value);
    }

    /**
     * Registra una nueva estadistica
     * 
     * @param  String $year
     * @return YearStatistics
     */
    private function newYearStatistics($year = null)
    {
        $now = new DateTime();
        if ($year === null) {
            $year = $now->format("Y");
        }

        $yearStatistics = $this->adapter->newYearStatistics($this);
        $yearStatistics->setYear($year);
        $yearStatistics->setCreatedAt($now);
        $yearStatistics->setObject($this->options["object"]);
        $yearStatistics->setObjectId($this->objectId);
        $yearStatistics->setObjectType($this->objectType);
        $yearStatistics->setCreatedFromIp($this->options["current_ip"]);        
        $this->adapter->persist($yearStatistics);
        for ($month = 1; $month <= 12; $month++) {
            $statisticsMonth = $this->adapter->newStatisticsMonth($this);
            $statisticsMonth->setMonth($month);
            $statisticsMonth->setYear($year);
            $statisticsMonth->setYearEntity($yearStatistics);
            $statisticsMonth->setCreatedAt($now);
            $statisticsMonth->setObject($this->options["object"]);
            $statisticsMonth->setObjectId($this->objectId);
            $statisticsMonth->setObjectType($this->objectType);
            $statisticsMonth->setCreatedFromIp($this->options["current_ip"]);            
            $yearStatistics->addMonth($statisticsMonth);
            $this->adapter->persist($statisticsMonth);
        }
        $this->adapter->flush();

        return $yearStatistics;
    }

    /**
     * Agrega un adaptador
     * @param StatisticsAdapterInterface $adapter
     */
    public function addAdapter(StatisticsAdapterInterface $adapter, $objectType)
    {
        $this->adapters[$objectType] = $adapter;
        
        return $this;
    }

    /**
     * Agrega objetos validos por tipo de objeto
     *  
     * @author Máximo Sojo <maxsojo13@gmail.com>
     * @param  $objectType
     * @param  array  $objectValids
     */
    public function addObjectValids($objectType, array $objectValids = array())
    {
        $this->objectValids[$objectType] = $objectValids;
        
        return $this;
    }

    /**
     * Registro de objeto a usar en las llamadas futuras
     * @author Máximo Sojo <maxsojo13@gmail.com>
     * @param  String $object
     */
    public function setObject($object)
    {
        if ($this->options["object"] !== null && !in_array($this->options["object"], $this->objectValids[$this->objectType])) {
            throw new InvalidArgumentException(sprintf("The object '%s' not add in object type '%s', please add. Available are %s", $this->options["object"], $this->objectType, implode(",", $this->objectValids[$this->objectType])));
        }
        $this->options["object"] = $object;
        return $this;
    }

}
