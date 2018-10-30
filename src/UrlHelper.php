<?php

namespace JeremyElliot;

/**
 * UrlHelper provides a wrapper for URLs.
 *
 * It provides a way to easily find out about a URL, to get particular
 * parts of the URL, and to extract parts of the URL into a new valid URL.
 *
 * UrlHelpers are immutable. When a UrlHelper is cast to string it will give the same string
 * that was passed in the constructor.
 */
class UrlHelper
{
    /** @var string */
    private $url;
    /** @var array */
    private $parts;

    /**
     * @param string $url URL string
     */
    public function __construct(string $url)
    {
        $this->url = trim($url);
    }

    /**
     * Returns the URL as an array of ports
     *
     * The keys of the returned array are some or all of
     *  'scheme', 'user', 'pass', 'host', 'port', 'dir', 'file', 'ext', 'query', 'fragment'
     *
     * @return array parts
     */
    public function getParts() : array
    {
        if (empty($this->parts)) {
            $parts = [
                'scheme' => '',
                'user' => '',
                'pass' => '',
                'host' => '',
                'port' => 0,
                'dir' => '',
                'file' => '',
                'ext' => '',
                'query' => '',
                'fragment' => ''
            ];
            foreach (parse_url($this->url) as $key => $value) {
                $parts[$key] = $value;
            }
            if (!empty($parts['path'])) {
                $dirMatch = [];
                \preg_match('#.*/#', $parts['path'], $dirMatch);
                $parts['dir'] = $dirMatch[0] ?? '';
                $parts['ext'] = \pathinfo($parts['path'], PATHINFO_EXTENSION);
                $filename = \substr($parts['path'], strlen($parts['dir']));
                if (!empty($parts['ext'])) {
                    $filename = \substr($filename, 0, -(strlen($parts['ext']) + 1));
                }
                $parts['file'] = $filename;
            }
            $this->parts = $parts;
        }
        return $this->parts;
    }

    /**
     * Return true if this is an absolute URL
     *
     * @return bool
     */
    public function isAbsolute() : bool
    {
        return !empty($this->getParts()['host']);
    }

    /**
    * Return true if this is a root-relative URL
    *
    * @return bool
    */
    public function isRootRelative() : bool
    {
        $parts = $this->getParts();
        return (
            !$this->isAbsolute()
            && !empty($parts['path'])
            && (substr($parts['path'], 0, 1) === '/')
        );
    }

    /**
    * Return true if this is a context-relative URL
    *
    * Context-relative URL examples:
    * - foo.html
    * - ./bar.html
    * - foo/bar/
    *
    * @return bool
    */
    public function isContextRelative() : bool
    {
        return (!$this->isAbsolute() && !$this->isRootRelative());
    }


    /**
     * Gets a new url built according to a definition string
     *
     * The returned url will consist of the parts that are in both the expression string
     * and $this url.
     *
     * The URL Parts Expression:
     *  - The expression for the full URL is 'scheme.user.pass.host.port.dir.file.ext.query.fragment'.
     *  - Expressions can consist of any or all of the dot-separated parts.
     *  - The ports of the returned URL are always put together in the same order, regardless of their
     *    order in the expression.
     *  - The word 'base' can be used as shorthand for 'scheme.user.pass.host.port'. This means that,
     *    for example, 'base.dir.file.ext' is equivalent to 'scheme.user.pass.host.port.dir.file.ext'
     *
     * @param string $expression dot-separated string of parts
     * @return UrlHelper new URL
     */
    public function get(string $expression='scheme.user.pass.host.port.dir.file.ext.query.fragment') : UrlHelper
    {
        $expression = str_replace('base', 'scheme.user.pass.host.port', $expression);
        $parts = $this->getParts();
        $expr = array_flip(array_filter(explode('.', $expression), function ($token) use ($parts) {
            return !empty($parts[$token]);
        }));
        return new self(
            (isset($expr['scheme']) ? "{$parts['scheme']}:" : '')
            . (isset($expr['host']) ? '//' : '')
            . (isset($expr['user']) ? $parts['user'] : '')
            . (isset($expr['pass']) ? ":{$parts['pass']}" : '')
            . (isset($expr['user']) ? '@' : '')
            . (isset($expr['host']) ? $parts['host'] : '')
            . (isset($expr['port']) ? ":{$parts['port']}" : '')
            . (isset($expr['dir']) ? $parts['dir'] : '')
            . (isset($expr['file']) ? $parts['file'] : '')
            . ((isset($expr['file']) && isset($expr['ext'])) ? '.' : '')
            . (isset($expr['ext']) ? $parts['ext'] : '')
            . (isset($expr['query']) ? "?{$parts['query']}" : '')
            . (isset($expr['fragment']) ? "#{$parts['fragment']}" : '')
        );
    }

    /**
     * Returns a single part of this URL, without decoration
     *
     * $part must be one of the following strings:
     *      'scheme', 'user', 'pass', 'host', 'port',
     *      'dir', 'file', 'ext', 'query', 'fragment'
     *
     * The shortcut-part 'base' cannot be used with this method
     * @see UrlHelper::get($expression)
     *
     * @param string $part name of a URL part
     * @return string part of this URL
     */
    public function getPart(string $part) : string
    {
        $parts = $this->getParts();
        return (isset($parts[$part])) ? $parts[$part] : '';
    }

    /**
     * Same as get('base.dir') except that the last character is always '/'
     *
     * The context part can be prepended to URLs that are relative to the page they appear on.
     *
     * @return UrlHelper context URL
     */
    public function getContextPart()
    {
        return new self(rtrim($this->get('base.dir'), '/') . '/');
    }

    public function __toString()
    {
        return $this->url;
    }
}
