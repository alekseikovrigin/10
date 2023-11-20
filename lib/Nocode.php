<?php
declare(strict_types=1);

namespace Inetris\Nocode;

use Inetris\Nocode\DTO\Settings;
use Inetris\Nocode\Service\IBlockDataService;
use Inetris\Nocode\Service\QueryBuilder;
use Inetris\Nocode\Tools\Tools;

class Nocode
{
    private $dataService;
    private $queryBuilder;

    private $context;
    private $targetIblockId;
    private $iblockType = "nocode";
    private $rulesCategory;
    private $forMassMode;
    private $maxCount;

    private $selectedRules;
    private $filterIn;
    private $filterOut;

    /**
     * @param IBlockDataService $dataService
     * @param QueryBuilder $queryBuilder
     * @param Settings $settings
     */
    public function __construct(
        Service\IBlockDataService $dataService,
        Service\QueryBuilder      $queryBuilder,
        DTO\Settings              $settings
    )
    {
        $this->dataService = $dataService;
        $this->queryBuilder = $queryBuilder;
        $this->context = $settings->context;
        $this->targetIblockId = $settings->targetIblockId;
        $this->rulesCategory = $settings->rulesCategory;
        $this->dataService->cacheTime = $settings->cacheTime;
        $this->maxCount = $settings->maxCount;
        $this->forMassMode = $settings->forMassMode;
    }

    /**
     * @return $this
     */
    public function initializer(): self
    {
        $this->dataService->iblocks = $this->dataService->getIblocks($this->iblockType);
        $this->queryBuilder->conditionTypes = Tools::getIndex($this->dataService->getTypeEnumIds());

        $propertiesIblock = Tools::getParam('nocode_conditions', $this->dataService->iblocks);
        $properties = Tools::getIndex($this->dataService->getProperties($propertiesIblock));
        $this->queryBuilder->dbField = $this->dataService->assignParams($properties);

        return $this;
    }

    /**
     * @return $this
     */
    public function criterionsGetter(): self
    {
        $this->queryBuilder->criterions = $this->dataService->getCriterions();
        $this->queryBuilder->arCritExist = Tools::getIndex($this->queryBuilder->criterions);

        return $this;
    }

    /**
     * @return $this
     */
    public function operationsGetter(): self
    {
        $this->queryBuilder->operations = $this->dataService->getOperations();
        $this->queryBuilder->types = $this->dataService->getOperationTypes();
        $this->queryBuilder->operationsByType = $this->queryBuilder->mapTypes();
        return $this;
    }

    /**
     * @return $this
     */
    public function getAllRules(): self
    {
        $rules = $this->dataService->getAllRules($this->rulesCategory);
        $indexedRules = Tools::getIndex($rules);
        $selectedRules["ids"] = array_keys($indexedRules);

        $this->selectedRules = $selectedRules;
        return $this;
    }

    /**
     * @return $this
     */
    public function globalCriterionsHandler(): self
    {
        $globalCriterions = $this->queryBuilder->prepareInputParams($this->queryBuilder->arCritExist);
        if (count($globalCriterions) > 0) {
            $this->context = array_merge($this->context, $globalCriterions);
        }
        $this->queryBuilder->addHandlers();
        return $this;
    }

    /**
     * @return $this
     */
    public function inFilterHandler(): self
    {
        $iblockId = Tools::getParam('nocode_conditions', $this->dataService->iblocks);

        if (count($this->selectedRules['ids']) > 0) {
            $strSelectedRules = implode(", ", $this->selectedRules['ids']);
        } else {
            $strSelectedRules = "0";
        }
        $strFilter = $this->queryBuilder->formInFilter($this->context, $iblockId, $strSelectedRules);
        $this->filterIn = $this->dataService->getConditions($strFilter);

        return $this;
    }

    /**
     * @return $this
     */
    public function outFilterHandler(): self
    {
        $this->queryBuilder->properties = Tools::getIndex($this->dataService->getProperties($this->targetIblockId), "CODE");
        $this->filterOut = $this->queryBuilder->formOutFilter($this->context, $this->filterIn);
        $this->filterOut = $this->queryBuilder->processingOutFilter($this->filterOut, 1);
        return $this;
    }

    /**
     * @return array
     */
    public function finalResultHandler(): array
    {
        $result = array();

        if (is_array($this->filterOut)) {
            $elementId = $this->context["ID"];
            $result = $this->execute($this->filterOut, $elementId);
        }

        return $result;
    }

    public function execute(array $filterOut, ?int $elementId): array
    {
        $idField = "ID";

        $blackList = array();
        if (!$this->forMassMode) {                                        //create common cache
            $blackList[] = $elementId;
        }
        $Item_Count = $this->maxCount;


        $arResultFinal = array();

        foreach ($filterOut as $rule) {

            if (
                //isset($rule["filter"]) &&
            ($Item_Count > 0)
            ) {
                $arResult = array();

                $query = $this->queryBuilder->formQuery($this->targetIblockId, $rule, $Item_Count, $blackList);
                $result = $this->dataService->getElements($query);

                foreach ($result as $arItem) {
                    $Item_Count = $Item_Count - 1;
                    $arResult[] = $arItem[$idField];
                    $blackList[] = $arItem[$idField];
                }

                $arResultFinal = array_merge($arResultFinal, $arResult);
            }
        }

        if ($this->forMassMode) {
            Tools::removeFromArray($elementId, $arResultFinal);
        }

        return $arResultFinal;
    }

    /**
     * @return array
     */
    public function get(): array
    {
        return $this
            ->initializer()
            ->criterionsGetter()
            ->operationsGetter()
            ->getAllRules()
            ->globalCriterionsHandler()
            ->inFilterHandler()
            ->outFilterHandler()
            ->finalResultHandler();
    }
}
