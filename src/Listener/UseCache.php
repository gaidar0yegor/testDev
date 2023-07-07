<?php

namespace App\Listener;

/**
 * Annotation to use on controllers when cache (like max-age, ...) is used.
 * Without this annotation, cache is overriden because of:
 *  Symfony\Component\HttpKernel\EventListener\AbstractSessionListener::NO_AUTO_CACHE_CONTROL_HEADER
 *
 * Example:
 * /**
 *  * @UseCache
 *  * @Cache(maxage="120")
 *  * @Route(...)
 *  //
 *  public function indexAction()
 *  {...}
 *
 * @Annotation
 */
class UseCache
{
}
