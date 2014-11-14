<?php

/*
 * iinano
 */

namespace Trismegiste\SocialBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

/**
 * PrivateMessageController is a CRUD controller for Private Message
 */
class PrivateMessageController extends Template
{

    public function createAction($author)
    {
        $form = $this->createForm('social_private_message', null, [
            'action' => $this->generateUrl('private_create')
        ]);

        if (!empty($author)) {
            $form->get('target')->setData(new \Trismegiste\Socialist\Author($author));
        }

        $repo = $this->get('social.private_message.repository');

        $form->handleRequest($this->getRequest());
        if ($form->isValid()) {
            $repo->persist($form->getData());
            $this->pushFlash('notice', 'Message sent');

            return $this->redirectRouteOk('private_create');
        }

        return $this->render('TrismegisteSocialBundle:PrivateMessage:create_form.html.twig', [
                    'form' => $form->createView(),
                    'received' => $repo->findAllReceived(),
                    'sent' => $repo->findAllSent()->limit(10)
        ]);
    }

    public function ajaxFindFollowerAction(Request $request)
    {
        $this->onlyAjaxRequest();
        $choice = [];
        $nick = $request->query->get('q');

        $iter = $this->getUser()->getFollowerIterator();
        $iter = new \Trismegiste\SocialBundle\Utils\KeyRegexFilter($iter, "#$nick#");
        $cursor = $this->get('social.netizen.repository')->findBatchNickname($iter);
        foreach ($cursor as $netizen) {
            $choice[] = [
                'key' => $netizen->getUsername(),
                'value' => $netizen->getUsername() . ' (' . $netizen->getProfile()->fullName . ')',
                'avatar' => $this->generateUrl('picture_get', [
                    'storageKey' => $netizen->getAuthor()->getAvatar(),
                    'size' => 'full'
                ])
            ];
        }

        return new \Symfony\Component\HttpFoundation\JsonResponse($choice);
    }

    public function markAsReadAction($id)
    {
        $repo = $this->get('social.private_message.repository');
        $repo->persistAsRead($id);
        $this->pushFlash('notice', 'PM marked as read');

        return $this->redirectRouteOk('private_create');
    }

}
