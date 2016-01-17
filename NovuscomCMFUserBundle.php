<?php

namespace Novuscom\CMFUserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class NovuscomCMFUserBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
