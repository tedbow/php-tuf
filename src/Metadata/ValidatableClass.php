<?php


namespace Tuf\Metadata;


class ValidatableClass implements \ArrayAccess, \Iterator, \Countable
{

    /**
     * @var \stdClass
     */
    private $internalClass;

    /**
     * @var string
     */
    private $internalCurrentKey;

    /**
     * ValidatableClass constructor.
     */
    public function __construct(\stdClass $class)
    {
        $this->internalClass = $class;
        $this->resetPublicProperties();
        $this->rewind();
    }


    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return property_exists($this->internalClass, $offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return $this->internalClass->{$offset};
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        if (in_array($offset, ['internalCurrentKey', 'internalClass'])) {
            throw new \RuntimeException("Cannot used reserved property name '$offset'");
        }
        $this->internalClass->{$offset} = $value;
        $this->resetPublicProperties();
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
        return $this->internalClass->{$this->internalCurrentKey};
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        $properties = $this->getPropertyNames();
        $index = array_search($this->internalCurrentKey, $properties);
        $index++;
        if ($index >= count($properties)) {
            $this->internalCurrentKey = NULL;
            return FALSE;
        }
        $this->internalCurrentKey = $properties[$index];
        return $this->internalClass->{$this->internalCurrentKey};

    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->internalCurrentKey;
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return in_array($this->internalCurrentKey, $this->getPropertyNames());
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $properties = $this->getPropertyNames();
        $this->internalCurrentKey = array_shift($properties);
    }

    /**
     * @return array
     */
    protected function getPropertyNames(): array
    {
        return array_keys(get_object_vars($this->internalClass));
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->getPropertyNames());
    }


    private function resetPublicProperties()
    {
        $reflection = new \ReflectionObject($this);
        $publicProperties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);
        foreach ($publicProperties as $propertyName) {
            unset($this->{$propertyName});
        }
        foreach ($this->getPropertyNames() as $propertyName) {
            $this->{$propertyName} = $this->offsetGet($propertyName);
        }
    }
}