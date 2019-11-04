<?php

namespace Ronanchilvers\Orm\Features\Type;

use Carbon\Carbon;

/**
 * Type handler for date time data
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class DateTimeHandler implements HandlerInterface
{
    /**
     * @var string
     */
    protected $format = 'Y-m-d H:i:s';

    /**
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function toType($raw, array $options = [])
    {
        if ($raw instanceof Carbon) {
            return $raw;
        }

        // Assume if the raw value is numeric that its a unix timestamp
        if (is_numeric($raw)) {
            return Carbon::createFromTimestamp($raw);
        }

        $format = $this->format;
        if (isset($options['format'])) {
            $format = $options['format'];
        }

        return Carbon::createFromFormat(
            $format,
            $raw
        );
    }

    /**
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function toRaw($typeData, array $options = [])
    {
        if (!$typeData instanceof \DateTime) {
            return $typeData;
        }

        $format = $this->format;
        if (isset($options['format'])) {
            $format = $options['format'];
        }

        return $typeData->format(
            $format
        );
    }
}
