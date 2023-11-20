<?php

namespace Inetris\Nocode\Repository;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\DB\Connection;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\SystemException;
use CPHPCache;

class CachedIBlockRepository implements IBlockRepositoryInterface
{
    private $repository;
    private $cache;
    private $cacheTime;
    private $defaultCacheTime;
    private $cachePath;

    public function __construct(IBlockRepositoryInterface $repository, $defaultCacheTime, $cachePath)
    {
        $this->repository = $repository;
        $this->cache = new CPHPCache();
        $this->defaultCacheTime = $this->cacheTime = $defaultCacheTime;
        $this->cachePath = $cachePath;
    }

    /**
     * @param string $cacheIdKey
     * @param callable $callback
     * @param ...$params
     * @return array|mixed
     */
    private function getCachedData(string $cacheIdKey, callable $callback, ...$params)
    {
        $cacheId = md5(serialize($cacheIdKey));
        $cacheTime = $this->getAndResetCacheTime();

        if ($this->cache->InitCache($cacheTime, $cacheId, $this->cachePath)) {
            return $this->cache->GetVars();
        } elseif ($this->cache->StartDataCache()) {
            $result = call_user_func_array($callback, $params);
            $this->cache->EndDataCache($result);
            return $result;
        }

        return [];
    }

    /**
     * @param int $time
     * @return void
     */
    public function setCacheTime(int $time): void
    {
        $this->cacheTime = $time;
    }

    /**
     * @return int
     */
    private function getAndResetCacheTime(): int
    {
        if ($this->cacheTime == $this->defaultCacheTime) {
            return $this->defaultCacheTime;
        } else {
            $result = $this->cacheTime;
            $this->cacheTime = $this->defaultCacheTime;
            return $result;
        }
    }

    /**
     * @param array $arFilter
     * @param array $arSelect
     * @param int $pageSize
     * @return array|mixed
     */
    public function getElementsByFilter(array $arFilter, array $arSelect, int $pageSize = 50)
    {
        return $this->getCachedData(
            serialize($arFilter),
            [$this->repository, 'getElementsByFilter'],
            $arFilter,
            $arSelect,
            $pageSize
        );
    }

    /**
     * @param $iblockId
     * @return array|mixed
     */
    public function getIblockProperties($iblockId)
    {
        return $this->getCachedData(
            'getIblockProperties' . $iblockId,
            [$this->repository, 'getIblockProperties'],
            $iblockId
        );
    }

    /**
     * @param $iblockType
     * @return array|mixed
     */
    public function getIBlocksByType($iblockType)
    {
        return $this->getCachedData(
            serialize([$iblockType]),
            [$this->repository, 'getIBlocksByType'],
            $iblockType
        );
    }

    /**
     * @param $iblockId
     * @return array|mixed
     */
    public function getConditionTypesByIblock($iblockId)
    {
        return $this->getCachedData(
            serialize([$iblockId, "TYPE"]),
            [$this->repository, 'getConditionTypesByIblock'],
            $iblockId
        );
    }

    /**
     * @param Connection $connection
     * @param string $query
     * @return array|mixed
     */
    public function executeSqlQuery(Connection $connection, string $query)
    {
        return $this->getCachedData(
            serialize($query),
            [$this->repository, 'executeSqlQuery'],
            $connection,
            $query
        );
    }

    /**
     * @param Query $query
     * @return array|mixed
     * @throws ArgumentException
     * @throws SystemException
     */
    public function executeQuery(Query $query)
    {
        return $this->getCachedData(
            serialize($query->getQuery()),
            [$this->repository, 'executeQuery'],
            $query
        );
    }


}
