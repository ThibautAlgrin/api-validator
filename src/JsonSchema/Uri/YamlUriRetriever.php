<?php declare(strict_types=1);

namespace ElevenLabs\Api\JsonSchema\Uri;

use JsonSchema\Uri\UriRetriever;
use Symfony\Component\Yaml\Yaml;

/**
 * Class YamlUriRetriever.
 */
class YamlUriRetriever extends UriRetriever
{
    /**
     * @see loadSchema
     *
     * @var array
     */
    private $schemaCache = array();

    /**
     * @param string $fetchUri
     *
     * @return mixed
     */
    protected function loadSchema($fetchUri)
    {
        if (isset($this->schemaCache[$fetchUri])) {
            return $this->schemaCache[$fetchUri];
        }

        $contents = $this->getUriRetriever()->retrieve($fetchUri);

        $contents = Yaml::parse($contents);
        $jsonSchema = json_decode(json_encode($contents));

        $this->schemaCache[$fetchUri] = $jsonSchema;

        return $jsonSchema;
    }
}
