<?php

/*
 * Iinano
 */

namespace Trismegiste\SocialBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Trismegiste\SocialBundle\Utils\SkippableIterator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * ContentController is a template for the wall/dashboard with published content
 *
 * The user MUST be authenticated
 */
class ContentController extends Template
{

    protected function getPagination()
    {
        return $this->getParameter('social.pagination');
    }

    protected function getParameter($name)
    {
        return $this->container->getParameter($name);
    }

    /**
     * Landing page
     *
     * @return Response
     */
    public function indexAction()
    {
        return $this->redirectRouteOk('wall_index', [
                    'wallNick' => $this->getUser()->getUsername(),
                    'wallFilter' => $this->getUser()->getProfile()->defaultWallFilter
        ]);
    }

    /**
     * Pagination scroll
     *
     * @param int $offset the offset to start from
     * @param string $wallNick the nickname of user to show
     * @param string $wallFilter the filter of content from the user
     *
     * @return Response
     *
     * @throws AccessDeniedException if not called with ajax request
     */
    public function ajaxMoreAction($offset, $wallNick, $wallFilter)
    {
        $this->onlyAjaxRequest();

        return $this->renderWall($wallNick, $wallFilter, 'TrismegisteSocialBundle:Content:ajax/index_more.html.twig', [], $offset);
    }

    /**
     * The wall
     *
     * @param string $wallNick the nickname of user to show
     * @param string $wallFilter the filter of content from the user
     *
     * @return Response
     */
    public function wallAction($wallNick, $wallFilter)
    {
        return $this->renderWall($wallNick, $wallFilter, 'TrismegisteSocialBundle:Content:index.html.twig');
    }

    /**
     * The rendering of index.html.twig and its subclass
     *
     * @param string $wallNick the nickname of the currently viewed wall
     * @param string $wallFilter the filter of the currently viewed wall
     * @param string $wallSubview the twig template
     * @param array $parameters other parameters
     *
     * @return Response
     *
     * @throws NotFoundHttpException
     */
    protected function renderWall($wallNick, $wallFilter, $wallSubview, array $parameters = [], $offset = 0)
    {
        // filling the wall user (logged user or not)
        if ($wallNick === $this->getUser()->getUsername()) {
            $parameters['wallNick'] = $wallNick;
            $parameters['wallUser'] = $this->getUser();
        } else {
            $repo = $this->get('social.netizen.repository');
            $user = $repo->findByNickname($wallNick);
            if (is_null($user)) {
                throw new NotFoundHttpException("$wallNick does not exists");
            }
            $parameters['wallUser'] = $user;
            $parameters['wallNick'] = $user->getUsername();
        }
        // filling the wall filter
        $parameters['wallFilter'] = $wallFilter;

        // filling feed entries and skipping one if in CRUD
        $repo = $this->get('social.publishing.repository');
        $it = $repo->findWallEntries($parameters['wallUser'], $wallFilter, $offset, $this->getPagination());

        // do we need to skip a record because it is currently edited ?
        if (array_key_exists('skipped_pub', $parameters)) {
            $it = new SkippableIterator($it, [$parameters['skipped_pub']]);
        }

        $parameters['listing'] = $it;
        $parameters['pagination'] = $this->getPagination();
        $parameters['commentary_preview'] = $this->getParameter('social.commentary_preview');

        return $this->render($wallSubview, $parameters);
    }

    /**
     * Gets all commentaries of a Publishing entity
     *
     * @param string $wallNick the nickname of the wall
     * @param type $wallFilter the filter of the wall
     * @param string $id primary key of the Publishing entity
     *
     * @return Response the html fragment
     *
     * @throws NotFoundException if id not found
     */
    public function ajaxGetAllCommentaryAction($wallNick, $wallFilter, $id)
    {
        $this->onlyAjaxRequest();

        $repo = $this->get('social.publishing.repository');
        try {
            $post = $repo->findByPk($id);

            $param = [
                'content' => $post,
                'commIt' => $post->getCommentaryIterator(),
                'wallNick' => $wallNick,
                'wallFilter' => $wallFilter
            ];
        } catch (\Trismegiste\Yuurei\Persistence\NotFoundException $e) {
            throw $this->createNotFoundException($e->getMessage());
        }

        return $this->render('TrismegisteSocialBundle:Content:ajax/commentary_list.html.twig'
                        , $param);
    }

}
