<?php


namespace Tuf\Metadata;


class ValidatableClass implements \ArrayAccess, \Iterator, \Countable
{

    /**
     * @var \stdClass
     */
    protected $class;

    /**
     * @var string
     */
    protected $current_key;

    /**
     * ValidatableClass constructor.
     */
    public function __construct(\stdClass $class)
    {
        $this->class = $class;
        $this->rewind();
    }


    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return property_exists($this->class, $offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return $this->class->{$offset};
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        $this->class->{$offset} = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        throw new \LogicException("don't unset");
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        return $this->class->{$this->current_key};
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        $properties = $this->getPropertyNames();
        $index = array_search($this->current_key, $properties);
        $index++;
        if ($index >= count($properties)) {
            $this->current_key = NULL;
            return FALSE;
        }
        $this->current_key = $properties[$index];
        return $this->class->{$this->current_key};

    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->current_key;
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return in_array($this->current_key, $this->getPropertyNames());
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $properties = $this->getPropertyNames();
        $this->current_key = array_shift($properties);
    }

    /**
     * @return array
     */
    protected function getPropertyNames(): array
    {
        return array_keys(get_object_vars($this->class));
    }

    public function count()
    {
        return count($this->getPropertyNames());
    }
}