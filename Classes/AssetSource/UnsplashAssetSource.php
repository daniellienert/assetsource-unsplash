<?php
declare(strict_types=1);
namespace DL\AssetSource\Unsplash\AssetSource;

/*
 * This file is part of the DL.AssetSource.Unsplash package.
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Configuration\Exception\InvalidConfigurationException;
use Neos\Flow\ResourceManagement\ResourceManager;
use Neos\Media\Domain\Model\AssetSource\AssetProxyRepositoryInterface;
use Neos\Media\Domain\Model\AssetSource\AssetSourceInterface;

use Neos\Media\Domain\Model\AssetSource\Neos\NeosAssetProxyRepository;
use Crew\Unsplash;

final class UnsplashAssetSource implements AssetSourceInterface
{
    /**
     * @var string
     */
    private $assetSourceIdentifier;

    /**
     * @var NeosAssetProxyRepository
     */
    private $assetProxyRepository;

    /**
     * @var string
     */
    private $copyRightNoticeTemplate;

    /**
     * @Flow\Inject
     * @var ResourceManager
     */
    protected $resourceManager;

    /**
     * @var string
     */
    protected $iconPath;

    /**
     * UnsplashAssetSource constructor.
     * @param string $assetSourceIdentifier
     * @param array $assetSourceOptions
     * @throws InvalidConfigurationException
     */
    public function __construct(string $assetSourceIdentifier, array $assetSourceOptions)
    {
        $this->assetSourceIdentifier = $assetSourceIdentifier;
        $this->copyRightNoticeTemplate = $assetSourceOptions['copyRightNoticeTemplate'] ?? '';
        $this->iconPath = $assetSourceOptions['icon'] ?? '';

        if (!isset($assetSourceOptions['accessKey']) || trim($assetSourceOptions['accessKey']) === '') {
            throw new InvalidConfigurationException(sprintf('Access Key for the %s data source not set.', $this->getLabel()), 1526326192);
        }

        Unsplash\HttpClient::init([
            'applicationId' => $assetSourceOptions['accessKey'],
            'utmSource' => $assetSourceOptions['utmSource'] ?? 'Neos CMS Unsplash Asset Source https://neos.io'
        ]);
    }

    /**
     * This factory method is used instead of a constructor in order to not dictate a __construct() signature in this
     * interface (which might conflict with an asset source's implementation or generated Flow proxy class).
     *
     * @param string $assetSourceIdentifier
     * @param array $assetSourceOptions
     * @return AssetSourceInterface
     * @throws InvalidConfigurationException
     */
    public static function createFromConfiguration(string $assetSourceIdentifier, array $assetSourceOptions): AssetSourceInterface
    {
        return new static($assetSourceIdentifier, $assetSourceOptions);
    }

    /**
     * A unique string which identifies the concrete asset source.
     * Must match /^[a-z][a-z0-9-]{0,62}[a-z]$/
     *
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->assetSourceIdentifier;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return 'Unsplash';
    }

    /**
     * @return string
     */
    public function getCopyRightNoticeTemplate(): string
    {
        return $this->copyRightNoticeTemplate;
    }

    /**
     * @return AssetProxyRepositoryInterface
     */
    public function getAssetProxyRepository(): AssetProxyRepositoryInterface
    {
        if ($this->assetProxyRepository === null) {
            $this->assetProxyRepository = new UnsplashAssetProxyRepository($this);
        }

        return $this->assetProxyRepository;
    }

    /**
     * @return bool
     */
    public function isReadOnly(): bool
    {
        return true;
    }

    /**
     * Returns the resource path to Assetsources icon
     *
     * @return string
     */
    public function getIconUri(): string
    {
        return $this->resourceManager->getPublicPackageResourceUriByPath($this->iconPath);
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return 'Beautiful Free Images & Pictures | Unsplash. Visit https://unsplash.com/';
    }
}
