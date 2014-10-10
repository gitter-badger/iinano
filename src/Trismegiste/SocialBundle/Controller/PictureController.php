<?php

/*
 * iinano
 */

namespace Trismegiste\SocialBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * PictureController is a controller for local storage of picture
 */
class PictureController extends Template
{

    public function getAction($id)
    {
        $doc = $this->getRepository()->findByPk($id);

        $response = new \Symfony\Component\HttpFoundation\Response();
        $response->setLastModified($doc->getLastEdited());
        $response->setPublic();
        $response->headers->set('Content-type', 'image/jpeg');

        if (!$response->isNotModified($this->getRequest())) {
            $response->setContent($doc->getStorageKey()->bin);
        }

        return $response;
    }

    /**
     * The "two-stage upload" : first part, binary
     * Why this ?
     * For two reasons :
     * 1. direct upload using Amazon S3 POST system need 2 post requests
     * 2. managing the uploadedFile in another action keeps the PublishingController unaware of
     *    specific treatment for the Picture entity persistence.
     *
     * So even for local storage, I use the "two-stage upload".
     * The second part, the metadatas are handled by the publishing controller
     */
    public function uploadAction(Request $request)
    {
        $this->onlyAjaxRequest();

        move_uploaded_file($_FILES['fileUpload']['tmp_name']
                , $this->container->getParameter('kernel.root_dir').'/../storage/'
                . $_FILES["fileUpload"]["name"]);
        return new Response('', 201);
    }

}
