<?php

/*
 * Iinano
 */

namespace Trismegiste\SocialBundle\Repository;

use Trismegiste\Yuurei\Persistence\RepositoryInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Trismegiste\DokudokiBundle\Transform\Mediator\Colleague\MapAlias;
use Trismegiste\Socialist\Publishing;
use Trismegiste\Socialist\Follower;

/**
 * PublishingRepository is a business repository for subclasses of Publishing
 *
 * This is a wrapper around a RepositoryInterface with SecurityContext
 * This is not a decorator of RepositoryInterface because we
 * try to avoid dumb repositories (as well as dumb entities), so only methods
 * with business relevance. Plus, security concerns will break Liskov principle
 *
 * @deprecated
 */
class PublishingRepository implements PublishingRepositoryInterface
{

    protected $repository;
    protected $security;
    protected $aliasFilter;

    /**
     * Ctor
     *
     * @param \Trismegiste\Yuurei\Persistence\RepositoryInterface $repo
     * @param \Symfony\Component\Security\Core\SecurityContextInterface $ctx
     * @param array $aliases a list a class key for each document
     */
    public function __construct(RepositoryInterface $repo, SecurityContextInterface $ctx, array $aliases)
    {
        $this->security = $ctx;
        $this->repository = $repo;
        $this->aliasFilter = [MapAlias::CLASS_KEY => ['$in' => $aliases]];
    }

    /**
     * @inheritdoc
     */
    public function findLastEntries($offset = 0, $limit = 20, \ArrayIterator $author = null)
    {
        $docFilter = $this->aliasFilter;
        if (!is_null($author)) {
            $filter = array_keys(iterator_to_array($author));
            $docFilter['owner.nickname'] = ['$in' => $filter];
        }

        return $this->repository
                        ->find($docFilter)
                        ->limit($limit)
                        ->offset($offset)
                        ->sort(['createdAt' => -1]);
    }

    /**
     * @inheritdoc
     */
    public function persist(Publishing $doc)
    {
        $this->repository->persist($doc);
    }

    /**
     * @inheritdoc
     */
    public function findByPk($pk)
    {
        $doc = $this->repository->findByPk($pk);
        if (!$doc instanceof Publishing) {
            throw new \LogicException("$pk is type of " . get_class($doc));
        }

        return $doc;
    }

    /**
     * @inheritdoc
     */
    public function findWallEntries(Follower $wallUser, $wallFilter, $offset = 0, $limit = 20)
    {
        switch ($wallFilter) {

            case 'self':
                $filterAuthor = new \ArrayIterator([$wallUser->getUniqueId() => true]);
                break;

            case 'following':
                $filterAuthor = $wallUser->getFollowingIterator();
                break;

            case 'follower':
                $filterAuthor = $wallUser->getFollowerIterator();
                break;

            case 'friend':
                $filterAuthor = $wallUser->getFriendIterator();
                break;

            case 'all':
                $filterAuthor = null;
                break;

            default:
                throw new \InvalidArgumentException("$wallFilter is not valid filter");
        }

        return $this->findLastEntries($offset, $limit, $filterAuthor);
    }

}
