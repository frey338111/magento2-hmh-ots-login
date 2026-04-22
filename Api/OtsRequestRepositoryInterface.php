<?php

declare(strict_types=1);

namespace Hmh\OtsLogin\Api;

use Hmh\OtsLogin\Api\Data\OtsRequestInterface;
use Hmh\OtsLogin\Api\Data\OtsRequestSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface OtsRequestRepositoryInterface
{
    public function save(OtsRequestInterface $otsRequest): OtsRequestInterface;

    public function getById(int $entityId): OtsRequestInterface;

    public function getList(SearchCriteriaInterface $searchCriteria): OtsRequestSearchResultsInterface;

    public function delete(OtsRequestInterface $otsRequest): bool;

    public function deleteById(int $entityId): bool;
}
