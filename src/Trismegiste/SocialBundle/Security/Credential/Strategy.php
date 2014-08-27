<?php

/*
 * Iinano
 */

namespace Trismegiste\SocialBundle\Security\Credential;

/**
 * Strategy is a strategy of authentication for a Netizen
 */
interface Strategy
{

    public function getPassword();

    public function getSalt();
}