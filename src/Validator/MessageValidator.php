<?php declare(strict_types=1);

namespace ElevenLabs\Api\Validator;

use ElevenLabs\Api\Decoder\DecoderInterface;
use ElevenLabs\Api\Decoder\DecoderUtils;
use ElevenLabs\Api\Definition\MessageDefinition;
use ElevenLabs\Api\Definition\RequestDefinition;
use ElevenLabs\Api\Normalizer\QueryParamsNormalizer;
use JsonSchema\Validator;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Rize\UriTemplate;

/**
 * Class MessageValidator.
 */
class MessageValidator
{
    /**
     * @var Validator
     */
    private $validator;

    /**
     * @var array
     */
    private $violations = [];

    /**
     * @var DecoderInterface
     */
    private $decoder;

    /**
     * MessageValidator constructor.
     *
     * @param Validator        $validator
     * @param DecoderInterface $decoder
     */
    public function __construct(Validator $validator, DecoderInterface $decoder)
    {
        $this->validator = $validator;
        $this->decoder = $decoder;
    }

    /**
     * @param RequestInterface  $request
     * @param RequestDefinition $definition
     */
    public function validateRequest(RequestInterface $request, RequestDefinition $definition): void
    {
        if ($definition->hasBodySchema()) {
            $contentTypeValid = $this->validateContentType($request, $definition);
            if ($contentTypeValid && in_array($request->getMethod(), ['PUT', 'PATCH', 'POST'])) {
                $this->validateMessageBody($request, $definition);
            }
        }

        $this->validateHeaders($request, $definition);
        $this->validatePath($request, $definition);
        $this->validateQueryParameters($request, $definition);
    }

    /**
     * @param ResponseInterface $response
     * @param RequestDefinition $definition
     */
    public function validateResponse(ResponseInterface $response, RequestDefinition $definition): void
    {
        $responseDefinition = $definition->getResponseDefinition($response->getStatusCode());
        if ($responseDefinition->hasBodySchema()) {
            $contentTypeValid = $this->validateContentType($response, $responseDefinition);
            if ($contentTypeValid) {
                $this->validateMessageBody($response, $responseDefinition);
            }
        }

        $this->validateHeaders($response, $responseDefinition);
    }

    /**
     * @param MessageInterface  $message
     * @param MessageDefinition $definition
     */
    public function validateHeaders(MessageInterface $message, MessageDefinition $definition): void
    {
        if ($definition->hasHeadersSchema()) {
            // Transform each header values into a string
            $headers = array_map(
                function (array $values) {
                    return implode(', ', $values);
                },
                $message->getHeaders()
            );

            $this->validate(
                (object) array_change_key_case($headers, CASE_LOWER),
                $definition->getHeadersSchema(),
                'header'
            );
        }
    }

    /**
     * @param MessageInterface  $message
     * @param MessageDefinition $definition
     */
    public function validateMessageBody(MessageInterface $message, MessageDefinition $definition): void
    {
        if ($message instanceof ServerRequestInterface) {
            $bodyString = json_encode((array) $message->getParsedBody());
        } else {
            $bodyString = (string) $message->getBody();
        }

        if ('' !== $bodyString && $definition->hasBodySchema()) {
            $contentType = $message->getHeaderLine('Content-Type');
            $decodedBody = $this->decoder->decode(
                $bodyString,
                DecoderUtils::extractFormatFromContentType($contentType)
            );

            $this->validate($decodedBody, $definition->getBodySchema(), 'body');
        }
    }

    /**
     * @param MessageInterface  $message
     * @param MessageDefinition $definition
     *
     * @return bool
     */
    public function validateContentType(MessageInterface $message, MessageDefinition $definition): bool
    {
        $contentType = explode(';', $message->getHeaderLine('Content-Type'));
        $contentTypes = $definition->getContentTypes();

        if (!in_array($contentType[0], $contentTypes, true)) {
            if ('' === $contentType[0]) {
                $violationMessage = 'Content-Type should not be empty';
                $constraint = 'required';
            } else {
                $violationMessage = sprintf(
                    '%s is not a supported content type, supported: %s',
                    $message->getHeaderLine('Content-Type'),
                    implode(', ', $contentTypes)
                );
                $constraint = 'enum';
            }

            $this->addViolation(
                new ConstraintViolation(
                    'Content-Type',
                    $violationMessage,
                    $constraint,
                    'header'
                )
            );

            return false;
        }

        return true;
    }

    /**
     * @param RequestInterface  $request
     * @param RequestDefinition $definition
     */
    public function validatePath(RequestInterface $request, RequestDefinition $definition): void
    {
        if ($definition->hasPathSchema()) {
            $template = new UriTemplate();
            $params = $template->extract($definition->getPathTemplate(), $request->getUri()->getPath());
            $schema = $definition->getPathSchema();

            $this->validate(
                (object) $params,
                $schema,
                'path'
            );
        }
    }

    /**
     * @param RequestInterface  $request
     * @param RequestDefinition $definition
     */
    public function validateQueryParameters(RequestInterface $request, RequestDefinition $definition): void
    {
        if ($definition->hasQueryParametersSchema()) {
            $queryParams = [];
            $query = $request->getUri()->getQuery();
            if ('' !== $query) {
                foreach (explode('&', $query) as $item) {
                    $tmp = explode('=', $item);
                    $queryParams[$tmp[0]] = $tmp[1];
                }
            }
            $schema = $definition->getQueryParametersSchema();
            $queryParams = QueryParamsNormalizer::normalize($queryParams, $schema);

            $this->validate(
                (object) $queryParams,
                $schema,
                'query'
            );
        }
    }

    /**
     * @return bool
     */
    public function hasViolations(): bool
    {
        return !empty($this->violations);
    }

    /**
     * @return ConstraintViolation[]
     */
    public function getViolations(): array
    {
        return $this->violations;
    }

    /**
     * @param mixed     $data
     * @param \stdClass $schema
     * @param string    $location
     */
    private function validate($data, \stdClass $schema, string $location): void
    {
        $this->validator->coerce($data, $schema);
        if (!$this->validator->isValid()) {
            $violations = array_map(
                function (array $error) use ($location) {
                    return new ConstraintViolation(
                        $error['property'],
                        $error['message'],
                        $error['constraint'],
                        $location
                    );
                },
                $this->validator->getErrors()
            );

            foreach ($violations as $violation) {
                $this->addViolation($violation);
            }
        }

        $this->validator->reset();
    }

    /**
     * @param ConstraintViolation $violation
     */
    private function addViolation(ConstraintViolation $violation)
    {
        $this->violations[] = $violation;
    }
}
