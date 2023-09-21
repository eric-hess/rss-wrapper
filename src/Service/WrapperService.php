<?php

namespace App\Service;

use App\Entity\Manipulator;
use App\Entity\Wrapper;
use App\Service\Manipulator\ManipulatorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class WrapperService
{
    /**
     * @param array<ManipulatorInterface> $manipulators
     */
    public function __construct(
        private HttpClientInterface $httpClient,
        private array $manipulators
    )
    {
    }

    public function wrap(Wrapper $wrapper): string
    {
        $response = $this->httpClient->request('GET', $wrapper->getFeed());

        $feed = new \SimpleXMLElement($response->getContent());

        if (in_array('http://www.w3.org/2005/Atom', $feed->getNamespaces())) {
            $feed->registerXPathNamespace('atom', 'http://www.w3.org/2005/Atom');
        }

        foreach ($wrapper->getManipulators() as $manipulator) {
            foreach ($feed->xpath('//item|//atom:entry') as $item) {
                if (true === $this->shouldItemBeRemoved($item, $manipulator)) {
                    unset($item[0]);
                }
            }
        }

        return $feed->asXML();
    }

    private function shouldItemBeRemoved(\SimpleXMLElement $item, Manipulator $manipulator): bool
    {
        foreach($this->manipulators as $manipulatorHandler) {
            if (!$manipulatorHandler->supports($manipulator->getType())) {
                continue;
            }

            return $manipulatorHandler->shouldElementBeRemoved($item, $manipulator);
        }

        return false;
    }
}