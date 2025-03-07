<?php

declare(strict_types=1);

namespace Roukmoute\HashidsBundle\ParamConverter;

use Hashids\HashidsInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

class HashidsParamConverter implements ParamConverterInterface
{
    /** @var HashidsInterface */
    protected $hashids;

    /** @var bool */
    private $passthrough;

    public function __construct(HashidsInterface $hashids, bool $passthrough)
    {
        $this->hashids = $hashids;
        $this->passthrough = $passthrough;
    }

    public function apply(Request $request, ParamConverter $configuration): bool
    {
        $this->decodeHashidOnRoute($request);

        return $this->continueWithNextParamConverters();
    }

    public function supports(ParamConverter $configuration): bool
    {
        return true;
    }

    private function decodeHashidOnRoute(Request $request): bool
    {
        foreach (['hashid', 'id'] as $item) {
            $hashids = $this->hashids->decode($request->attributes->get($item));
            if ($this->hasHashidDecoded($hashids)) {
                $hashid = current($hashids);

                $request->attributes->remove($item);
                $request->attributes->set('id', $hashid);
                return true;
            }
        }

        return false;
    }

    private function hasHashidDecoded($hashids): bool
    {
        return $hashids && is_iterable($hashids) && is_int(reset($hashids));
    }

    private function continueWithNextParamConverters(): bool
    {
        return !$this->passthrough;
    }
}
