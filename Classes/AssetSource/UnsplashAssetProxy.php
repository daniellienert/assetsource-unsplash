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
use Behat\Transliterator\Transliterator;
use Crew\Unsplash\Photo;
use Neos\Flow\Http\Uri;
use Neos\Media\Domain\Model\AssetSource\AssetProxy\AssetProxyInterface;
use Neos\Media\Domain\Model\AssetSource\AssetProxy\HasRemoteOriginalInterface;
use Neos\Media\Domain\Model\AssetSource\AssetSourceInterface;
use Neos\Media\Domain\Model\ImportedAsset;
use Neos\Media\Domain\Repository\ImportedAssetRepository;
use Psr\Http\Message\UriInterface;

final class UnsplashAssetProxy implements AssetProxyInterface, HasRemoteOriginalInterface
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
        return (string)$this->getProperty('description');
    }

    /**
     * @return string
     */
    public function getFilename(): string
    {
        $url = $this->getImageUrl(UnsplashImageSizeInterface::THUMB);

        if (!empty($url)) {
            preg_match('/[^_]*\_(.*)\?.*/', $url, $matches);

            if (isset($matches[1]) && !empty($matches[1])) {
                return $matches[1];
            } else {
                $fromDescription = trim(Transliterator::urlize($this->getProperty('description')));
                $fromId = $this->getProperty('id');
                return (!empty($fromDescription) ? $fromDescription : $fromId) . '.jpg';
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
        return new Uri($this->getImageUrl(UnsplashImageSizeInterface::THUMB));
    }

    /**
     * @return null|UriInterface
     */
    public function getPreviewUri(): ?UriInterface
    {
        return new Uri($this->getImageUrl(UnsplashImageSizeInterface::REGULAR));
    }

    /**
     * @return resource
     */
    public function getImportStream()
    {
        return fopen($this->getImageUrl(UnsplashImageSizeInterface::RAW), 'r');
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
}
