<?php
namespace DL\AssetSource\Unsplash\AssetSource;

/*
 * This file is part of the DL.AssetSource.Unsplash package.
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Media\Domain\Model\AssetSource\AssetProxyQueryInterface;
use Neos\Media\Domain\Model\AssetSource\AssetProxyQueryResultInterface;
use Neos\Media\Domain\Model\AssetSource\AssetSourceConnectionExceptionInterface;
use Crew\Unsplash;

final class UnsplashAssetProxyQuery implements AssetProxyQueryInterface
{

    /**
     * @var UnsplashAssetSource
     */
    private $assetSource;

    /**
     * UnsplashAssetProxyQuery constructor.
     * @param UnsplashAssetSource $assetSource
     */
    public function __construct(UnsplashAssetSource $assetSource)
    {
        $this->assetSource = $assetSource;
    }

    /**
     * @var int
     */
    private $limit = 20;

    /**
     * @var int
     */
    private $offset = 0;

    /**
     * @var string
     */
    private $searchTerm = '';

    /**
     * @param int $offset
     */
    public function setOffset(int $offset): void
    {
        $this->offset = $offset;
    }

    /**
     * @return int
     */
    public function getOffset(): int
    {
        return $this->offset;
    }

    /**
     * @param int $limit
     */
    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @return string
     */
    public function getSearchTerm(): string
    {
        return $this->searchTerm;
    }

    /**
     * @param string $searchTerm
     */
    public function setSearchTerm(string $searchTerm): void
    {
        $this->searchTerm = $searchTerm;
    }

    /**
     * @return AssetProxyQueryResultInterface
     * @throws AssetSourceConnectionExceptionInterface
     * @throws \Exception
     */
    public function execute(): AssetProxyQueryResultInterface
    {
        $page = (int) ceil(($this->offset + 1) / $this->limit);

        if($this->searchTerm === '') {
            $photos = Unsplash\Photo::all($page, $this->limit, 'popular');
        } else {
            $photos = Unsplash\Search::photos($this->searchTerm, $page, $this->limit)->getArrayObject();
        }

        return new UnsplashAssetProxyQueryResult($this, $photos, $this->assetSource);
    }

    /**
     * @return int
     */
    public function count(): int
    {
        throw new \Exception(__METHOD__ . ' is not yet implemented');
    }
}
