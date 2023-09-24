<?php
namespace alekseikovrigin\bxfullquery\Service;

use Bitrix\Main\ORM\Objectify\Collection;
use Bitrix\Main\ORM\Objectify\IdentityMap;
use Bitrix\Main\ORM\Query\Filter\ConditionTree;
use Bitrix\Main\ORM\Query\Query;

class QueryHelper
{
    public static function decompose(Query $query, $fairLimit = true, $separateRelations = true, array $runtimeAll = array(), array $runtimeSelect = array())
    {
        $entity = $query->getEntity();
        $primaryNames = $entity->getPrimaryArray();
        $originalSelect = $query->getSelect();

        if ($fairLimit)
        {
            $query->setSelect($entity->getPrimaryArray());
            $query->setDistinct();

            foreach ($runtimeSelect as $field) {
                $query->registerRuntimeField($field);
            }

            $rows = $query->fetchAll();

            if (empty($rows))
            {
                return $query->getEntity()->createCollection();
            }

            $query = $entity->getDataClass()::query();
            $query->setSelect($originalSelect);
            $query->where(static::getPrimaryFilter($primaryNames, $rows));
        }

        if ($separateRelations)
        {
            $commonSelect = [];
            $dividedSelect = [];

            foreach ($originalSelect as $selectItem)
            {
                $selQuery = $entity->getDataClass()::query();
                $selQuery->addSelect($selectItem);

                foreach ($runtimeAll as $field) {
                    $selQuery->registerRuntimeField($field);
                }
                $selQuery->getQuery(true);

                foreach ($selQuery->getChains() as $chain)
                {
                    if ($chain->hasBackReference())
                    {
                        $dividedSelect[] = $selectItem;
                        continue 2;
                    }
                }

                $commonSelect[] = $selectItem;
            }

            if (empty($commonSelect))
            {
                $commonSelect = $query->getEntity()->getPrimaryArray();
            }

            $query->setSelect($commonSelect);
        }

        foreach ($runtimeAll as $field) {
            $query->registerRuntimeField($field);
        }

        $collection = $query->fetchAll();

        if (!empty($dividedSelect) && $collection->count())
        {
            $im = new IdentityMap;
            $primaryValues = [];

            foreach ($collection as $object)
            {
                $im->put($object);

                $primaryValues[] = $object->primary;
            }

            $primaryFilter = static::getPrimaryFilter($primaryNames, $primaryValues);

            foreach ($dividedSelect as $selectItem)
            {
                $result = $entity->getDataClass()::query()
                    ->addSelect($selectItem)
                    ->where($primaryFilter)
                    ->exec();

                $result->setIdentityMap($im);
                $result->fetchAll();
            }
        }

        return $collection;
    }

    public static function getPrimaryFilter($primaryNames, $primaryValues)
    {
        $commonSubFilter = new ConditionTree();

        if (count($primaryNames) === 1)
        {
            $values = [];

            foreach ($primaryValues as $row)
            {
                $values[] = $row[$primaryNames[0]];
            }

            $commonSubFilter->whereIn($primaryNames[0], $values);
        }
        else
        {
            $commonSubFilter->logic('or');

            foreach ($primaryValues as $row)
            {
                $primarySubFilter = new ConditionTree();

                foreach ($primaryNames as $primaryName)
                {
                    $primarySubFilter->where($primaryName, $row[$primaryName]);
                }
            }
        }

        return $commonSubFilter;
    }
}
