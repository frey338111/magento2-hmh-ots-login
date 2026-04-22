<?php

declare(strict_types=1);

namespace Hmh\OtsLogin\Model;

use Hmh\OtsLogin\Api\Data\OtsRequestInterface;
use Hmh\OtsLogin\Api\Data\OtsRequestSearchResultsInterface;
use Hmh\OtsLogin\Api\Data\OtsRequestSearchResultsInterfaceFactory;
use Hmh\OtsLogin\Api\OtsRequestRepositoryInterface;
use Hmh\OtsLogin\Model\ResourceModel\OtsRequest as OtsRequestResource;
use Hmh\OtsLogin\Model\ResourceModel\OtsRequest\CollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class OtsRequestRepository implements OtsRequestRepositoryInterface
{
    public function __construct(
        private readonly OtsRequestResource $resource,
        private readonly OtsRequestFactory $otsRequestFactory,
        private readonly CollectionFactory $collectionFactory,
        private readonly OtsRequestSearchResultsInterfaceFactory $searchResultsFactory,
        private readonly CollectionProcessorInterface $collectionProcessor
    ) {
    }

    public function save(OtsRequestInterface $otsRequest): OtsRequestInterface
    {
        try {
            $this->resource->save($otsRequest);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
                __('Could not save OTS request: %1', $exception->getMessage()),
                $exception
            );
        }

        return $otsRequest;
    }

    public function getById(int $entityId): OtsRequestInterface
    {
        $otsRequest = $this->otsRequestFactory->create();
        $this->resource->load($otsRequest, $entityId);

        if (!$otsRequest->getId()) {
            throw new NoSuchEntityException(__('OTS request with ID "%1" does not exist.', $entityId));
        }

        return $otsRequest;
    }

    public function getList(SearchCriteriaInterface $searchCriteria): OtsRequestSearchResultsInterface
    {
        $collection = $this->collectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());

        return $searchResults;
    }

    public function delete(OtsRequestInterface $otsRequest): bool
    {
        try {
            $this->resource->delete($otsRequest);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(
                __('Could not delete OTS request: %1', $exception->getMessage()),
                $exception
            );
        }

        return true;
    }

    public function deleteById(int $entityId): bool
    {
        return $this->delete($this->getById($entityId));
    }
}
