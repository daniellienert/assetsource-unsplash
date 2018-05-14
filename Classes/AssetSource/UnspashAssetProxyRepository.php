<?php
namespace DL\AssetSource\Unsplash\AssetSource;

/*
 * This file is part of the DL.AssetSource.Unsplash package.
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Media\Domain\Model\AssetSource\AssetNotFoundExceptionInterface;
use Neos\Media\Domain\Model\AssetSource\AssetProxy\AssetProxyInterface;
use Neos\Media\Domain\Model\AssetSource\AssetProxyQueryResultInterface;
use Neos\Media\Domain\Model\AssetSource\AssetProxyRepositoryInterface;
use Neos\Media\Domain\Model\AssetSource\AssetSourceConnectionExceptionInterface;
use Neos\Media\Domain\Model\AssetSource\AssetTypeFilter;
use Neos\Media\Domain\Model\Tag;
use Crew\Unsplash;

final class UnspashAssetProxyRepository implements AssetProxyRepositoryInterface
{
    /**
     * @var UnsplashAssetSource
     */
    private $assetSource;

    /**
     * @param UnsplashAssetSource $assetSource
     */
    public function __construct(UnsplashAssetSource $assetSource)
    {
        $this->assetSource = $assetSource;
    }

    /**
     * @param string $identifier
     * @return AssetProxyInterface
     * @throws AssetNotFoundExceptionInterface
     * @throws AssetSourceConnectionExceptionInterface
     */
    public function getAssetProxy(string $identifier): AssetProxyInterface
    {
        return new UnsplashAssetProxy(Unsplash\Photo::find($identifier), $this->assetSource);
    }

    /**
     * @param AssetTypeFilter $assetType
     */
    public function filterByType(AssetTypeFilter $assetType = null): void
    {

    }

    /**
     * @return AssetProxyQueryResultInterface
     * @throws AssetSourceConnectionExceptionInterface
     */
    public function findAll(): AssetProxyQueryResultInterface
    {
        $query = new UnsplashAssetProxyQuery($this->assetSource);
        return $query->execute();
    }

    /**
     * @param string $searchTerm
     * @return AssetProxyQueryResultInterface
     * @throws AssetSourceConnectionExceptionInterface
     * @throws \Exception
     */
    public function findBySearchTerm(string $searchTerm): AssetProxyQueryResultInterface
    {
        $query = new UnsplashAssetProxyQuery($this->assetSource);
        $query->setSearchTerm($searchTerm);
        return $query->execute();
    }

    /**
     * @param Tag $tag
     * @return AssetProxyQueryResultInterface
     */
    public function findByTag(Tag $tag): AssetProxyQueryResultInterface
    {
        throw new \Exception(__METHOD__ . 'is not yet implemented');
    }

    /**
     * @return AssetProxyQueryResultInterface
     */
    public function findUntagged(): AssetProxyQueryResultInterface
    {
        throw new \Exception(__METHOD__ . 'is not yet implemented');
    }

    /**
     * Count all assets, regardless of tag or collection
     *
     * @return int
     */
    public function countAll(): int
    {
        return 500000;
    }
}
