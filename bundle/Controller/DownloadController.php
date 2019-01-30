<?php

namespace Netgen\Bundle\EnhancedBinaryFileBundle\Controller;

use eZ\Bundle\EzPublishIOBundle\BinaryStreamResponse;
use eZ\Publish\Core\IO\IOServiceInterface;
use eZ\Publish\Core\SignalSlot\Repository;
use Netgen\Bundle\InformationCollectionBundle\Entity\EzInfoCollection;
use Netgen\Bundle\InformationCollectionBundle\Entity\EzInfoCollectionAttribute;
use Netgen\Bundle\InformationCollectionBundle\Repository\EzInfoCollectionAttributeRepository;
use Netgen\Bundle\InformationCollectionBundle\Repository\EzInfoCollectionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\ControllerTrait;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class DownloadController
{
    use ControllerTrait;

    private $infocollectionAttributeRepository;

    private $infocollectionRepository;

    private $ioService;

    private $repository;

    public function __construct(
        EzInfoCollectionAttributeRepository $infocollectionAttributeRepository,
        EzInfoCollectionRepository $infocollectionRepository,
        IOServiceInterface $ioService,
        Repository $repository
    ) {
        $this->infocollectionAttributeRepository = $infocollectionAttributeRepository;
        $this->infocollectionRepository = $infocollectionRepository;
        $this->ioService = $ioService;
        $this->repository = $repository;
    }

    /**
     * @param int $infocollectionAttributeId
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFoundException
     *
     * @return BinaryStreamResponse
     */
    public function downloadCollectedEnhancedEzBinaryFileAction($infocollectionAttributeId)
    {
        /** @var EzInfoCollectionAttribute $infocollectionAttribute */
        $infocollectionAttribute = $this->infocollectionAttributeRepository->find($infocollectionAttributeId);

        if ($infocollectionAttribute === null) {
            throw new \InvalidArgumentException(
                "Information collection attribute with id '#{$infocollectionAttributeId}'' could not be found"
            );
        }

        /** @var EzInfoCollection $infocollection */
        $infocollection = $this->infocollectionRepository->find($infocollectionAttribute->getInformationCollectionId());

        $contentId = $infocollection->getContentObjectId();
        $content = $this->repository->getContentService()->loadContent($contentId);

        if (!$this->repository->getPermissionResolver()->canUser('infocollector', 'read', $content)) {
            throw new AccessDeniedException('Access denied.');
        }

        $binaryFileXML = $infocollectionAttribute->getDataText();
        $doc = new \DOMDocument('1.0', 'utf-8');
        $doc->loadXML($binaryFileXML);

        $xpath = new \DOMXPath($doc);
        $filePathNodes = $xpath->evaluate('/binaryfile-info/binaryfile-attributes/Filename');
        $originalFilenameNodes = $xpath->evaluate('/binaryfile-info/binaryfile-attributes/OriginalFilename');
        if (!$filePathNodes->length) {
            throw new \InvalidArgumentException(
                "Information collection attribute with id '#{$infocollectionAttributeId}'' could not be found"
            );
        }

        $filePath = $filePathNodes->item(0)->textContent;
        $fileName = basename($filePath);

        $originalFilename = html_entity_decode($originalFilenameNodes->length ? $originalFilenameNodes->item(0)->textContent : $fileName);

        $binaryFile = $this->ioService->loadBinaryFile('collected' . \DIRECTORY_SEPARATOR . $fileName);

        $response = new BinaryStreamResponse($binaryFile, $this->ioService);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $originalFilename);

        return $response;
    }
}
