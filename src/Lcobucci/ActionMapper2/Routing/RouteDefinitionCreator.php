<?php
namespace Lcobucci\ActionMapper2\Routing;

use Doctrine\Common\Annotations\Reader;
use InvalidArgumentException;

class RouteDefinitionCreator
{
    /**
     * @var string
     */
    const PARAM = '/\([a-zA-Z0-9\_]{1,}\)/';

    /**
     * @var string
     */
    const URI_PATTERN = '/^\/(\(?[a-zA-Z0-9-_\*\.%]{1,}\)?\/?)*$/';

    /**
     * @var string
     */
    const BAR = '/\//';

    /**
     * @var string
     */
    const DOT = '/\./';

    /**
     * @var string
     */
    const WILDCARD1 = '/%/';

    /**
     * @var string
     */
    const WILDCARD2 = '/\*/';

    /**
     * @var string
     */
    const DEFINITION_CLASS = '\Lcobucci\ActionMapper2\Routing\RouteDefinition';

    /**
     * @var string
     */
    protected static $baseClass;

    /**
     * @param string $baseClass
     * @throws InvalidArgumentException
     */
    public static function setBaseClass($baseClass)
    {
        $baseClass = (string) $baseClass;

        if (is_subclass_of($baseClass, static::DEFINITION_CLASS)) {
            static::$baseClass = $baseClass;
            return ;
        }

        throw new InvalidArgumentException(
            'Route definition must be instance of '
            . '\Lcobucci\ActionMapper2\Routing\RouteDefinition'
        );
    }

    /**
     * @param string $pattern
     * @param Route|Filter|\Closure|string $handler
     * @param Reader $annotationReader
     * @return RouteDefinition
     */
    public static function create($pattern, $handler, Reader $annotationReader)
    {
        $baseClass = static::$baseClass ?: static::DEFINITION_CLASS;
        $pattern = static::preparePattern($pattern);

        $route = new $baseClass(
            $pattern,
            static::createRegex($pattern),
            $handler
        );

        $route->setAnnotationReader($annotationReader);

        return $route;
    }

    /**
     * @param string $pattern
     * @param bool $addEnd
     * @return string
     */
    public static function createRegex($pattern, $addEnd = true)
    {
        $regex = '/^';
        $regex .= preg_replace(
            array(
                static::PARAM,
                static::BAR,
                static::DOT,
                static::WILDCARD1,
                static::WILDCARD2
            ),
            array('([^/]+)', '\/', '\.', '.*', '.*'),
            $pattern
        );

        $regex = str_replace('\\/.*', '(\\/.*)?', $regex);
        $regex .= $addEnd ? '$/' : '/';

        return $regex;
    }

    /**
     * @param string $pattern
     * @return boolean
     * @throws InvalidArgumentException
     */
    public static function preparePattern($pattern)
    {
        if (preg_match(static::URI_PATTERN, $pattern) == 0) {
            throw new InvalidArgumentException(
                'Pattern "' . $pattern . '" is invalid'
            );
        }

        if ($pattern != '/') {
            $pattern = rtrim($pattern, '/');
        }

        return preg_replace(
            static::PARAM,
            '(x)',
            $pattern
        );
    }
}
