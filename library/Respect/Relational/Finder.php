<?php

namespace Respect\Relational;

use ArrayAccess;

class Finder implements ArrayAccess
{

    protected $required = true;
    protected $mapper;
    protected $name;
    protected $condition;
    protected $parent;
    protected $nextSibling;
    protected $lastSibling;
    protected $children = array();

    public static function __callStatic($name, $children)
    {
        $newFinder = new static($name);

        foreach ($children as $child)
            if ($child instanceof Finder)
                $newFinder->addChild($child);
            else
                $newFinder->setCondition($child);
        return $newFinder;
    }

    public function __construct($name, $condition = array())
    {
        if (!is_scalar($condition) && !is_array($condition))
            throw new \InvalidArgumentException('Unexpected');

        $this->name = $name;
        $this->condition = $condition;
        $this->lastSibling = $this;
    }

    public function __get($name)
    {
        return $this->stackSibling(new static($name));
    }

    public function __call($name, $children)
    {
        $newFinder = static::__callStatic($name, $children);

        return $this->stackSibling($newFinder);
    }

    public function __invoke()
    {
        foreach (func_get_args () as $child)
            if ($child instanceof static)
                $this->lastSibling->addChild($child);
            else
                throw new \InvalidArgumentException('Unexpected');
        return $this;
    }

    public function addChild(Finder $child)
    {
        $childClone = clone $child;
        $childClone->setRequired(false);
        $childClone->setMapper($this->mapper);
        $childClone->setParent($this);
        $this->children[] = $childClone;
    }

    public function fetch()
    {
        if (!$this->mapper)
            throw new \RuntimeException;
        return $this->mapper->fetch($this);
    }

    public function fetchAll()
    {
        if (!$this->mapper)
            throw new \RuntimeException;
        return $this->mapper->fetchAll($this);
    }

    public function getChildren()
    {
        return $this->children;
    }

    public function getCondition()
    {
        return $this->condition;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getNextSibling()
    {
        return $this->nextSibling;
    }

    public function getParentName()
    {
        return $this->parent ? $this->parent->getName() : null;
    }

    public function getNextSiblingName()
    {
        return $this->nextSibling ? $this->nextSibling->getName() : null;
    }

    public function hasChildren()
    {
        return!empty($this->children);
    }

    public function hasMore()
    {
        return $this->hasChildren() || $this->hasNextSibling();
    }

    public function hasNextSibling()
    {
        return!is_null($this->nextSibling);
    }

    public function isRequired()
    {
        return $this->required;
    }

    public function offsetExists($offset)
    {
        throw new \InvalidArgumentException('Unexpected'); //FIXME
    }

    public function offsetGet($condition)
    {
        $this->lastSibling->condition = $condition;
        return $this;
    }

    public function offsetSet($offset, $value)
    {
        throw new \InvalidArgumentException('Unexpected'); //FIXME
    }

    public function offsetUnset($offset)
    {
        throw new \InvalidArgumentException('Unexpected'); //FIXME
    }

    public function setCondition($condition)
    {
        $this->condition = $condition;
    }

    public function setMapper($mapper)
    {
        foreach ($this->children as $child)
            $child->setMapper($mapper);
        $this->mapper = $mapper;
    }

    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    public function setNextSibling($sibling)
    {
        $sibling->setParent($this);
        $this->nextSibling = $sibling;
    }

    public function setRequired($required)
    {
        $this->required = $required;
    }

    protected function stackSibling(Finder $sibling)
    {
        $this->lastSibling->setNextSibling($sibling);
        $this->lastSibling = $sibling;
        return $this;
    }

}

/**
 * LICENSE
 *
 * Copyright (c) 2009-2011, Alexandre Gomes Gaigalas.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 *
 *     * Redistributions of source code must retain the above copyright notice,
 *       this list of conditions and the following disclaimer.
 *
 *     * Redistributions in binary form must reproduce the above copyright notice,
 *       this list of conditions and the following disclaimer in the documentation
 *       and/or other materials provided with the distribution.
 *
 *     * Neither the name of Alexandre Gomes Gaigalas nor the names of its
 *       contributors may be used to endorse or promote products derived from this
 *       software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
 * ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
 * ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 */