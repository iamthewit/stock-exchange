<?php

namespace StockExchange\Infrastructure\Normalizer;

use StockExchange\Application\Message\GenericMessage;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizableInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectToPopulateTrait;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerAwareTrait;

// TODO: double check and tidy up - this is a frankenstein class copied from
// here: https://github.com/symfony/symfony/blob/6.0/src/Symfony/Component/Serializer/Normalizer/CustomNormalizer.php
// and here: https://symfony.com/doc/current/serializer/custom_normalizer.html#creating-a-new-normalizer
class StockExchangeEventNormalizer implements NormalizerInterface, DenormalizerInterface, SerializerAwareInterface, CacheableSupportsMethodInterface
{
    use ObjectToPopulateTrait;
    use SerializerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function normalize(mixed $object, string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        return $object->normalize($this->serializer, $format, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize(mixed $data, string $type, string $format = null, array $context = []): mixed
    {
        return new GenericMessage($type, $data);
    }

    /**
     * Checks if the given class implements the NormalizableInterface.
     *
     * @param mixed  $data   Data to normalize
     * @param string $format The format being (de-)serialized from or into
     */
    public function supportsNormalization(mixed $data, string $format = null): bool
    {
        // TODO: void this - we don't need it afaik...
        return $data instanceof NormalizableInterface;
    }

    /**
     * Checks if the given class implements the DenormalizableInterface.
     *
     * @param mixed  $data   Data to denormalize from
     * @param string $type   The class to which the data should be denormalized
     * @param string $format The format being deserialized from
     */
    public function supportsDenormalization(mixed $data, string $type, string $format = null): bool
    {
        // strpos() returns a 0 integer if the string needle is found at position 0 in the stack
        // it returns false if the string position is not found
        // so we check if the result is an integer to prove that we found the string
        return is_int(strpos($type, 'StockExchange\StockExchange'));
    }

    /**
     * {@inheritdoc}
     */
    public function hasCacheableSupportsMethod(): bool
    {
        return __CLASS__ === static::class;
    }
}