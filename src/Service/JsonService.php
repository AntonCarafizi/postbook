<?php

namespace App\Service;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\DependencyInjection\ContainerInterface;

class JsonService
{

    private $encoder;

    private $dateFormat;

    public function __construct(ContainerInterface $container)
    {
        $this->encoder = new JsonEncoder();
        $this->dateFormat = $container->getParameter('date_format');
    }

    public function convert($array) {

        $dateCallback = function ($innerObject, $outerObject, string $attributeName, string $format = null, array $context = []) {
            $dateFormat = $this->dateFormat;
            if ($attributeName == 'createdAt') {
                $dateFormat = $this->dateFormat . " H:i";
            }

            return $innerObject->format($dateFormat);
        };

        $defaultContext = [
            AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object, $format, $context) {
                return $object->getId();
            },
            AbstractNormalizer::IGNORED_ATTRIBUTES => ['roles', 'password', 'posts', 'likes', 'favorites', 'lastLogin', 'salt'],
            AbstractNormalizer::CALLBACKS => [
                'createdAt' => $dateCallback
            ],
        ];
        $normalizer = new ObjectNormalizer(null, null, null, null, null, null, $defaultContext);

        $serializer = new Serializer([$normalizer], [$this->encoder]);
        $data = $serializer->serialize($array, 'json', ['groups' => 'post_index']);

        return $data;
    }
}