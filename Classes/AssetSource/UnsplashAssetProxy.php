<?php
namespace DL\AssetSource\Unsplash\AssetSource;

/*
 * This file is part of the DL.AssetSource.Unsplash package.
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Eel\EelEvaluatorInterface;
use Neos\Eel\Utility;
use Behat\Transliterator\Transliterator;
use Crew\Unsplash\Photo;
use Neos\Media\Domain\Model\AssetSource\AssetProxy\AssetProxyInterface;
use Neos\Media\Domain\Model\AssetSource\AssetProxy\HasRemoteOriginalInterface;
use Neos\Media\Domain\Model\AssetSource\AssetProxy\SupportsIptcMetadataInterface;
use Neos\Media\Domain\Model\AssetSource\AssetSourceInterface;
use Neos\Media\Domain\Model\ImportedAsset;
use Neos\Media\Domain\Repository\ImportedAssetRepository;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;

final class UnsplashAssetProxy implements AssetProxyInterface, HasRemoteOriginalInterface, SupportsIptcMetadataInterface
{
    /**
     * @var Photo
     */
    private $photo;

    /**
     * @var UnsplashAssetSource
     */
    private $assetSource;

    /**
     * @var ImportedAsset
     */
    private $importedAsset;

    /**
     * @var array
     */
    private $iptcProperties;

    /**
     * @var array
     * @Flow\InjectConfiguration(path="defaultContext", package="Neos.Fusion")
     */
    protected $defaultContextConfiguration;

    /**
     * @var EelEvaluatorInterface
     * @Flow\Inject(lazy=false)
     */
    protected $eelEvaluator;

    /**
     * @var UriFactoryInterface
     * @Flow\Inject(lazy=false)
     */
    protected $uriFactory;

    /**
     * UnsplashAssetProxy constructor.
     * @param Photo $photo
     * @param UnsplashAssetSource $assetSource
     */
    public function __construct(Photo $photo, UnsplashAssetSource $assetSource)
    {
        $this->photo = $photo;
        $this->assetSource = $assetSource;
        $this->importedAsset = (new ImportedAssetRepository)->findOneByAssetSourceIdentifierAndRemoteAssetIdentifier($assetSource->getIdentifier(), $this->getIdentifier());
    }

    /**
     * @return AssetSourceInterface
     */
    public function getAssetSource(): AssetSourceInterface
    {
        return $this->assetSource;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return (string)$this->getProperty('id');
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        $label = trim((string)$this->getProperty('description'));
        return $label !== '' ? $label : $this->getFilename();
    }

    /**
     * @return string
     */
    public function getFilename(): string
    {
        $fromDescription = trim(Transliterator::urlize($this->getProperty('description')));
        if ($fromDescription !== '') {
            return $fromDescription . '.jpg';
        }

        $fromUrl = $this->extractNameFromUrl();
        if ($fromUrl !== '') {
            return $fromUrl . '.jpg';
        }

        $fromId = $this->getProperty('id');
        if ($fromId !== '') {
            return $fromId . '.jpg';
        }

        return '';
    }

    /**
     * @return string
     */
    protected function extractNameFromUrl(): string
    {
        $url = $this->getImageUrl(UnsplashImageSizeInterface::THUMB);

        if (!empty($url)) {
            preg_match('/[^\_]*\_([^\?]*)\?(.*)/', $url, $matches);

            if (isset($matches[1]) && !empty($matches[1])) {
                return $matches[1];
            }
        }

        return '';
    }

    /**
     * @return \DateTimeInterface
     */
    public function getLastModified(): \DateTimeInterface
    {
        return \DateTime::createFromFormat(\DateTime::ATOM, $this->getProperty('updated_at'));
    }

    /**
     * @return int
     */
    public function getFileSize(): int
    {
        return 0;
    }

    /**
     * @return string
     */
    public function getMediaType(): string
    {
        return 'image/jpeg';
    }

    /**
     * @return int|null
     */
    public function getWidthInPixels(): ?int
    {
        return (int)$this->getProperty('width');
    }

    /**
     * @return int|null
     */
    public function getHeightInPixels(): ?int
    {
        return (int)$this->getProperty('height');
    }

    /**
     * @return null|UriInterface
     */
    public function getThumbnailUri(): ?UriInterface
    {
        return $this->uriFactory->createUri($this->getImageUrl(UnsplashImageSizeInterface::THUMB));
    }

    /**
     * @return null|UriInterface
     */
    public function getPreviewUri(): ?UriInterface
    {
        return $this->uriFactory->createUri($this->getImageUrl(UnsplashImageSizeInterface::REGULAR));
    }

    /**
     * @return resource
     */
    public function getImportStream()
    {
        return fopen($this->photo->download(), 'rb');
    }

    /**
     * @return null|string
     */
    public function getLocalAssetIdentifier(): ?string
    {
        return $this->importedAsset instanceof ImportedAsset ? $this->importedAsset->getLocalAssetIdentifier() : '';
    }

    /**
     * Returns true if the binary data of the asset has already been imported into the Neos asset source.
     *
     * @return bool
     */
    public function isImported(): bool
    {
        return $this->importedAsset !== null;
    }

    /**
     * @param string $propertyName
     * @return mixed|null
     */
    protected function getProperty(string $propertyName)
    {
        return $this->photo->__isset($propertyName) ? $this->photo->__get($propertyName) : null;
    }

    /**
     * @param string $size
     * @return string
     */
    protected function getImageUrl(string $size): string
    {
        $urls = $this->getProperty('urls');
        if (isset($urls[$size])) {
            return $urls[$size];
        }
        return '';
    }

    /**
     * Returns true, if the given IPTC metadata property is available, ie. is supported and is not empty.
     *
     * @param string $propertyName
     * @return bool
     * @throws \Neos\Eel\Exception
     */
    public function hasIptcProperty(string $propertyName): bool
    {
        return isset($this->getIptcProperties()[$propertyName]);
    }

    /**
     * Returns the given IPTC metadata property if it exists, or an empty string otherwise.
     *
     * @param string $propertyName
     * @return string
     * @throws \Neos\Eel\Exception
     */
    public function getIptcProperty(string $propertyName): string
    {
        return $this->getIptcProperties()[$propertyName] ?? '';
    }

    /**
     * Returns all known IPTC metadata properties as key => value (e.g. "Title" => "My Photo")
     *
     * @return array
     * @throws \Neos\Eel\Exception
     */
    public function getIptcProperties(): array
    {
        if ($this->iptcProperties === null) {
            $this->iptcProperties = [
                'Title' => $this->getLabel(),
                'CopyrightNotice' => $this->compileCopyrightNotice($this->photo->user),
                'Creator' => $this->photo->user['name']
            ];
        }

        return $this->iptcProperties;
    }

    /**
     * @param array $userProperties
     * @return string
     * @throws \Neos\Eel\Exception
     */
    protected function compileCopyrightNotice(array $userProperties): string
    {
        return Utility::evaluateEelExpression($this->assetSource->getCopyRightNoticeTemplate(), $this->eelEvaluator, ['user' => $userProperties], $this->defaultContextConfiguration);
    }
}
