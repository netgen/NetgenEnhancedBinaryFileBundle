<?php

namespace Netgen\Bundle\EnhancedBinaryFileBundle\Controller;

use eZ\Bundle\EzPublishCoreBundle\Controller;
use eZ\Bundle\EzPublishIOBundle\BinaryStreamResponse;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\Core\IO\IOServiceInterface;
use Netgen\InformationCollection\Doctrine\Entity\EzInfoCollection;
use Netgen\InformationCollection\Doctrine\Entity\EzInfoCollectionAttribute;
use Netgen\InformationCollection\Doctrine\Repository\EzInfoCollectionAttributeRepository;
use Netgen\InformationCollection\Doctrine\Repository\EzInfoCollectionRepository;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class DownloadController extends Controller
{
    private $infocollectionAttributeRepository;
    private $infocollectionRepository;
    private $ioService;

    public function __construct(
        EzInfoCollectionAttributeRepository $infocollectionAttributeRepository,
        EzInfoCollectionRepository $infocollectionRepository,
        IOServiceInterface $ioService
    ) {
        $this->infocollectionAttributeRepository = $infocollectionAttributeRepository;
        $this->infocollectionRepository = $infocollectionRepository;
        $this->ioService = $ioService;
    }

    /**
     * @Route(methods={"GET"}, name="netgen_enhancedezbinaryfile.route.download_binary_file", path="/netgen/enhancedezbinaryfile/download/{infocollectionAttributeId}")
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
        $content = $this->getRepository()->getContentService()->loadContent($contentId);

        if (!$this->getRepository()->getPermissionResolver()->canUser('infocollector', 'read', $content)) {
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
