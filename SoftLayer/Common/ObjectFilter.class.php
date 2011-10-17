<?php

/**
 * Filtering object for use with the SoftLayer API
 */
class SoftLayer_ObjectFilter extends SoftLayer_ObjectMask
{
	/**
	 * Options for the filter
	 */
	//private $options 	= array();

	/**
	 * Operation to perform
	 */
	//private $operation 	= null;

	/**
	 * Sort the object by the direction
	 *
	 * @param string $direction Sort direction
	 * @param string $sortAs Sort the field as int, string, etc
	 * @return object SoftLayer_ObjectFilter
	 */
	private function _sort($direction,$sortAs=null)
	{
		if(!is_string($this->operation)){
			$this->operation = 'orderBy';
		}

		$sort1 = $this->_createOption('sort',array($direction));
		$sort2 = null;

		switch(strtolower($sortAs)){
			case 'init':
				break;
			default:
				$sort2 = $this->_createOption('lower',null);
		}
		$sortNullsFirst = (strtoupper($direction) == 'DESC') ? 'LAST' : 'FIRST';
		
		$this->_addOptions(array($sort1,$sort2));
		return $this;
	}

	/**
	 * Apply the new property as a filter object
	 *
	 * @param string $var Property name
	 * @return object new Filter object
	 */
	public function __get($var)
	{
		$this->{$var} = new SoftLayer_ObjectFilter();
		return $this->{$var};
	}

	/**
	 * Add option(s) to the filter
	 *
	 * @param mixed $options Single/array of options
	 * @return object SoftLayer_ObjectFilter
	 */
	private function _addOptions($options)
	{
		if(is_array($options) && count($options)>0){
			foreach($options as $option){
				$this->_addOption($option);
			}
		}else{
			throw new Exception('Invalid option!');
		}
		return $this;
	}

	/**
	 * Add a single option to the filter
	 *
	 * @param mixed $option Option to add
	 * @return object SoftLayer_ObjectFilter
	 */
	private function _addOption($option)
	{
		if(!is_array($this->options)){
			$this->options = array($option);
		}else{
			$this->options[] = $option;
		}
		return $this;
	}

	/**
	 * Create a new option to add to the filter
	 *
	 * @param string $name Name of the option
	 * @param mixed $value Option value
	 * @return object $option Class
	 */
	private function _createOption($name,$value)
	{
		$option 		= new stdClass();
		$option->name 	= $name;
		$option->value 	= $value;
		return $option;
	}

	//######--------------

	/**
	 * Sort results by option, ascending
	 *
	 * @param string $sortAs[optional] Sort as data type
	 */
	public function sortUp($sortAs=null)
	{
		return $this->_sort('ASC',$sortAs);
	}

	/**
	 * Sort results by option, descending
	 *
	 * @param string $sortAs[optional] Sort as data type
	 */
	public function sortDown($sortAs=null)
	{
		return $this->_sort('DESC',$sortAs);	
	}

	/**
	 * Find record(s) that has term in it
	 *
	 * @param string $term Search term
	 * @return object SoftLayer_ObjectFilter
	 */
	public function contains($term=null)
	{
		if($term != null){
			$this->operation = '*='.$term;
		}
		return $this;
	}

	/**
	 * Find record(s) with data in the set
	 *
	 * @param array $dataSet Data set
	 * @return object SoftLayer_ObjectFilter
	 */
	public function in($dataSet)
	{
		$this->operation = 'in';
		$this->_addOption($this->_createOption('data',$dataSet));
		return $this;
	}

	/**
	 * Find record(s) with data NOT in the set
	 *
	 * @param array $dataSet Data set
	 * @return object SoftLayer_ObjectFilter
	 */
	public function notIn($dataSet)
	{
		$this->operation = 'not in';
		$this->_addOption($this->_createOption('data',$dataSet));
		return $this;
	}

	/**
	 * Find record(s) where data equals term
	 *
	 * @param string $term Search term
	 * @return object SoftLayer_ObjectFilter
	 */
	public function equals($term)
	{
		if($term != null){ $this->operation = $term; }
		return $this;
	}

	/**
	 * Find record(s) where data DOES NOT equal term
	 *
	 * @param string $term Search term
	 * @return object SoftLayer_ObjectFilter
	 */
	public function notEquals($term)
	{
		if($term != null){ $this->operation = '!= '.$term; }
		return $this;
	}

	/**
	 * Find record(s) where filter field IS NULL
	 *
	 * @return object SoftLayer_ObjectFilter
	 */
	public function isNull()
	{
		$this->operation = 'is null';
		return $this;
	}

	/**
	 * Find record(s) where filter field is NOT NULL
	 *
	 * @return object SoftLayer_ObjectFilter
	 */
	public function notNull()
	{
		$this->operation = 'not null';
		return $this;
	}

	/**
	 * Find record(s) with magic "count" field matching value & operator
	 *
	 * @param int $count Count value to match
	 * @param string $operator Operator to use
	 * @return SoftLayer_ObjectFilter
	 */
	public function countEquals($count=1,$operator='=')
	{
		$this->_addOptions(array(
            $this->_createOption('itemCount', array($count)),
            $this->_createOption('countOperator', array($operator))
        ));

        return $this;
	}

	/**
	 * Merge two (or more) fields with the "glue" and match on value
	 *
	 * @param array $fields Fields to match
	 * @param string $glue "Glue" to merge the fields with
	 * @return SoftLayer_ObjectFilter
	 */
	public function mergeEquals($fields,$glue,$value)
	{
		$this->operation = 'implodeLike';
        $this->_addOptions(array(
            $this->_createOption('properties', $fields),
            $this->_createOption('glue', array($glue)),
            $this->_createOption('value', array($value))
        ));
        return $this;
	}

	/**
	 * Find record(s) in a certain date range (or, optionally, 
	 *	 only before or only after)
	 *
	 * @param string $startDate[optional] Start Date
	 * @param string $endDate[optional] End Date
	 * @return object SoftLayer_ObjectFilter
	 */
	public function dateRange($startDate=null,$endDate=null)
	{
        if($startDate != null && $endDate != null){
        	// we have a start and end
        	$this->operation = 'betweenDate';
        	$this->_addOptions(array(
        		$this->_createOption('startDate',array($startDate)),
        		$this->_createOption('endDate',array($endDate))
	        ));
        }else{
        	if($startDate != null){ $this->_startDate($startDate); }
        	if($endDate != null){ $this->_endDate($startDate); }
        }
        return $this;
	}

	/**
	 * Build option for "start date"
	 *
	 * @param string $date Date string
	 * @return SoftLayer_ObjectFilter
	 */
	private function _startDate($date)
	{
		if($date != null){
			$this->operation = 'greaterThanDate';
			$this->_addOption($this->_createOption('date',array($date)));
		}
		return $this;
	}

	/**
	 * Build option for "end date"
	 *
	 * @param string $date Date string
	 * @return object SoftLayer_ObjectFilter
	 */
	private function _endDate($date)
	{
		if($date != null){
			$this->operation = 'lessThanDate';
			$this->_addOption($this->_createOption('date',array($date)));
		}
		return $this;
	}
}

?>