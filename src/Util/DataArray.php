<?php namespace Pauldro\Minicli\v2\Util;

/**
 * DataArray
 *
 * Container for Data lists
 *
 * @property array $data Array where values are stored
 */
class DataArray implements \IteratorAggregate, \ArrayAccess, \Countable {
	protected $data = [];

/* =============================================================
	Getters
============================================================= */
	/**
	 * Return the value of the item at the given index, or null if not set.
	 * @param  int|string $key Provide any of the following:
	 * @return Data|null Value of item requested, or null if it doesn't exist.
	 */
	public function get($key) {
		if ($this->has($key) === false) {
			return null;
		}
		return $this->data[$key];
	}

	/**
	 * Return
	 * @param  int|string $key Provide any of the following:
	 * @return Data|null Value of item requested, or null if it doesn't exist.
	 */
	public function has($key) {
		return array_key_exists($key, $this->data);
	}

	/**
	 * Return a PHP array of all the items in this DataArray with original keys maintained
	 * @return array Copy of the array that DataArray uses internally.
	 */
	public function getArray() {
		return $this->data;
	}

	/**
	 * Return Array of Arrays
	 * NOTE: use if listing Data Classes
	 * @return array[array]
	 */
	public function getJsonArray() {
		$data = [];
		foreach ($this->data as $item) {
			$data[] = $item->getJsonArray();
		}
		return $data;
	}

	/**
	 * Return a regular PHP array of all keys used in this DataArray.
	 * @return array Keys used in the DataArray.
	 */
	public function getKeys() {
		return array_keys($this->data);
	}

	/**
	 * Return a regular PHP array of all values used in this DataArray.
	 * NOTE: this does not attempt to maintain original
	 * keys of the items. The returned array is reindexed from 0.
	 * @return array Values used in the DataArray.
	 */
	public function getValues() {
		return array_values($this->data);
	}

	/**
	 * Return the first item in the DataArray or boolean false if empty.
	 * Note that this resets the internal DataArray pointer, which would affect other active iterations.
	 * @return Data|mixed|bool
	 */
	public function first() {
		return reset($this->data);
	}

	/**
	 * Returns the last item in the DataArray or boolean false if empty.
	 * Note that this resets the internal DataArray pointer, which would affect other active iterations.
	 */
	public function last() {
		return end($this->data);
	}

	/**
	 * Return new/blank item of the type that this DataArray holds
	 * @return Data
	 *
	 */
	public function makeBlankItem() {
		return new Data();
	}

	/**
	 * Return subset of the DatArray
	 *
	 * Given a starting point and a number of items, returns a new DataArray of those items.
	 * If `$limit` is omitted, then it includes everything beyond the starting point.
	 *
	 * ~~~~~
	 * // Get first 3 items
	 * $myItems = $items->slice(0, 3);
	 * ~~~~~
	 *
	 * @param  int $start Starting index.
	 * @param  int $limit Number of items to include. If omitted, includes the rest of the array.
	 * @return DataArray Returns a new WireArray.
	 */
	public function subset($start, $limit = 0) {
		if ($limit) {
			$slice = array_slice($this->data, $start, $limit);
		} else {
			$slice = array_slice($this->data, $start);
		}
		$items = new static();
		$items->setArray($slice);
		return $items;
	}

/* =============================================================
	Setters
============================================================= */
	/**
	 * Set an item by key in the DataArray.
	 * @param int|string $key Key of item to set.
	 * @param int|string|array|object|Data $value Item value to set.
	 * @return $this
	 */
	public function set($key, $value) {
		$this->data[$key] = $value;
		return $this;
	}

	/**
	 * Add an item to the end of the DataArray.
	 * ~~~~~
	 * $list->add($item);
	 * ~~~~~
	 * @param int|string|array|object$item Item to add.
	 * @return $this
	 */
	public function add($item) {
		$this->data[] = $item;
		return $this;
	}

	/**
	 * Set Entire DataArray.
	 * ~~~~~
	 * $list->set($items);
	 * ~~~~~
	 * @param  array Set Array
	 * @return $this
	 */
	public function setArray($items = []) {
		$this->data = $items;
	}

/* =============================================================
	Removal
============================================================= */
	/**
	 * Remove the given item or index from the WireArray (if it exists).
	 * @param int|string $key Item to remove (object), or index of that item.
	 * @return $this This instance.
	 */
	public function remove($key) {
		if(array_key_exists($key, $this->data)) {
			unset($this->data[$key]);
		}
		return $this;
	}

/* =============================================================
	Interface Functions
============================================================= */
	/**
	 * Allows iteration of the DataArray.
	 * - Fulfills PHP's IteratorAggregate interface so that you can traverse the DataArray.
	 * - No need to call this method directly, just use PHP's `foreach()` method on the DataArray.
	 *
	 * ~~~~~
	 * // Traversing a DataArray with foreach:
	 * foreach($items as $item) {
	 *   // ...
	 * }
	 * ~~~~~
	 * @return \ArrayObject|Data[]
	 */
	public function getIterator() {
		return new \ArrayObject($this->data);
	}

	/**
	 * Returns the number of items in this DataArray.
	 * Fulfills PHP's Countable interface, meaning it also enables this DataArray to be used with PHP's `count()` function.
	 * ~~~~~
	 * // These two are the same
	 * $qty = $items->count();
	 * $qty = count($items);
	 * ~~~~~
	 * @return int
	 */
	public function count() {
		return count($this->data);
	}

	/**
	 * Sets an index in the DataArray.
	 * For the \ArrayAccess interface.
	 * @param int|string $key Key of item to set.
	 * @param Data|mixed $value Value of item.
	 */
	public function offsetSet($key, $value) {
        $this->set($key, $value);
    }

	/**
	 * Returns the value of the item at the given index, or false if not set.
	 * @param int|string $key Key of item to retrieve.
	 * @return Data|mixed|bool Value of item requested, or false if it doesn't exist.
	 */
	public function offsetGet($key) {
		if($this->offsetExists($key)) {
			return $this->data[$key];
		} else {
			return false;
		}
	}


	/**
	 * Unsets the value at the given index.
	 * For the \ArrayAccess interface.
	 * @param int|string $key Key of the item to unset.
	 * @return bool True if item existed and was unset. False if item didn't exist.
	 */
	public function offsetUnset($key) {
		if($this->offsetExists($key)) {
			$this->remove($key);
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Determines if the given index exists in this DataArray.
	 * For the \ArrayAccess interface
	 * @param int|string $key Key of the item to check for existance.
	 * @return bool True if the item exists, false if not.
	 */
	public function offsetExists($key) {
		return array_key_exists($key, $this->data);
	}

}
